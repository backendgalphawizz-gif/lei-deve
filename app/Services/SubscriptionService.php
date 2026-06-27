<?php

namespace App\Services;

use App\Models\LeiApplication;
use App\Models\LeiBusinessSetting;
use App\Models\LeiPricingPlan;
use App\Models\LeiSubscription;
use App\Models\User;
use Illuminate\Support\Collection;

class SubscriptionService
{
    public function expireDueSubscriptions(): void
    {
        LeiSubscription::query()
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);
    }

    public function renewalWindowDays(): int
    {
        try {
            $settings = LeiBusinessSetting::current();

            return max(0, (int) ($settings->renewal_window_days ?? 90));
        } catch (\Throwable) {
            return 90;
        }
    }

    public function activePlansForSection(string $section): Collection
    {
        return LeiPricingPlan::query()
            ->where('section', $section)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function isEntityEligibleForRenewal(LeiApplication $entity): bool
    {
        if ($entity->status !== 'approved' || ! $entity->lei_number || ! $entity->expiry_date) {
            return false;
        }

        if ($entity->expiry_date->isPast()) {
            return true;
        }

        $window = $this->renewalWindowDays();

        if ($window === 0) {
            return false;
        }

        return now()->diffInDays($entity->expiry_date, false) <= $window;
    }

    public function entityRenewalLabel(LeiApplication $entity): ?string
    {
        if (! $this->isEntityEligibleForRenewal($entity)) {
            return null;
        }

        if ($entity->expiry_date->isPast()) {
            return 'Expired';
        }

        return 'Renewal open';
    }

    public function eligibleEntitiesForRenewal(User $user): Collection
    {
        return LeiApplication::query()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereNotNull('lei_number')
            ->whereNotNull('expiry_date')
            ->orderBy('expiry_date')
            ->get()
            ->filter(fn (LeiApplication $entity) => $this->isEntityEligibleForRenewal($entity))
            ->values();
    }

    public function renewalEligibilityBlockReason(User $user, ?string $leiNumber = null): ?string
    {
        $eligible = $this->eligibleEntitiesForRenewal($user);

        if ($eligible->isEmpty()) {
            $window = $this->renewalWindowDays();

            if ($window === 0) {
                return 'Renewal plans are available only after your LEI expiry date.';
            }

            return 'Renewal plans open within ' . $window . ' days of your LEI expiry. None of your entities are eligible yet.';
        }

        if ($leiNumber) {
            $entity = $eligible->first(fn (LeiApplication $e) => $e->lei_number === $leiNumber);

            if (! $entity) {
                return 'The selected LEI is not eligible for renewal yet.';
            }
        }

        return null;
    }

    public function activeSubscriptionForSection(User $user, string $section): ?LeiSubscription
    {
        $this->expireDueSubscriptions();

        return LeiSubscription::query()
            ->where('user_id', $user->id)
            ->where('plan_section', $section)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderByDesc('duration_years')
            ->first();
    }

    public function subscriptionForWorkflow(User $user, string $section, ?LeiApplication $application = null): ?LeiSubscription
    {
        $this->expireDueSubscriptions();

        if ($application?->lei_subscription_id) {
            $linked = LeiSubscription::find($application->lei_subscription_id);

            if ($linked && $this->isSubscriptionActive($linked)) {
                return $linked;
            }
        }

        $candidates = LeiSubscription::query()
            ->where('user_id', $user->id)
            ->where('plan_section', $section)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderByDesc('created_at')
            ->get();

        foreach ($candidates as $subscription) {
            $consumed = LeiApplication::query()
                ->where('lei_subscription_id', $subscription->id)
                ->whereNotIn('status', ['draft', 'rejected'])
                ->when($application, fn ($query) => $query->where('id', '!=', $application->id))
                ->exists();

            if (! $consumed) {
                return $subscription;
            }
        }

        return null;
    }

    public function isSubscriptionActive(LeiSubscription $subscription): bool
    {
        if ($subscription->status !== 'active') {
            return false;
        }

        return ! $subscription->expires_at || $subscription->expires_at->isFuture();
    }

    public function purchaseBlockReason(User $user, LeiPricingPlan $plan, ?string $leiNumber = null): ?string
    {
        if (! $user->isApplicant()) {
            return null;
        }

        if ($plan->section === 'renewal') {
            if ($reason = $this->renewalEligibilityBlockReason($user, $leiNumber)) {
                return $reason;
            }
        }

        $unused = $this->subscriptionForWorkflow($user, $plan->section);

        if (! $unused) {
            return null;
        }

        $currentTier = (int) $unused->duration_years;
        $requestedTier = (int) ($plan->duration_years ?: 1);

        if ($requestedTier > $currentTier) {
            return 'You already have an unused ' . $unused->plan_name . '. Continue in the portal before purchasing another plan.';
        }

        if ($requestedTier === $currentTier) {
            return 'You already have an unused ' . $unused->plan_name . '. Continue in the portal.';
        }

        $expires = $unused->expires_at?->format('M j, Y');

        return 'You already have an unused ' . $unused->plan_name . '. Lower-tier plans cannot be purchased'
            . ($expires ? ' until your current plan expires on ' . $expires . '.' : '.');
    }

    public function portalPlanBlocks(User $user, Collection $plans, ?string $leiNumber = null): array
    {
        $blocks = [];

        foreach ($plans as $plan) {
            if ($reason = $this->purchaseBlockReason($user, $plan, $leiNumber)) {
                $blocks[$plan->id] = $reason;
            }
        }

        return $blocks;
    }

    public function subscribe(User $user, LeiPricingPlan $plan, ?string $ip = null, ?string $leiNumber = null): LeiSubscription
    {
        if ($reason = $this->purchaseBlockReason($user, $plan, $leiNumber)) {
            throw new \InvalidArgumentException($reason);
        }

        $settings = LeiBusinessSetting::current();
        $years = (int) ($plan->duration_years ?: 1);
        $startsAt = now();

        return LeiSubscription::create([
            'reference' => LeiSubscription::generateReference(),
            'user_id' => $user->id,
            'pricing_plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'plan_section' => $plan->section,
            'amount' => $plan->price,
            'currency_code' => $settings->currency_code ?: 'INR',
            'duration_years' => $years,
            'status' => 'active',
            'payment_status' => 'paid',
            'starts_at' => $startsAt,
            'expires_at' => $startsAt->copy()->addYears($years),
            'ip_address' => $ip,
        ]);
    }

    public function createFromAdmin(User $user, array $data): LeiSubscription
    {
        $settings = LeiBusinessSetting::current();
        $plan = ! empty($data['pricing_plan_id'])
            ? LeiPricingPlan::find($data['pricing_plan_id'])
            : null;

        $years = (int) ($data['duration_years'] ?? $plan?->duration_years ?? 1);
        $startsAt = ! empty($data['starts_at'])
            ? \Illuminate\Support\Carbon::parse($data['starts_at'])
            : now();

        $expiresAt = ! empty($data['expires_at'])
            ? \Illuminate\Support\Carbon::parse($data['expires_at'])
            : $startsAt->copy()->addYears($years);

        return LeiSubscription::create([
            'reference' => LeiSubscription::generateReference(),
            'user_id' => $user->id,
            'pricing_plan_id' => $plan?->id,
            'plan_name' => $data['plan_name'] ?? $plan?->name ?? 'Custom Plan',
            'plan_section' => $data['plan_section'] ?? $plan?->section ?? 'registration',
            'amount' => $data['amount'] ?? $plan?->price ?? 0,
            'currency_code' => $settings->currency_code ?: 'INR',
            'duration_years' => $years,
            'status' => $data['status'] ?? 'active',
            'payment_status' => $data['payment_status'] ?? 'paid',
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'admin_notes' => $data['admin_notes'] ?? null,
        ]);
    }
}

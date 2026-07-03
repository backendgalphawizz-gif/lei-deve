<?php

namespace App\Services;

use App\Mail\ApplicationSubmittedMail;
use Illuminate\Support\Facades\Mail;

use App\Models\LeiApplication;
use App\Models\LeiApplicationAuditEvent;
use App\Models\LeiSubscription;
use App\Models\User;
use Illuminate\Support\Str;

class ApplicantApplicationService
{
    public function __construct(private SubscriptionService $subscriptions) {}

    public function activeDraft(User $user, ?string $workflowType = null): ?LeiApplication
    {
        $query = LeiApplication::query()
            ->where('user_id', $user->id)
            ->where('status', 'draft');

        if ($workflowType) {
            $query->where('workflow_type', $workflowType);
        }

        return $query->orderByDesc('updated_at')->first();
    }

    public function hasSubmittedRegistration(User $user): bool
    {
        return LeiApplication::query()
            ->where('user_id', $user->id)
            ->where('workflow_type', 'registration')
            ->where('status', '!=', 'draft')
            ->exists();
    }

    public function submittedRegistration(User $user): ?LeiApplication
    {
        return LeiApplication::query()
            ->where('user_id', $user->id)
            ->where('workflow_type', 'registration')
            ->where('status', '!=', 'draft')
            ->orderByDesc('submitted_on')
            ->orderByDesc('created_at')
            ->first();
    }

    public function startRegistration(User $user, LeiSubscription $subscription): LeiApplication
    {
        if ($this->hasSubmittedRegistration($user)) {
            return $this->submittedRegistration($user);
        }

        $existing = $this->activeDraft($user, 'registration');

        if ($existing) {
            if (! $existing->lei_subscription_id) {
                $existing->update(['lei_subscription_id' => $subscription->id]);
            }

            if (! $existing->lei_number && $user->lei_number) {
                $existing->update(['lei_number' => $user->lei_number]);
            }

            if ($user->organization_name && $existing->entity_name === $user->name) {
                $existing->update(['entity_name' => $user->organization_name]);
            }

            return $existing->fresh();
        }

        return LeiApplication::create([
            'user_id' => $user->id,
            'lei_subscription_id' => $subscription->id,
            'application_code' => $this->generateCode(),
            'entity_name' => $user->organization_name ?: $user->name,
            'country' => $user->country_of_incorporation ?: 'United Kingdom',
            'lei_number' => $user->lei_number,
            'issuance_type' => 'Direct Issuance',
            'workflow_type' => 'registration',
            'workflow_step' => 1,
            'application_type' => 'new_registration',
            'status' => 'draft',
            'priority' => 'med',
            'draft_data' => [],
        ]);
    }

    public function startRenewal(
        User $user,
        LeiSubscription $subscription,
        ?string $leiNumber = null,
        ?string $entityName = null,
        ?string $country = null,
    ): LeiApplication {
        $existing = $this->activeDraft($user, 'renewal');

        if ($existing) {
            if (! $existing->lei_subscription_id) {
                $existing->update(['lei_subscription_id' => $subscription->id]);
            }

            if ($leiNumber && ! $existing->lei_number) {
                $existing->update([
                    'lei_number' => $leiNumber,
                    'entity_name' => $entityName ?: $existing->entity_name,
                    'country' => $country ?: $existing->country,
                ]);
            }

            return $existing->fresh();
        }

        return LeiApplication::create([
            'user_id' => $user->id,
            'lei_subscription_id' => $subscription->id,
            'application_code' => $this->generateCode(),
            'entity_name' => $entityName ?: 'Entity pending selection',
            'country' => $country ?: ($user->country_of_incorporation ?: 'United Kingdom'),
            'issuance_type' => 'Renewal',
            'workflow_type' => 'renewal',
            'workflow_step' => 1,
            'application_type' => 'renewal',
            'lei_number' => $leiNumber,
            'status' => 'draft',
            'priority' => 'med',
            'draft_data' => [],
        ]);
    }

    public function findApprovedEntity(User $user, string $search): ?LeiApplication
    {
        $search = trim($search);

        if ($search === '') {
            return null;
        }

        return LeiApplication::query()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereNotNull('lei_number')
            ->where(function ($query) use ($search) {
                $query->where('lei_number', 'like', "%{$search}%")
                    ->orWhere('entity_name', 'like', "%{$search}%");
            })
            ->orderByDesc('expiry_date')
            ->first();
    }

    public function approvedEntitiesForUser(User $user)
    {
        return LeiApplication::query()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereNotNull('lei_number')
            ->orderByDesc('expiry_date')
            ->get();
    }

    public function applyRegistrationPrefill(LeiApplication $application): LeiApplication
    {
        $prefill = session(GleifRegistrationPrefillService::SESSION_KEY);
        if (! is_array($prefill) || $prefill === []) {
            return $application;
        }

        $application->loadMissing('user');

        $draft = $application->draft_data ?? [];
        $appUpdates = [];

        foreach (['entity_name', 'registration_number', 'country', 'registered_address', 'registration_authority'] as $field) {
            if (! empty($prefill[$field]) && empty($draft[$field])) {
                $draft[$field] = $prefill[$field];
            }
        }

        if (! empty($prefill['source_lei']) && empty($draft['reference_gleif_lei'])) {
            $draft['reference_gleif_lei'] = $prefill['source_lei'];
        }

        if (! empty($prefill['entity_name']) && in_array($application->entity_name, ['', $application->user?->name], true)) {
            $appUpdates['entity_name'] = $prefill['entity_name'];
        }

        if (! empty($prefill['country']) && empty($application->country)) {
            $appUpdates['country'] = $prefill['country'];
        }

        if ($draft !== ($application->draft_data ?? []) || $appUpdates !== []) {
            $application->update(array_merge($appUpdates, ['draft_data' => $draft]));
        }

        return $application->fresh();
    }

    public function saveStep(LeiApplication $application, int $step, array $data): LeiApplication
    {
        $draft = array_merge($application->draft_data ?? [], $data);

        $application->fill([
            'workflow_step' => $step,
            'draft_data' => $draft,
        ]);

        if (! empty($data['entity_name'])) {
            $application->entity_name = $data['entity_name'];
        }

        if (! empty($data['country'])) {
            $application->country = $data['country'];
        }

        if (! empty($data['lei_number'])) {
            $application->lei_number = $data['lei_number'];
        }

        $application->save();

        return $application->fresh();
    }

    public function submitRegistration(LeiApplication $application): LeiApplication
    {
        $application->loadMissing('user');

        if ($application->user && LeiApplication::query()
            ->where('user_id', $application->user_id)
            ->where('workflow_type', 'registration')
            ->where('id', '!=', $application->id)
            ->where('status', '!=', 'draft')
            ->exists()) {
            throw new \RuntimeException('You have already submitted a LEI registration. Each account may register only once.');
        }

        $leiNumber = $application->lei_number ?: $application->user?->lei_number;

        $application->update([
            'status' => 'new',
            'workflow_step' => 4,
            'submitted_on' => now()->toDateString(),
            'lei_number' => $leiNumber,
        ]);

        $description = $leiNumber
            ? "Registration submitted. LEI code: {$leiNumber}."
            : 'Registration submitted by applicant for review.';

        $this->recordAuditEvent($application, $description);

        $application = $application->fresh();

        // Send confirmation email to applicant
        if ($application->user?->email) {
            try {
                Mail::to($application->user->email)->send(new ApplicationSubmittedMail($application));
            } catch (\Throwable) {
                // Non-fatal
            }
        }

        return $application;
    }

    public function submitRenewal(LeiApplication $application): LeiApplication
    {
        $application->update([
            'status' => 'new',
            'workflow_step' => 4,
            'submitted_on' => now()->toDateString(),
        ]);

        $this->recordAuditEvent($application, 'Renewal submitted by applicant for review.');

        return $application->fresh();
    }

    public function entitiesForUser(User $user)
    {
        return LeiApplication::query()
            ->where('user_id', $user->id)
            ->where('status', '!=', 'draft')
            ->orderByDesc('updated_at')
            ->get();
    }

    public function applicationsForUser(User $user)
    {
        return LeiApplication::query()
            ->where('user_id', $user->id)
            ->where('status', '!=', 'draft')
            ->orderByDesc('submitted_on')
            ->orderByDesc('created_at')
            ->get();
    }

    public function registrationSubscription(User $user, ?LeiApplication $application = null): ?LeiSubscription
    {
        return $this->subscriptions->subscriptionForWorkflow($user, 'registration', $application);
    }

    public function renewalSubscription(User $user, ?LeiApplication $application = null): ?LeiSubscription
    {
        return $this->subscriptions->subscriptionForWorkflow($user, 'renewal', $application);
    }

    private function recordAuditEvent(LeiApplication $application, string $description): void
    {
        LeiApplicationAuditEvent::where('lei_application_id', $application->id)
            ->update(['is_highlight' => false]);

        LeiApplicationAuditEvent::create([
            'lei_application_id' => $application->id,
            'occurred_at' => now(),
            'description' => $description,
            'actor' => $application->user?->name ?? 'Applicant',
            'is_highlight' => true,
            'sort_order' => 0,
        ]);
    }

    private function generateCode(): string
    {
        do {
            $code = 'APP-' . strtoupper(Str::random(8));
        } while (LeiApplication::where('application_code', $code)->exists());

        return $code;
    }
}

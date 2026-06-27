<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\LeiPricingPlan;
use App\Services\ApplicantApplicationService;
use App\Services\ApplicantPortalRedirect;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptions,
        private ApplicantApplicationService $applications,
        private ApplicantPortalRedirect $portalRedirect,
    ) {}

    public function show(LeiPricingPlan $plan)
    {
        if (! $plan->is_active || (float) $plan->price <= 0) {
            return redirect()->route('contact')->with('info', 'Please contact sales for this plan.');
        }

        session(['intended_plan_id' => $plan->id]);

        if (request()->filled('lei')) {
            session(['intended_renewal_lei' => request()->query('lei')]);
        }

        if (auth()->check()) {
            $user = auth()->user();

            if ($user->isApplicant()) {
                if (! $user->is_active) {
                    session(['otp_user_id' => $user->id]);

                    return redirect()->route('applicant.verify-otp')
                        ->with('info', 'Verify your account to continue with ' . $plan->name . '.');
                }

                if ($reason = $this->subscriptions->purchaseBlockReason(
                    $user,
                    $plan,
                    $plan->section === 'renewal' ? request()->query('lei') : null,
                )) {
                    return redirect()->route('applicant.payments.index')
                        ->with('error', $reason);
                }

                $url = route('applicant.plans.subscribe', $plan);
                if (request()->filled('lei')) {
                    $url .= '?lei=' . urlencode(request()->query('lei'));
                }

                return redirect($url);
            }

            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard')
                    ->with('info', 'Please use an applicant account to purchase a plan.');
            }
        }

        if (session('otp_user_id')) {
            return redirect()->route('applicant.verify-otp')
                ->with('info', 'Verify your account to continue with ' . $plan->name . '.');
        }

        return redirect()->route('applicant.login')
            ->with('info', 'Sign in to subscribe to ' . $plan->name . '.');
    }

    public function store(Request $request, LeiPricingPlan $plan)
    {
        if (! auth()->check() || ! auth()->user()->isApplicant() || ! auth()->user()->is_active) {
            session(['intended_plan_id' => $plan->id]);

            return redirect()->route('applicant.login')
                ->with('error', 'Please sign in to complete your purchase.');
        }

        if (! $plan->is_active || (float) $plan->price <= 0) {
            return redirect()->route('pricing')->with('error', 'This plan is not available for online purchase.');
        }

        $user = auth()->user();

        $lei = $plan->section === 'renewal'
            ? ($request->query('lei') ?: session('intended_renewal_lei'))
            : null;

        if ($reason = $this->subscriptions->purchaseBlockReason($user, $plan, $lei)) {
            return redirect()->route('applicant.payments.index')->with('error', $reason);
        }

        try {
            $subscription = $this->subscriptions->subscribe($user, $plan, $request->ip(), $lei);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('pricing')->with('error', $e->getMessage());
        }

        session()->forget('intended_plan_id');

        if ($plan->section === 'registration') {
            $this->applications->startRegistration($user, $subscription);

            return redirect()
                ->route('applicant.registration.step', ['step' => 1])
                ->with('success', 'Subscription ' . $subscription->reference . ' is active. Continue your LEI registration.');
        }

        if ($plan->section === 'renewal') {
            $prefill = $lei ? $this->applications->findApprovedEntity($user, $lei) : null;

            if ($prefill && ! $this->subscriptions->isEntityEligibleForRenewal($prefill)) {
                return redirect()->route('pricing', ['#renewal'])
                    ->with('error', 'This LEI is not eligible for renewal yet.');
            }

            $this->applications->startRenewal(
                $user,
                $subscription,
                $prefill?->lei_number,
                $prefill?->entity_name,
                $prefill?->country,
            );

            session()->forget('intended_renewal_lei');

            return redirect()
                ->route('applicant.renewal.step', [
                    'step' => 1,
                    'lei' => $prefill?->lei_number,
                ])
                ->with('success', 'Subscription ' . $subscription->reference . ' is active. Continue your LEI renewal.');
        }

        return $this->portalRedirect->redirect($user, 'Subscription ' . $subscription->reference . ' is now active.');
    }
}

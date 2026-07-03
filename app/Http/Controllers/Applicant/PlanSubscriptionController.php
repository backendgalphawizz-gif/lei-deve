<?php

namespace App\Http\Controllers\Applicant;

use App\Mail\PaymentConfirmationMail;
use App\Models\LeiPricingPlan;
use App\Services\ApplicantApplicationService;
use App\Services\ApplicantPortalRedirect;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PlanSubscriptionController extends ApplicantPortalController
{
    public function __construct(
        ApplicantApplicationService $applications,
        private SubscriptionService $subscriptions,
        private ApplicantPortalRedirect $portalRedirect,
    ) {
        parent::__construct($applications);
    }

    public function show(Request $request, LeiPricingPlan $plan)
    {
        $this->sharePortalContext();
        $user = auth()->user();

        if (! $plan->is_active || (float) $plan->price <= 0) {
            return redirect()
                ->route('applicant.payments.index')
                ->with('error', 'This plan is not available for online purchase.');
        }

        $lei = $plan->section === 'renewal' ? $request->query('lei') : null;

        if ($reason = $this->subscriptions->purchaseBlockReason($user, $plan, $lei)) {
            return redirect()
                ->route('applicant.payments.index')
                ->with('error', $reason);
        }

        return view('applicant.payments.subscribe', compact('plan', 'lei'));
    }

    public function store(Request $request, LeiPricingPlan $plan)
    {
        $user = auth()->user();

        if (! $plan->is_active || (float) $plan->price <= 0) {
            return redirect()
                ->route('applicant.payments.index')
                ->with('error', 'This plan is not available for online purchase.');
        }

        $lei = $plan->section === 'renewal'
            ? ($request->query('lei') ?: $request->input('lei'))
            : null;

        if ($reason = $this->subscriptions->purchaseBlockReason($user, $plan, $lei)) {
            return redirect()
                ->route('applicant.payments.index')
                ->with('error', $reason);
        }

        try {
            $subscription = $this->subscriptions->subscribe($user, $plan, $request->ip(), $lei);
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('applicant.payments.index')
                ->with('error', $e->getMessage());
        }

        // Send payment confirmation email
        try {
            Mail::to($user->email)->send(new PaymentConfirmationMail($user, $subscription));
        } catch (\Throwable) {
            // Non-fatal
        }

        if ($plan->section === 'registration') {
            $this->applications->startRegistration($user, $subscription);

            return redirect()
                ->route('applicant.registration.step', ['step' => 1])
                ->with('success', 'Subscription ' . $subscription->reference . ' is active. Continue your LEI registration.');
        }

        if ($plan->section === 'renewal') {
            $prefill = $lei ? $this->applications->findApprovedEntity($user, $lei) : null;

            if ($prefill && ! $this->subscriptions->isEntityEligibleForRenewal($prefill)) {
                return redirect()
                    ->route('applicant.payments.index')
                    ->with('error', 'This LEI is not eligible for renewal yet.');
            }

            $this->applications->startRenewal(
                $user,
                $subscription,
                $prefill?->lei_number,
                $prefill?->entity_name,
                $prefill?->country,
            );

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

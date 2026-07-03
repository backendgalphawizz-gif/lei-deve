<?php

namespace App\Http\Controllers\Applicant;

use App\Models\LeiSubscription;
use App\Models\LeiBusinessSetting;
use App\Services\ApplicantApplicationService;
use App\Services\SubscriptionService;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentController extends ApplicantPortalController
{
    public function __construct(
        ApplicantApplicationService $applications,
        private SubscriptionService $subscriptions,
    ) {
        parent::__construct($applications);
    }

    public function index()
    {
        $this->sharePortalContext();
        $user = auth()->user();

        $this->subscriptions->expireDueSubscriptions();

        $subscriptions = $user->subscriptions()->with('pricingPlan')->latest()->get();
        $pending = $subscriptions->where('payment_status', 'pending');
        $paid = $subscriptions->where('payment_status', 'paid');

        $registrationPlans = $this->subscriptions->activePlansForSection('registration');
        $renewalPlans = $this->subscriptions->activePlansForSection('renewal');
        $eligibleRenewals = $this->subscriptions->eligibleEntitiesForRenewal($user);

        $registrationBlocks = $this->subscriptions->portalPlanBlocks($user, $registrationPlans);
        $renewalBlocks = $eligibleRenewals->isNotEmpty()
            ? $this->subscriptions->portalPlanBlocks($user, $renewalPlans)
            : collect($renewalPlans)->mapWithKeys(fn ($plan) => [
                $plan->id => $this->subscriptions->renewalEligibilityBlockReason($user),
            ])->filter()->all();

        $unusedRegistration = $this->subscriptions->subscriptionForWorkflow($user, 'registration');
        $unusedRenewal = $this->subscriptions->subscriptionForWorkflow($user, 'renewal');

        return view('applicant.payments.index', compact(
            'subscriptions',
            'pending',
            'paid',
            'registrationPlans',
            'renewalPlans',
            'eligibleRenewals',
            'registrationBlocks',
            'renewalBlocks',
            'unusedRegistration',
            'unusedRenewal',
        ));
    }

    public function invoice(LeiSubscription $subscription)
    {
        abort_unless($subscription->user_id === auth()->id(), 404);

        $user = auth()->user();
        $businessSettings = LeiBusinessSetting::current();

        $baseAmount  = (float) ($subscription->amount ?? 0);
        $gstAmount   = round($baseAmount * 0.18, 2);
        $totalAmount = $baseAmount + $gstAmount;
        $currency    = '₹';

        $pdf = Pdf::loadView('applicant.payments.invoice', compact(
            'subscription', 'user', 'businessSettings',
            'baseAmount', 'gstAmount', 'totalAmount', 'currency',
        ))
            ->setPaper('A4', 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => false]);

        $filename = 'Invoice-' . $subscription->reference . '.pdf';

        return $pdf->download($filename);
    }
}

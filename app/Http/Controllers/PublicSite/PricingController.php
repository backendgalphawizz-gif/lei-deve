<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\LeiFaq;
use App\Models\LeiPricingMatrixRow;
use App\Models\LeiSiteSection;
use App\Services\SubscriptionService;

class PricingController extends Controller
{
    public function __construct(private SubscriptionService $subscriptions) {}

    public function index()
    {
        $sections = LeiSiteSection::forPage('pricing');
        $registrationPlans = $this->subscriptions->activePlansForSection('registration');
        $renewalPlans = $this->subscriptions->activePlansForSection('renewal');
        $matrixRows = LeiPricingMatrixRow::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        $pricingFaqs = LeiFaq::query()
            ->where('is_published', true)
            ->where('show_on_pricing', true)
            ->orderBy('sort_order')
            ->get();

        $purchaseBlocks = [];
        $renewalPurchaseBlocks = [];
        $activeRegistrationSubscription = null;
        $activeRenewalSubscription = null;
        $eligibleRenewalEntities = collect();
        $renewalLei = request()->query('lei');

        if (auth()->check() && auth()->user()->isApplicant()) {
            $user = auth()->user();
            $activeRegistrationSubscription = $this->subscriptions->activeSubscriptionForSection($user, 'registration');
            $activeRenewalSubscription = $this->subscriptions->activeSubscriptionForSection($user, 'renewal');
            $eligibleRenewalEntities = $this->subscriptions->eligibleEntitiesForRenewal($user);

            $purchaseBlocks = $this->subscriptions->portalPlanBlocks($user, $registrationPlans);
            $renewalPurchaseBlocks = $this->subscriptions->portalPlanBlocks($user, $renewalPlans, $renewalLei);

            if ($eligibleRenewalEntities->isEmpty()) {
                $reason = $this->subscriptions->renewalEligibilityBlockReason($user);
                foreach ($renewalPlans as $plan) {
                    $renewalPurchaseBlocks[$plan->id] = $renewalPurchaseBlocks[$plan->id] ?? $reason;
                }
            }
        }

        return view('public.pricing.index', compact(
            'sections',
            'registrationPlans',
            'renewalPlans',
            'matrixRows',
            'pricingFaqs',
            'purchaseBlocks',
            'renewalPurchaseBlocks',
            'activeRegistrationSubscription',
            'activeRenewalSubscription',
            'eligibleRenewalEntities',
            'renewalLei',
        ));
    }
}

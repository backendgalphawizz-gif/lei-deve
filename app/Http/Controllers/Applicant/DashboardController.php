<?php

namespace App\Http\Controllers\Applicant;

use App\Services\ApplicantApplicationService;
use App\Services\SubscriptionService;

class DashboardController extends ApplicantPortalController
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

        $entities = $this->applications->entitiesForUser($user);
        $subscriptions = $user->subscriptions()->latest()->get();
        $renewalEligibleIds = $this->subscriptions->eligibleEntitiesForRenewal($user)->pluck('id')->all();
        $window = $this->subscriptions->renewalWindowDays();

        $stats = [
            'total' => $entities->count(),
            'active' => $entities->where('status', 'approved')->filter(fn ($e) => ! $e->expiry_date || $e->expiry_date->isFuture())->count(),
            'lapsed' => $entities->filter(fn ($e) => $e->expiry_date && $e->expiry_date->isPast())->count(),
            'pending_renewal' => $entities->filter(fn ($e) => $this->subscriptions->isEntityEligibleForRenewal($e) && $e->expiry_date?->isFuture())->count(),
        ];

        return view('applicant.dashboard.index', compact(
            'user',
            'entities',
            'subscriptions',
            'stats',
            'renewalEligibleIds',
            'window',
        ));
    }
}

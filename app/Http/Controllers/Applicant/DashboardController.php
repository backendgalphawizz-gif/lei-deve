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

        $activeLeis = $entities
            ->filter(fn ($e) => $e->lei_number)
            ->values();

        $accountLei = null;
        if ($user->lei_number && $activeLeis->where('lei_number', $user->lei_number)->isEmpty()) {
            $accountLei = [
                'lei_number' => $user->lei_number,
                'entity_name' => $user->organization_name ?: $user->name,
                'status' => 'account',
            ];
        }

        // Profile completion score (0–100)
        $profileFields = ['name', 'email', 'phone', 'organization_name', 'lei_number', 'country_of_incorporation'];
        $filled = collect($profileFields)->filter(fn ($f) => ! empty($user->$f))->count();
        $profileCompletion = (int) round(($filled / count($profileFields)) * 100);

        $hasSubmittedRegistration = $this->applications->hasSubmittedRegistration($user);
        $submittedRegistration = $hasSubmittedRegistration
            ? $this->applications->submittedRegistration($user)
            : null;

        $showAssignedLei = (bool) $user->lei_number;
        $highlightAssignedLei = session()->pull('lei_show_on_dashboard', false);

        return view('applicant.dashboard.index', compact(
            'user',
            'entities',
            'subscriptions',
            'stats',
            'renewalEligibleIds',
            'window',
            'activeLeis',
            'accountLei',
            'profileCompletion',
            'hasSubmittedRegistration',
            'submittedRegistration',
            'showAssignedLei',
            'highlightAssignedLei',
        ));
    }
}

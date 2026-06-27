<?php

namespace App\Http\Controllers\Applicant;

use App\Services\ApplicantApplicationService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class RenewalController extends ApplicantPortalController
{
    public function __construct(
        ApplicantApplicationService $applications,
        private SubscriptionService $subscriptions,
    ) {
        parent::__construct($applications);
    }

    public function step(Request $request, int $step)
    {
        $this->sharePortalContext();
        $step = max(1, min(4, $step));
        $user = auth()->user();

        $draft = $this->applications->activeDraft($user, 'renewal');
        $eligibleEntities = $this->subscriptions->eligibleEntitiesForRenewal($user);

        if ($eligibleEntities->isEmpty() && ! $draft) {
            return redirect()
                ->route('applicant.payments.index')
                ->with('info', 'Renewal is available when your LEI expires' . ($this->subscriptions->renewalWindowDays() > 0 ? ' or enters the renewal window.' : '.'));
        }

        $subscription = $this->applications->renewalSubscription($user, $draft);

        if (! $subscription) {
            return redirect()
                ->route('applicant.payments.index', ['#renewal'])
                ->with('info', 'Purchase a renewal plan to continue.');
        }

        $prefill = null;
        if ($request->filled('lei')) {
            $prefill = $eligibleEntities->first(fn ($e) => $e->lei_number === $request->string('lei')->toString())
                ?: $this->applications->findApprovedEntity($user, $request->string('lei')->toString());
        }

        $application = $this->applications->startRenewal(
            $user,
            $subscription,
            $prefill?->lei_number,
            $prefill?->entity_name,
            $prefill?->country,
        );

        if ($prefill && empty($application->draft_data['lei_search'])) {
            $this->applications->saveStep($application, max(1, (int) $application->workflow_step), [
                'lei_search' => $prefill->lei_number,
                'lei_number' => $prefill->lei_number,
                'entity_name' => $prefill->entity_name,
            ]);
            $application = $application->fresh();
        }

        if ($step > (int) $application->workflow_step + 1) {
            return redirect()->route('applicant.renewal.step', ['step' => $application->workflow_step]);
        }

        $draft = $application->draft_data ?? [];
        $approvedEntities = $eligibleEntities;

        return view('applicant.renewal.step' . $step, compact(
            'application',
            'draft',
            'step',
            'subscription',
            'approvedEntities',
        ));
    }

    public function save(Request $request, int $step)
    {
        $step = max(1, min(4, $step));
        $user = auth()->user();

        $existingDraft = $this->applications->activeDraft($user, 'renewal');
        $subscription = $this->applications->renewalSubscription($user, $existingDraft);

        if (! $subscription) {
            return redirect()
                ->route('applicant.payments.index', ['#renewal'])
                ->with('error', 'An active renewal subscription is required.');
        }

        $application = $this->applications->startRenewal($user, $subscription);

        $rules = match ($step) {
            1 => [
                'lei_search' => ['required', 'string', 'max:120'],
            ],
            2 => [
                'renewal_years' => ['nullable', 'in:1,3,5'],
                'modify_entity' => ['nullable', 'boolean'],
            ],
            3 => [
                'renewal_certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            ],
            default => [],
        };

        $data = $request->validate($rules);

        if ($step === 1) {
            $entity = $this->subscriptions->eligibleEntitiesForRenewal($user)
                ->first(fn ($e) => $e->lei_number === $data['lei_search'] || str_contains(strtolower($e->entity_name), strtolower($data['lei_search']))
                    || $e->lei_number === $data['lei_search']);

            if (! $entity) {
                $entity = $this->applications->findApprovedEntity($user, $data['lei_search']);
            }

            if (! $entity || ! $this->subscriptions->isEntityEligibleForRenewal($entity)) {
                return back()
                    ->withInput()
                    ->with('error', 'This LEI is not eligible for renewal yet. Check Payment Management for available renewal plans.');
            }

            $data['lei_number'] = $entity->lei_number;
            $data['entity_name'] = $entity->entity_name;
            $data['country'] = $entity->country;
        }

        if ($step === 3 && $request->hasFile('renewal_certificate')) {
            $data['renewal_certificate'] = $request->file('renewal_certificate')->store('uploads/applicant-documents', 'public');
        }

        $nextStep = min(4, $step + 1);
        $this->applications->saveStep($application, $nextStep, $data);

        if ($step === 4 && $request->boolean('submit')) {
            if (! $application->lei_number) {
                return back()->with('error', 'Select a valid LEI before submitting your renewal.');
            }

            $this->applications->submitRenewal($application->fresh());

            return redirect()
                ->route('applicant.applications.show', $application)
                ->with('success', 'Renewal submitted successfully.');
        }

        if ($step >= 4) {
            return back()->with('success', 'Renewal saved.');
        }

        return redirect()
            ->route('applicant.renewal.step', ['step' => $nextStep])
            ->with('success', 'Step ' . $step . ' saved.');
    }
}

<?php

namespace App\Services;

use App\Models\LeiApplication;
use App\Models\User;

class ApplicantPortalRedirect
{
    public function __construct(
        protected ApplicantApplicationService $applications,
        protected SubscriptionService $subscriptions,
    ) {}

    public function url(User $user): string
    {
        $draft = $this->applications->activeDraft($user);

        if ($draft) {
            return $this->workflowUrl($draft);
        }

        if ($this->applications->registrationSubscription($user)
            && ! LeiApplication::query()
                ->where('user_id', $user->id)
                ->where('workflow_type', 'registration')
                ->whereNotIn('status', ['draft', 'rejected'])
                ->exists()) {
            $subscription = $this->applications->registrationSubscription($user);
            $this->applications->startRegistration($user, $subscription);

            return route('applicant.registration.step', ['step' => 1]);
        }

        if ($this->applications->renewalSubscription($user)
            && ! $this->applications->activeDraft($user, 'renewal')) {
            $subscription = $this->applications->renewalSubscription($user);
            $this->applications->startRenewal($user, $subscription);

            return route('applicant.renewal.step', ['step' => 1]);
        }

        return route('applicant.dashboard');
    }

    public function redirect(User $user, ?string $message = null)
    {
        return redirect($this->url($user))->with('success', $message);
    }

    private function workflowUrl(LeiApplication $application): string
    {
        $step = max(1, min(4, (int) $application->workflow_step));

        return match ($application->workflow_type) {
            'renewal' => route('applicant.renewal.step', ['step' => $step]),
            default => route('applicant.registration.step', ['step' => $step]),
        };
    }
}

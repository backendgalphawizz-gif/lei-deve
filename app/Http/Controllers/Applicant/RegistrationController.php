<?php

namespace App\Http\Controllers\Applicant;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RegistrationController extends ApplicantPortalController
{
    public function step(Request $request, int $step)
    {
        $this->sharePortalContext();
        $step = max(1, min(4, $step));
        $user = auth()->user();

        $draft = $this->applications->activeDraft($user, 'registration');
        $subscription = $this->applications->registrationSubscription($user, $draft);

        if (! $subscription) {
            return redirect()
                ->route('applicant.payments.index')
                ->with('info', 'Purchase a registration plan to begin your LEI application.');
        }

        $application = $this->applications->startRegistration($user, $subscription);

        if ($step > (int) $application->workflow_step + 1) {
            return redirect()->route('applicant.registration.step', ['step' => $application->workflow_step]);
        }

        $draft = $application->draft_data ?? [];

        return view('applicant.registration.step' . $step, compact('application', 'draft', 'step', 'subscription'));
    }

    public function save(Request $request, int $step)
    {
        $step = max(1, min(4, $step));
        $user = auth()->user();

        $existingDraft = $this->applications->activeDraft($user, 'registration');
        $subscription = $this->applications->registrationSubscription($user, $existingDraft);

        if (! $subscription) {
            return redirect()
                ->route('applicant.payments.index')
                ->with('error', 'An active registration subscription is required.');
        }

        $application = $this->applications->startRegistration($user, $subscription);

        if ($step === 2 && $request->boolean('draft')) {
            $data = $request->validate([
                'certificate_of_incorporation' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
                'articles_of_association' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
                'proof_of_authority_type' => ['nullable', 'string', Rule::in(['poa', 'registry_extract', 'letter_of_authorization'])],
                'proof_of_authority' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            ]);

            foreach (['certificate_of_incorporation', 'articles_of_association', 'proof_of_authority'] as $field) {
                if ($request->hasFile($field)) {
                    $data[$field] = $request->file($field)->store('uploads/applicant-documents', 'public');
                }
            }

            $this->applications->saveStep($application, 2, $data);

            return back()->with('success', 'Draft saved. You can continue when ready.');
        }

        $rules = match ($step) {
            1 => [
                'entity_name' => ['required', 'string', 'max:255'],
                'registration_authority' => ['required', 'string', 'max:120'],
                'registration_number' => ['required', 'string', 'max:80'],
                'registered_address' => ['required', 'string', 'max:500'],
                'country' => ['required', 'string', 'max:80'],
                'entity_type' => ['required', 'string', 'max:80'],
            ],
            2 => (function () use ($application) {
                $draft = $application->draft_data ?? [];
                $hasCertificate = ! empty($draft['certificate_of_incorporation']);
                $hasProof = ! empty($draft['proof_of_authority']);

                return [
                    'certificate_of_incorporation' => [
                        $hasCertificate ? 'nullable' : 'required',
                        'file',
                        'mimes:pdf,jpg,jpeg,png',
                        'max:10240',
                    ],
                    'articles_of_association' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
                    'proof_of_authority_type' => [
                        'required',
                        'string',
                        Rule::in(['poa', 'registry_extract', 'letter_of_authorization']),
                    ],
                    'proof_of_authority' => [
                        $hasProof ? 'nullable' : 'required',
                        'file',
                        'mimes:pdf,jpg,jpeg,png',
                        'max:10240',
                    ],
                ];
            })(),
            3 => [
                'authority_confirmed' => ['accepted'],
                'accuracy_confirmed' => ['accepted'],
                'terms_confirmed' => ['accepted'],
                'signature_name' => ['required', 'string', 'max:200'],
            ],
            default => [],
        };

        $data = $request->validate($rules);

        if ($step === 2) {
            foreach (['certificate_of_incorporation', 'articles_of_association', 'proof_of_authority'] as $field) {
                if ($request->hasFile($field)) {
                    $data[$field] = $request->file($field)->store('uploads/applicant-documents', 'public');
                }
            }
        }

        if ($step === 3) {
            $data['authority_confirmed'] = true;
            $data['accuracy_confirmed'] = true;
            $data['terms_confirmed'] = true;
        }

        $nextStep = min(4, $step + 1);
        $this->applications->saveStep($application, $nextStep, $data);

        if ($step === 4 && $request->boolean('submit')) {
            $this->applications->submitRegistration($application->fresh());

            return redirect()
                ->route('applicant.applications.show', $application)
                ->with('success', 'Your LEI registration has been submitted for review.');
        }

        if ($step >= 4) {
            return back()->with('success', 'Application saved.');
        }

        return redirect()
            ->route('applicant.registration.step', ['step' => $nextStep])
            ->with('success', 'Step ' . $step . ' saved. Continue to the next step.');
    }
}

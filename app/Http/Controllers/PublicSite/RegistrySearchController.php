<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ApplicantApplicationService;
use App\Services\GleifRegistrationPrefillService;
use App\Services\PublicRegistrySearchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RegistrySearchController extends Controller
{
    public function __construct(
        private PublicRegistrySearchService $registry,
        private GleifRegistrationPrefillService $prefill,
        private ApplicantApplicationService $applications,
    ) {}

    public function index(Request $request)
    {
        $query = $request->string('q')->trim()->toString();
        $type = $request->string('type')->toString() ?: 'all';

        $results = null;
        if ($query !== '') {
            $results = $this->registry->search($query, $type);
        }

        return view('public.registry.search', [
            'query' => $query,
            'type' => $type,
            'results' => $results,
            'registry' => $this->registry,
        ]);
    }

    public function suggest(Request $request)
    {
        $query = $request->string('q')->trim()->toString();
        $type = $request->string('type')->toString() ?: 'all';

        if (mb_strlen($query) < 2) {
            return response()->json(['items' => []]);
        }

        $items = $this->registry->suggest($query, $type);

        return response()->json([
            'items' => $items,
            'more_url' => route('registry.search', ['q' => $query, 'type' => $type]),
        ]);
    }

    public function show(string $leiNumber)
    {
        $resolved = $this->registry->resolveRecord($leiNumber);
        abort_unless($resolved, 404, 'No LEI record found in our registry or GLEIF global index.');

        if ($resolved['source'] === 'local') {
            return view('public.registry.show', [
                'application' => $resolved['application'],
                'record' => $resolved['record'],
                'registry' => $this->registry,
                'source' => 'local',
            ]);
        }

        return view('public.registry.show-gleif', [
            'record' => $resolved['record'],
            'registry' => $this->registry,
            'source' => 'gleif',
        ]);
    }

    public function registerWithUs(Request $request): RedirectResponse
    {
        $prefill = $this->prefill->fromRequest($request);

        if (! $prefill) {
            return redirect()->route('register')
                ->with('info', 'Start your LEI registration below. A new LEI code will be assigned when you create your account.');
        }

        $this->prefill->store($prefill);

        $user = auth()->user();
        if ($user instanceof User && $user->isApplicant()) {
            return $this->redirectAuthenticatedApplicant($user);
        }

        $message = ! empty($prefill['source_lei'])
            ? 'Create your account to register this entity with our LOU. You may edit the entity name before submitting — a new LEI code will be assigned to your account (not the existing GLEIF code).'
            : 'Create your account to continue. A new LEI code will be assigned when you sign up.';

        return redirect()->route('register')->with('info', $message);
    }

    private function redirectAuthenticatedApplicant(User $user): RedirectResponse
    {
        if ($this->applications->hasSubmittedRegistration($user)) {
            $submitted = $this->applications->submittedRegistration($user);

            return redirect()
                ->route('applicant.applications.show', $submitted)
                ->with('info', 'You have already submitted a LEI registration for this account.');
        }

        $subscription = $this->applications->registrationSubscription($user);
        if (! $subscription) {
            return redirect()
                ->route('applicant.payments.index')
                ->with('info', 'Purchase a registration plan to continue. Your entity details have been saved — review them on step 1 after checkout.');
        }

        $application = $this->applications->startRegistration($user, $subscription);
        $this->applications->applyRegistrationPrefill($application);

        $message = $user->lei_number
            ? 'Review and edit your entity details below. Your new LEI code is '.$user->lei_number.'.'
            : 'Review and edit your entity details below before submitting your application.';

        return redirect()
            ->route('applicant.registration.step', ['step' => 1])
            ->with('info', $message);
    }
}

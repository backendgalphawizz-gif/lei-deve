@extends('applicant.layouts.app')

@section('title', 'Review & Submit Renewal')

@section('content')
@include('applicant.partials.stepper', [
    'currentStep' => 4,
    'routeName' => 'applicant.renewal.step',
    'steps' => [1 => 'LEI Search', 2 => 'Renewal Request', 3 => 'Documentation', 4 => 'Submit'],
])

<form method="POST" action="{{ route('applicant.renewal.save', ['step' => 4]) }}" class="lei-portal-split">
    @csrf
    <div>
        <div class="lei-portal-card">
            <h2>Review Renewal</h2>
            <p><strong>Entity:</strong> {{ $application->entity_name }}</p>
            <p><strong>LEI Number:</strong> {{ $application->lei_number }}</p>
            <p><strong>Country:</strong> {{ $application->country }}</p>
            @if (! empty($draft['renewal_certificate']))
                <p><strong>Certificate:</strong> <a href="{{ asset('storage/' . $draft['renewal_certificate']) }}" target="_blank" rel="noopener">View uploaded file</a></p>
            @endif
        </div>
        <div class="lei-portal-actions">
            <a href="{{ route('applicant.renewal.step', ['step' => 3]) }}" class="lei-btn-secondary">Back to Documentation</a>
            <button type="submit" name="submit" value="1" class="lei-btn-primary"><i class="fa-solid fa-paper-plane"></i> Submit Renewal</button>
        </div>
    </div>
    @include('applicant.partials.subscription-summary', ['subscription' => $subscription, 'title' => 'Renewal Summary'])
</form>
@endsection

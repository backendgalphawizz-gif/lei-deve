@extends('applicant.layouts.app')

@section('title', 'LEI Renewal')

@section('content')
@include('applicant.partials.stepper', [
    'currentStep' => 1,
    'routeName' => 'applicant.renewal.step',
    'steps' => [1 => 'LEI Search', 2 => 'Renewal Request', 3 => 'Documentation', 4 => 'Submit'],
])

<form method="POST" action="{{ route('applicant.renewal.save', ['step' => 1]) }}" class="lei-portal-split">
    @csrf
    <div class="lei-portal-card">
        <h2>Select LEI to Renew</h2>
        <div class="lei-portal-field">
            <label for="lei_search">Enter LEI Number or Entity Name</label>
            <div style="display:flex;gap:10px;">
                <input id="lei_search" name="lei_search" value="{{ old('lei_search', $draft['lei_search'] ?? $application->lei_number ?? '') }}" placeholder="Enter LEI Number or Entity Name..." required>
                <button type="submit" class="lei-btn-primary">Search</button>
            </div>
        </div>
        @if ($approvedEntities->isEmpty())
            <p class="muted">You need at least one approved LEI before you can renew. Complete a registration first.</p>
        @else
            <p class="muted">Select one of your approved LEIs below or search above.</p>
            <div style="margin-top:16px;display:grid;gap:10px;">
                @foreach ($approvedEntities as $entity)
                    <label class="lei-portal-card" style="margin:0;cursor:pointer;display:flex;gap:12px;align-items:flex-start;">
                        <input type="radio" name="lei_search" value="{{ $entity->lei_number }}" {{ ($draft['lei_number'] ?? $application->lei_number) === $entity->lei_number ? 'checked' : '' }}>
                        <span>
                            <strong>{{ $entity->entity_name }}</strong><br>
                            <span class="muted">{{ $entity->lei_number }}</span>
                            @if ($entity->expiry_date)
                                <br><small>Expires {{ $entity->expiry_date->format('M j, Y') }}</small>
                            @endif
                        </span>
                    </label>
                @endforeach
            </div>
        @endif
        @if (! empty($draft['entity_name']) || $application->lei_number)
            <div class="lei-portal-card" style="margin-top:16px;background:#f8fafc;">
                <strong>{{ $draft['entity_name'] ?? $application->entity_name }}</strong>
                <div>{{ $draft['lei_number'] ?? $application->lei_number }}</div>
            </div>
        @endif
    </div>
    <aside class="lei-portal-summary">
        <h3>Renewal Guidelines</h3>
        <ul>
            <li>Only your approved LEIs can be renewed here.</li>
            <li>Your renewal plan: {{ $subscription->plan_name }}.</li>
            <li>Admin will extend your LEI expiry after review.</li>
        </ul>
        @if ($approvedEntities->isNotEmpty())
            <div class="lei-portal-actions" style="border:0;padding-top:0;">
                <button type="submit" class="lei-btn-primary full" style="width:100%;justify-content:center;">Next Step</button>
            </div>
        @endif
    </aside>
</form>
@endsection

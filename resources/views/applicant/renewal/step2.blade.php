@extends('applicant.layouts.app')

@section('title', 'Renewal Request')

@section('content')
@include('applicant.partials.stepper', [
    'currentStep' => 2,
    'routeName' => 'applicant.renewal.step',
    'steps' => [1 => 'LEI Search', 2 => 'Renewal Request', 3 => 'Documentation', 4 => 'Payment'],
])

<form method="POST" action="{{ route('applicant.renewal.save', ['step' => 2]) }}" class="lei-portal-split">
    @csrf
    <div>
        <div class="lei-portal-card">
            <h2>Renewal Details</h2>
            <p><strong>{{ $draft['entity_name'] ?? $application->entity_name }}</strong></p>
            <p>{{ $draft['registered_address'] ?? 'Level 12, Tower B, Canary Wharf, London, E14 5AB, United Kingdom' }}</p>
            <label style="display:flex;gap:8px;margin-top:16px;">
                <input type="checkbox" name="modify_entity" value="1">
                I need to modify my entity details (legal name, address, etc.)
            </label>
        </div>
        <div class="lei-portal-card">
            <h3>Renewal Period</h3>
            @foreach ([1 => 150, 3 => 400, 5 => 650] as $years => $price)
                <label style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #e5e7eb;">
                    <span><input type="radio" name="renewal_years" value="{{ $years }}" @checked(old('renewal_years', $draft['renewal_years'] ?? 1) == $years)> {{ $years }} Year{{ $years > 1 ? 's' : '' }}</span>
                    <strong>${{ $price }}</strong>
                </label>
            @endforeach
        </div>
        <div class="lei-portal-actions">
            <a href="{{ route('applicant.renewal.step', ['step' => 1]) }}" class="lei-btn-secondary">Back to Search</a>
            <button type="submit" class="lei-btn-primary">Save & Continue to Step 3</button>
        </div>
    </div>
    <aside class="lei-portal-summary">
        <h3>Renewal Summary</h3>
        <div class="lei-portal-summary-row"><span>Renewal Fee (1 Year)</span><span>$135.00</span></div>
        <div class="lei-portal-summary-row"><span>GLEIF Administrative Fee</span><span>$11.00</span></div>
        <div class="lei-portal-summary-total"><span>Total Amount</span><span>$150.00</span></div>
    </aside>
</form>
@endsection

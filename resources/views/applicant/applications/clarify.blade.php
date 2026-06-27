@extends('applicant.layouts.app')

@section('title', 'Clarification Requested')

@section('content')
<a href="{{ route('applicant.applications.show', $application) }}" class="lei-portal-back">
    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
    Back to Application
</a>

<div class="lei-portal-page-head">
    <div>
        <p class="lei-portal-eyebrow">Clarification Request</p>
        <h1>{{ $application->entity_name }}</h1>
        <p class="lei-portal-ref">Reference: <strong>{{ $application->application_code }}</strong></p>
    </div>
    <span class="lei-portal-badge lei-portal-page-badge red">Clarify</span>
</div>

<div class="lei-portal-split">
    <div class="lei-portal-card">
        <div class="lei-portal-alert lei-portal-alert--warning lei-portal-alert--flat">
            <i class="fa-solid fa-message" aria-hidden="true"></i>
            <div>
                <strong>Message from review team</strong>
                <span>Please provide the ultimate consolidated financial statement or an equivalent document to verify the direct parent relationship.</span>
            </div>
        </div>

        <form method="POST" action="{{ route('applicant.applications.clarify.submit', $application) }}" enctype="multipart/form-data">
            @csrf
            <div class="lei-portal-field">
                <label for="response">Your Response</label>
                <textarea id="response" name="response" placeholder="Provide context or explanation regarding the uploaded files..." required>{{ old('response') }}</textarea>
            </div>
            <div class="lei-portal-field">
                <label>Supporting Documents</label>
                <label class="lei-portal-upload">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <div>Upload supporting files (PDF, JPG, PNG up to 10MB)</div>
                    <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" hidden>
                </label>
            </div>
            <div class="lei-portal-actions">
                <a href="{{ route('applicant.applications.show', $application) }}" class="lei-btn-secondary">Cancel</a>
                <button type="submit" class="lei-btn-primary">Submit Response</button>
            </div>
        </form>
    </div>

    <aside class="lei-portal-summary lei-portal-summary--sticky">
        <h3>Application Summary</h3>
        <dl class="lei-portal-dl">
            <div class="lei-portal-dl-row">
                <dt>Entity</dt>
                <dd>{{ $application->entity_name }}</dd>
            </div>
            <div class="lei-portal-dl-row">
                <dt>Country</dt>
                <dd>{{ $application->country }}</dd>
            </div>
            <div class="lei-portal-dl-row">
                <dt>Submitted</dt>
                <dd>{{ $application->submitted_on?->format('M j, Y') ?? '—' }}</dd>
            </div>
        </dl>
    </aside>
</div>
@endsection

@extends('applicant.layouts.app')

@section('title', 'LEI Application')

@section('content')
<div class="lei-portal-page-head">
    <div>
        <p class="lei-portal-eyebrow">One-Time Application</p>
        <h1>Complete Your LEI Application</h1>
        <p>Fill in all details below and submit once. You will not need to return to complete additional steps.</p>
    </div>
</div>

@if ($errors->any())
    <div class="lei-portal-alert lei-portal-alert--error" role="alert" style="margin-bottom:20px;">
        <i class="fa-solid fa-circle-exclamation"></i>
        <ul style="margin:0;padding-left:18px;">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<form method="POST" action="{{ route('applicant.registration.apply.submit') }}" enctype="multipart/form-data" class="lei-portal-card">
    @csrf

    <h2 style="margin-bottom:16px;">1. Entity Information</h2>
    <div class="lei-portal-form-grid">
        <div class="lei-portal-field full">
            <label for="entity_name">Legal Entity Name <span class="lei-field-required">*</span></label>
            <input id="entity_name" name="entity_name" value="{{ old('entity_name', $draft['entity_name'] ?? $application->entity_name) }}" required>
        </div>
        <div class="lei-portal-field">
            <label for="registration_authority">Registration Authority <span class="lei-field-required">*</span></label>
            <input id="registration_authority" name="registration_authority" value="{{ old('registration_authority', $draft['registration_authority'] ?? '') }}" required>
        </div>
        <div class="lei-portal-field">
            <label for="registration_number">Registration / CIN Number <span class="lei-field-required">*</span></label>
            <input id="registration_number" name="registration_number" value="{{ old('registration_number', $draft['registration_number'] ?? '') }}" required>
        </div>
        <div class="lei-portal-field full">
            <label for="registered_address">Registered Address <span class="lei-field-required">*</span></label>
            <input id="registered_address" name="registered_address" value="{{ old('registered_address', $draft['registered_address'] ?? '') }}" required>
        </div>
        <div class="lei-portal-field">
            <label for="country">Country <span class="lei-field-required">*</span></label>
            <input id="country" name="country" value="{{ old('country', $draft['country'] ?? $application->country) }}" required>
        </div>
        <div class="lei-portal-field">
            <label for="entity_type">Entity Type <span class="lei-field-required">*</span></label>
            <select id="entity_type" name="entity_type" required>
                @foreach (['Limited Liability Company', 'Public Limited Company', 'Partnership', 'Trust', 'Sole Proprietorship', 'Government Entity', 'Non-Profit Organisation', 'Other'] as $type)
                    <option value="{{ $type }}" @selected(old('entity_type', $draft['entity_type'] ?? 'Limited Liability Company') === $type)>{{ $type }}</option>
                @endforeach
            </select>
        </div>
        <div class="lei-portal-field">
            <label for="authorized_person_role">Authorized Person Role <small class="muted">(ISO 17442-2 OID role)</small></label>
            <input id="authorized_person_role" name="authorized_person_role" placeholder="e.g. CEO, Director" value="{{ old('authorized_person_role', $draft['authorized_person_role'] ?? '') }}">
        </div>
    </div>

    <hr style="margin:28px 0;border:none;border-top:1px solid #e2e8f0;">

    <h2 style="margin-bottom:16px;">2. Documents</h2>
    <div class="lei-portal-form-grid">
        <div class="lei-portal-field full">
            <label>Certificate of Incorporation <span class="lei-field-required">*</span></label>
            @if (!empty($draft['certificate_of_incorporation']))
                <p class="muted" style="font-size:12px;margin-bottom:6px;">Uploaded: {{ basename($draft['certificate_of_incorporation']) }}</p>
            @endif
            <input type="file" name="certificate_of_incorporation" accept=".pdf,.jpg,.jpeg,.png" {{ empty($draft['certificate_of_incorporation']) ? 'required' : '' }}>
        </div>
        <div class="lei-portal-field full">
            <label>Articles of Association <span class="muted">(optional)</span></label>
            <input type="file" name="articles_of_association" accept=".pdf,.jpg,.jpeg,.png">
        </div>
        <div class="lei-portal-field">
            <label for="proof_of_authority_type">Proof of Authority Type <span class="lei-field-required">*</span></label>
            <select id="proof_of_authority_type" name="proof_of_authority_type" required>
                <option value="">Select…</option>
                @foreach (['poa' => 'Power of Attorney', 'registry_extract' => 'Registry Extract', 'letter_of_authorization' => 'Letter of Authorization'] as $val => $label)
                    <option value="{{ $val }}" @selected(old('proof_of_authority_type', $draft['proof_of_authority_type'] ?? '') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="lei-portal-field">
            <label>Proof of Authority File <span class="lei-field-required">*</span></label>
            <input type="file" name="proof_of_authority" accept=".pdf,.jpg,.jpeg,.png" {{ empty($draft['proof_of_authority']) ? 'required' : '' }}>
        </div>
    </div>

    <hr style="margin:28px 0;border:none;border-top:1px solid #e2e8f0;">

    <h2 style="margin-bottom:16px;">3. Declaration &amp; Submit</h2>
    <div class="lei-portal-checklist">
        <label class="lei-portal-check"><input type="checkbox" name="authority_confirmed" value="1" required> I confirm legal authority to represent this entity.</label>
        <label class="lei-portal-check"><input type="checkbox" name="accuracy_confirmed" value="1" required> I certify all information is accurate and complete.</label>
        <label class="lei-portal-check"><input type="checkbox" name="terms_confirmed" value="1" required> I agree to the Terms of Service and Privacy Policy.</label>
    </div>
    <div class="lei-portal-field" style="margin-top:16px;">
        <label for="signature_name">Full Name (Electronic Signature) <span class="lei-field-required">*</span></label>
        <input id="signature_name" name="signature_name" value="{{ old('signature_name', $draft['signature_name'] ?? auth()->user()->name) }}" required>
    </div>

    <div class="lei-portal-actions" style="margin-top:24px;">
        <a href="{{ route('applicant.dashboard') }}" class="lei-btn-link">Cancel</a>
        <button type="submit" class="lei-btn-primary" data-loading="Submitting…">
            <i class="fa-solid fa-paper-plane"></i> Submit Application
        </button>
    </div>
</form>
@endsection

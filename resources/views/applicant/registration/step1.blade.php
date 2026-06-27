@extends('applicant.layouts.app')

@section('title', 'New LEI Registration')

@section('content')
@include('applicant.partials.stepper', ['currentStep' => 1, 'routeName' => 'applicant.registration.step'])

<form method="POST" action="{{ route('applicant.registration.save', ['step' => 1]) }}" class="lei-portal-card">
    @csrf
    <h2>Company Information</h2>
    <div class="lei-portal-form-grid">
        <div class="lei-portal-field full">
            <label for="entity_name">Legal Entity Name</label>
            <input id="entity_name" name="entity_name" value="{{ old('entity_name', $draft['entity_name'] ?? $application->entity_name) }}" required>
        </div>
        <div class="lei-portal-field">
            <label for="registration_authority">Registration Authority</label>
            <input id="registration_authority" name="registration_authority" value="{{ old('registration_authority', $draft['registration_authority'] ?? 'UK Companies House (RA000585)') }}" required>
        </div>
        <div class="lei-portal-field">
            <label for="registration_number">Registration Number</label>
            <input id="registration_number" name="registration_number" value="{{ old('registration_number', $draft['registration_number'] ?? '') }}" required>
        </div>
        <div class="lei-portal-field full">
            <label for="registered_address">Registered Address</label>
            <input id="registered_address" name="registered_address" value="{{ old('registered_address', $draft['registered_address'] ?? '') }}" required>
        </div>
        <div class="lei-portal-field">
            <label for="country">Country</label>
            <input id="country" name="country" value="{{ old('country', $draft['country'] ?? $application->country) }}" required>
        </div>
        <div class="lei-portal-field">
            <label for="entity_type">Entity Type</label>
            <select id="entity_type" name="entity_type" required>
                @foreach (['Limited Liability Company', 'Public Limited Company', 'Partnership', 'Trust'] as $type)
                    <option value="{{ $type }}" @selected(old('entity_type', $draft['entity_type'] ?? 'Limited Liability Company') === $type)>{{ $type }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="lei-portal-actions">
        <a href="{{ route('applicant.dashboard') }}" class="lei-btn-link">Cancel</a>
        <div class="right">
            <button type="submit" class="lei-btn-primary">Next Step <i class="fa-solid fa-arrow-right"></i></button>
        </div>
    </div>
</form>
@endsection

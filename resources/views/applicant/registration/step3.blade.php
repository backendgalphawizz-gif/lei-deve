@extends('applicant.layouts.app')

@section('title', 'Declaration of Authorization')

@section('content')
@include('applicant.partials.stepper', ['currentStep' => 3, 'routeName' => 'applicant.registration.step'])

<form method="POST" action="{{ route('applicant.registration.save', ['step' => 3]) }}" class="lei-portal-card lei-portal-declaration">
    @csrf
    <h2>Declaration of Authorization</h2>

    <blockquote class="lei-portal-declaration-quote">
        I, the undersigned, acting as an authorized representative of the entity described in this application,
        hereby declare that I possess the necessary legal authority to apply for, manage, and maintain a Legal
        Entity Identifier (LEI) on behalf of said entity. I understand that the information provided will be
        submitted to the Global Legal Entity Identifier Foundation (GLEIF) and will be made publicly available
        as part of the Global LEI System.
    </blockquote>

    <div class="lei-portal-checklist">
        <label class="lei-portal-check">
            <input type="checkbox" name="authority_confirmed" value="1" @checked(old('authority_confirmed', $draft['authority_confirmed'] ?? false)) required>
            <span>I confirm that I have the legal authority to represent the entity.</span>
        </label>
        <label class="lei-portal-check">
            <input type="checkbox" name="accuracy_confirmed" value="1" @checked(old('accuracy_confirmed', $draft['accuracy_confirmed'] ?? false)) required>
            <span>I certify that all information provided is accurate and complete to the best of my knowledge.</span>
        </label>
        <label class="lei-portal-check">
            <input type="checkbox" name="terms_confirmed" value="1" @checked(old('terms_confirmed', $draft['terms_confirmed'] ?? false)) required>
            <span>
                I agree to the
                <a href="{{ route('pages.show', 'terms-of-service') }}" target="_blank" rel="noopener">Terms of Service</a>
                and
                <a href="{{ route('pages.show', 'privacy-policy') }}" target="_blank" rel="noopener">Privacy Policy</a>.
            </span>
        </label>
    </div>
    @error('authority_confirmed')<p class="lei-portal-field-error">{{ $message }}</p>@enderror
    @error('accuracy_confirmed')<p class="lei-portal-field-error">{{ $message }}</p>@enderror
    @error('terms_confirmed')<p class="lei-portal-field-error">{{ $message }}</p>@enderror

    <div class="lei-portal-form-grid">
        <div class="lei-portal-field">
            <label for="signature_name">Full Name (Digital Signature)</label>
            <input id="signature_name" name="signature_name" placeholder="Type your full legal name" value="{{ old('signature_name', $draft['signature_name'] ?? auth()->user()->name) }}" required>
            <p class="lei-portal-field-hint">By typing your name, you are providing a legally binding electronic signature.</p>
        </div>
        <div class="lei-portal-field">
            <label for="submission_date">Date of Submission</label>
            <input id="submission_date" value="{{ now()->format('F j, Y') }}" disabled>
        </div>
    </div>
    @error('signature_name')<p class="lei-portal-field-error">{{ $message }}</p>@enderror

    <div class="lei-portal-actions">
        <a href="{{ route('applicant.registration.step', ['step' => 2]) }}" class="lei-btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back to Documents</a>
        <button type="submit" class="lei-btn-primary">Next Step <i class="fa-solid fa-arrow-right"></i></button>
    </div>
</form>
@endsection

@extends('applicant.layouts.app')

@section('title', 'Document Upload')

@section('content')
@include('applicant.partials.stepper', ['currentStep' => 2, 'routeName' => 'applicant.registration.step'])

@php
    $proofTypes = [
        'poa' => 'Power of Attorney (PoA)',
        'registry_extract' => 'Extract from official registry showing authorized signatories',
        'letter_of_authorization' => 'Letter of Authorization signed by a director',
    ];
    $certificatePath = $draft['certificate_of_incorporation'] ?? null;
    $articlesPath = $draft['articles_of_association'] ?? null;
    $proofPath = $draft['proof_of_authority'] ?? null;
@endphp

<form method="POST" action="{{ route('applicant.registration.save', ['step' => 2]) }}" enctype="multipart/form-data" class="lei-portal-split lei-portal-split--docs">
    @csrf

    <div class="lei-portal-wizard">
        {{-- 1. Certificate of Incorporation (Required) --}}
        <div class="lei-portal-doc-card">
            <div class="lei-portal-doc-card-head">
                <h3>Certificate of Incorporation</h3>
                <span class="lei-portal-tag required">Required</span>
            </div>
            <p class="lei-portal-doc-desc">
                Please provide the official certificate issued by your local registry. Ensure the legal name
                and registration number match your previous step.
            </p>
            @if ($certificatePath)
                <div class="lei-portal-doc-file" data-existing>
                    <div class="lei-portal-doc-file-icon"><i class="fa-regular fa-file-pdf"></i></div>
                    <div class="lei-portal-doc-file-meta">
                        <strong>{{ basename($certificatePath) }}</strong>
                        <span>Uploaded — replace below if needed</span>
                    </div>
                </div>
            @endif
            <label class="lei-portal-upload lei-portal-upload--primary">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <div class="lei-portal-upload-text">
                    <span>Supported: PDF, JPG, PNG (Max 10MB)</span>
                </div>
                <span class="lei-portal-upload-browse">Browse Files</span>
                <input type="file" name="certificate_of_incorporation" accept=".pdf,.jpg,.jpeg,.png" data-doc-key="certificate_of_incorporation" data-doc-label="Certificate of Incorporation" {{ $certificatePath ? '' : 'required' }}>
            </label>
            @error('certificate_of_incorporation')<p class="lei-portal-field-error">{{ $message }}</p>@enderror
        </div>

        {{-- 2. Articles of Association (Optional) --}}
        <div class="lei-portal-doc-card">
            <div class="lei-portal-doc-card-head">
                <h3>Articles of Association</h3>
                <span class="lei-portal-tag optional">Optional</span>
            </div>
            @if ($articlesPath)
                <div class="lei-portal-doc-file" data-existing>
                    <div class="lei-portal-doc-file-icon"><i class="fa-regular fa-file-pdf"></i></div>
                    <div class="lei-portal-doc-file-meta">
                        <strong>{{ basename($articlesPath) }}</strong>
                        <span>Uploaded — replace below if needed</span>
                    </div>
                </div>
            @endif
            <label class="lei-portal-upload lei-portal-upload--compact">
                <i class="fa-regular fa-file-lines"></i>
                <div>{{ $articlesPath ? 'Click to replace document' : 'Click to upload document' }}</div>
                <input type="file" name="articles_of_association" accept=".pdf,.jpg,.jpeg,.png" data-doc-key="articles_of_association" data-doc-label="Articles of Association">
            </label>
            @error('articles_of_association')<p class="lei-portal-field-error">{{ $message }}</p>@enderror
        </div>

        {{-- 3. Proof of Authority (Required) --}}
        <div class="lei-portal-doc-card">
            <div class="lei-portal-doc-card-head">
                <h3>Proof of Authority</h3>
                <span class="lei-portal-tag required">Required</span>
            </div>

            <div class="lei-portal-callout">
                <strong>Accepted Documents:</strong>
                <ul>
                    <li>Power of Attorney (PoA)</li>
                    <li>Extract from official registry showing authorized signatories</li>
                    <li>Letter of Authorization signed by a director</li>
                </ul>
            </div>

            @if ($proofPath)
                <div class="lei-portal-doc-file" data-existing>
                    <div class="lei-portal-doc-file-icon"><i class="fa-regular fa-file-pdf"></i></div>
                    <div class="lei-portal-doc-file-meta">
                        <strong>{{ basename($proofPath) }}</strong>
                        <span>{{ $proofTypes[$draft['proof_of_authority_type'] ?? ''] ?? 'Uploaded document' }}</span>
                    </div>
                </div>
            @endif

            <div class="lei-portal-doc-picker">
                <div class="lei-portal-doc-picker-select">
                    <i class="fa-regular fa-file-lines" aria-hidden="true"></i>
                    <select name="proof_of_authority_type" required>
                        <option value="">Select document type to upload…</option>
                        @foreach ($proofTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('proof_of_authority_type', $draft['proof_of_authority_type'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <label class="lei-portal-doc-upload-btn">
                    <input type="file" name="proof_of_authority" accept=".pdf,.jpg,.jpeg,.png" data-doc-key="proof_of_authority" data-doc-label="Proof of Authority" {{ $proofPath ? '' : 'required' }}>
                    <i class="fa-solid fa-plus"></i> Upload
                </label>
            </div>
            <p class="lei-portal-doc-filename" data-proof-filename hidden></p>
            @error('proof_of_authority_type')<p class="lei-portal-field-error">{{ $message }}</p>@enderror
            @error('proof_of_authority')<p class="lei-portal-field-error">{{ $message }}</p>@enderror
        </div>

        <div class="lei-portal-actions">
            <a href="{{ route('applicant.registration.step', ['step' => 1]) }}" class="lei-btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back to Entity Details</a>
            <div class="right">
                <button type="submit" name="draft" value="1" class="lei-btn-link">Save as Draft</button>
                <button type="submit" class="lei-btn-primary">Next Step <i class="fa-solid fa-arrow-right"></i></button>
            </div>
        </div>
    </div>

    @include('applicant.partials.uploaded-files-sidebar', ['draft' => $draft])
</form>
@endsection

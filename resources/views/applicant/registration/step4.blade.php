@extends('applicant.layouts.app')

@section('title', 'Review & Submit')

@section('content')
@include('applicant.partials.stepper', ['currentStep' => 4, 'routeName' => 'applicant.registration.step'])

@php
    use Illuminate\Support\Facades\Storage;

    $proofTypes = [
        'poa' => 'Power of Attorney (PoA)',
        'registry_extract' => 'Registry extract',
        'letter_of_authorization' => 'Letter of Authorization',
    ];

    $uploadedDocs = [];
    if (! empty($draft['certificate_of_incorporation'])) {
        $uploadedDocs[] = [
            'path' => $draft['certificate_of_incorporation'],
            'label' => 'Certificate of Incorporation',
        ];
    }
    if (! empty($draft['articles_of_association'])) {
        $uploadedDocs[] = [
            'path' => $draft['articles_of_association'],
            'label' => 'Articles of Association',
        ];
    }
    if (! empty($draft['proof_of_authority'])) {
        $typeLabel = $proofTypes[$draft['proof_of_authority_type'] ?? ''] ?? 'Proof of Authority';
        $uploadedDocs[] = [
            'path' => $draft['proof_of_authority'],
            'label' => $typeLabel,
        ];
    }

    $formatFileSize = function (?string $path): string {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return '—';
        }
        $bytes = Storage::disk('public')->size($path);
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }

        return number_format($bytes / 1024, 1).' KB';
    };
@endphp

<form method="POST" action="{{ route('applicant.registration.save', ['step' => 4]) }}" class="lei-portal-split">
    @csrf
    <div class="lei-portal-review-main">
        <div class="lei-portal-card">
            <div class="lei-portal-review-head">
                <h2>Review Application</h2>
                <a href="{{ route('applicant.registration.step', ['step' => 1]) }}" class="lei-btn-link"><i class="fa-solid fa-pen"></i> Edit Details</a>
            </div>

            <div class="lei-portal-review-section">
                <h4>Entity Details</h4>
                <dl class="lei-portal-review-dl">
                    <div>
                        <dt>Legal Name</dt>
                        <dd>{{ $application->entity_name }}</dd>
                    </div>
                    <div>
                        <dt>Registration Authority</dt>
                        <dd>{{ $draft['registration_authority'] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Registration Number</dt>
                        <dd>{{ $draft['registration_number'] ?? '—' }}</dd>
                    </div>
                    <div class="full">
                        <dt>Registered Address</dt>
                        <dd>{{ $draft['registered_address'] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Country</dt>
                        <dd>{{ $draft['country'] ?? $application->country }}</dd>
                    </div>
                    <div>
                        <dt>Entity Type</dt>
                        <dd>{{ $draft['entity_type'] ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="lei-portal-review-section">
                <h4>Uploaded Documents</h4>
                @if (count($uploadedDocs))
                    <div class="lei-portal-doc-list">
                        @foreach ($uploadedDocs as $doc)
                            <div class="lei-portal-doc-file lei-portal-doc-file--review">
                                <div class="lei-portal-doc-file-icon"><i class="fa-regular fa-file-pdf"></i></div>
                                <div class="lei-portal-doc-file-meta">
                                    <strong>{{ basename($doc['path']) }}</strong>
                                    <span>{{ $formatFileSize($doc['path']) }} &bull; {{ $doc['label'] }}</span>
                                </div>
                                <span class="lei-portal-doc-status"><i class="fa-solid fa-circle-check"></i> Ready</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="muted">No documents uploaded yet. <a href="{{ route('applicant.registration.step', ['step' => 2]) }}">Add documents</a></p>
                @endif
            </div>

            <div class="lei-portal-review-section">
                <h4>Declaration</h4>
                <dl class="lei-portal-review-dl">
                    <div class="full">
                        <dt>Signed by</dt>
                        <dd>{{ $draft['signature_name'] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Date</dt>
                        <dd>{{ now()->format('F j, Y') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="lei-portal-card lei-portal-payment-card">
            <h3><i class="fa-regular fa-credit-card"></i> Payment</h3>
            <div class="lei-portal-payment-done">
                <div class="lei-portal-payment-done-icon"><i class="fa-solid fa-circle-check"></i></div>
                <div>
                    <strong>Plan already paid</strong>
                    <p>Your subscription <code>{{ $subscription->reference }}</code> is active. No additional payment is required to submit this application.</p>
                </div>
            </div>
        </div>

        <div class="lei-portal-actions">
            <a href="{{ route('applicant.registration.step', ['step' => 3]) }}" class="lei-btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back to Declarations</a>
            <button type="submit" name="submit" value="1" class="lei-btn-primary lei-btn-primary--lg"><i class="fa-solid fa-lock"></i> Submit Application</button>
        </div>
    </div>

    @include('applicant.partials.subscription-summary', [
        'subscription' => $subscription,
        'showSubmitNote' => true,
    ])
</form>
@endsection

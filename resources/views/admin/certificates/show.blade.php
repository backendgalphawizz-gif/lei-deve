@extends('admin.layouts.app')

@section('title', 'Certificate — '.$certificate->serial_number)
@section('body_class', 'lei-page-certificates')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/lei-certificates.css') }}?v=2">
@endpush

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Registry</a>
    <span> / </span>
    <a href="{{ route('admin.certificates.index') }}">CA Management</a>
    <span> / </span>
    <span>{{ \Illuminate\Support\Str::limit($certificate->serial_number, 24) }}</span>
@endsection

@section('content')
<div class="lei-ca-page">
    <a href="{{ route('admin.certificates.index') }}" class="lei-ca-detail-back">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to CA Queue
    </a>

    <div class="lei-ca-detail-head">
        <div>
            <p class="lei-ca-header-eyebrow">ISO 17442-2:2020 · X.509 Certificate</p>
            <h2>{{ $certificate->application->entity_name }}</h2>
            <p class="serial">Serial: {{ $certificate->serial_number }}</p>
        </div>
        <span class="lei-app-status lei-app-status--{{ $certificate->statusTone() }}">
            <span class="dot"></span>{{ $certificate->statusLabel() }}
        </span>
    </div>

    <div class="lei-ca-detail-layout">
        <div class="lei-ca-detail-main">
            <div class="lei-ca-card">
                <h4>Certificate Data (Annex A)</h4>
                <dl class="lei-ca-dl">
                    <div><dt>Version</dt><dd>3 (0x2)</dd></div>
                    <div><dt>Serial Number</dt><dd class="mono">{{ $certificate->serial_number }}</dd></div>
                    <div><dt>Signature Algorithm</dt><dd>{{ $certificate->signature_algorithm }}</dd></div>
                    <div><dt>Valid From</dt><dd>{{ $certificate->valid_from?->format('M j, Y H:i') ?? '—' }}</dd></div>
                    <div><dt>Valid Until</dt><dd>{{ $certificate->valid_until?->format('M j, Y H:i') ?? '—' }}</dd></div>
                    <div><dt>Application Ref</dt><dd>{{ $certificate->application->application_code }}</dd></div>
                    <div style="grid-column:1/-1;"><dt>Issuer DN</dt><dd>{{ $certificate->issuer_dn }}</dd></div>
                    <div style="grid-column:1/-1;"><dt>Subject DN</dt><dd>{{ $certificate->subject_dn }}</dd></div>
                </dl>
            </div>

            <div class="lei-ca-card">
                <h4>LEI OID Extensions</h4>
                <dl class="lei-ca-dl">
                    <div style="grid-column:1/-1;">
                        <dt>{{ $certificate->lei_oid }} (LEI)</dt>
                        <dd class="mono">{{ $certificate->application->lei_number }}</dd>
                    </div>
                    @if ($certificate->certificate_role)
                        <div style="grid-column:1/-1;">
                            <dt>{{ $certificate->role_oid }} (Role)</dt>
                            <dd>{{ $certificate->certificate_role }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <aside class="lei-ca-sidebar">
            @include('admin.certificates.partials.signature-upload', ['warnIfMissing' => $certificate->isPendingCa()])

            <div class="lei-ca-action-card">
                <h4>CA Actions</h4>

                @if ($certificate->unsigned_pdf_path)
                    <a href="{{ route('admin.certificates.download.unsigned', $certificate) }}" class="lei-ca-btn-block lei-ca-btn-block--secondary">
                        <i class="fa-solid fa-file-pdf" aria-hidden="true"></i> Download Unsigned PDF
                    </a>
                @endif

                @if ($certificate->isSigned() && $certificate->signed_pdf_path)
                    <a href="{{ route('admin.certificates.download.signed', $certificate) }}" class="lei-ca-btn-block lei-ca-btn-block--primary">
                        <i class="fa-solid fa-file-certificate" aria-hidden="true"></i> Download Signed PDF
                    </a>
                    <p class="lei-ca-hint">
                        Signed by <strong>{{ $certificate->signer?->name }}</strong>
                        on {{ $certificate->signed_at?->format('M j, Y · g:i A') }}.
                    </p>
                @elseif ($certificate->isPendingCa())
                    <form method="POST"
                          action="{{ route('admin.certificates.sign', $certificate) }}"
                          data-confirm="Digitally sign this LEI certificate? This action cannot be undone."
                          data-confirm-title="Sign Certificate"
                          data-confirm-button="Sign Certificate"
                          data-confirm-variant="primary">
                        @csrf
                        <div class="lei-ca-field">
                            <label for="ca_notes">CA Notes (optional)</label>
                            <textarea id="ca_notes" name="ca_notes" rows="3" placeholder="Signing notes for audit trail…"></textarea>
                        </div>
                        <button type="submit" class="lei-ca-btn-block lei-ca-btn-block--primary">
                            <i class="fa-solid fa-file-signature" aria-hidden="true"></i> Digitally Sign Certificate
                        </button>
                    </form>
                    <p class="lei-ca-hint">Signing generates the ISO 17442-2 compliant signed certificate and notifies the applicant by email.</p>
                @endif

                @if ($certificate->signature_hash)
                    <div class="lei-ca-hash">SHA-256: {{ $certificate->signature_hash }}</div>
                @endif
            </div>
        </aside>
    </div>
</div>
@endsection

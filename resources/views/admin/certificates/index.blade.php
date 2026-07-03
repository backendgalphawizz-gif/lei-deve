@extends('admin.layouts.app')

@section('title', 'CA Management')
@section('body_class', 'lei-page-certificates')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/lei-certificates.css') }}?v=2">
@endpush

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Registry</a>
    <span> / </span>
    <span>CA Management</span>
@endsection

@section('content')
<div class="lei-ca-page">
    <div class="lei-ca-header">
        <p class="lei-ca-header-eyebrow">ISO 17442-2:2020</p>
        <h2>CA Management</h2>
        <p>Review unsigned LEI certificates and apply digital signatures before release to applicants.</p>
    </div>

    @include('admin.certificates.partials.signature-upload')

    <div class="lei-ca-metrics">
        <div class="lei-ca-metric">
            <div class="lei-ca-metric-icon lei-ca-metric-icon--pending">
                <i class="fa-solid fa-file-signature" aria-hidden="true"></i>
            </div>
            <div>
                <strong>{{ $stats['pending'] }}</strong>
                <span>Awaiting Signature</span>
            </div>
        </div>
        <div class="lei-ca-metric">
            <div class="lei-ca-metric-icon lei-ca-metric-icon--signed">
                <i class="fa-solid fa-certificate" aria-hidden="true"></i>
            </div>
            <div>
                <strong>{{ $stats['signed'] }}</strong>
                <span>Signed</span>
            </div>
        </div>
        <div class="lei-ca-metric">
            <div class="lei-ca-metric-icon lei-ca-metric-icon--rejected">
                <i class="fa-solid fa-ban" aria-hidden="true"></i>
            </div>
            <div>
                <strong>{{ $stats['rejected'] }}</strong>
                <span>Rejected</span>
            </div>
        </div>
    </div>

    <div class="lei-ca-pool-card">
        <div class="lei-ca-pool-head">
            <h3>Certificate Queue</h3>
            <div class="lei-ca-tabs">
                <a href="{{ route('admin.certificates.index', ['status' => 'pending_ca']) }}"
                   class="lei-ca-tab {{ $status === 'pending_ca' ? 'active' : '' }}">Pending CA</a>
                <a href="{{ route('admin.certificates.index', ['status' => 'signed']) }}"
                   class="lei-ca-tab {{ $status === 'signed' ? 'active' : '' }}">Signed</a>
                <a href="{{ route('admin.certificates.index', ['status' => 'all']) }}"
                   class="lei-ca-tab {{ $status === 'all' ? 'active' : '' }}">All</a>
            </div>
        </div>

        @if ($certificates->isNotEmpty())
            <div class="lei-ca-table-head">
                <div class="lei-ca-th lei-ca-th--serial">Serial</div>
                <div class="lei-ca-th lei-ca-th--entity">Entity</div>
                <div class="lei-ca-th lei-ca-th--lei">LEI</div>
                <div class="lei-ca-th lei-ca-th--status">Status</div>
                <div class="lei-ca-th lei-ca-th--date">Updated</div>
                <div class="lei-ca-th lei-ca-th--action">Action</div>
            </div>
            <div class="lei-ca-table-body">
                @foreach ($certificates as $cert)
                    <div class="lei-ca-row">
                        <div class="lei-ca-td lei-ca-td--serial">{{ \Illuminate\Support\Str::limit($cert->serial_number, 18) }}</div>
                        <div class="lei-ca-td lei-ca-td--entity">
                            {{ $cert->application->entity_name }}
                            @if ($cert->application->user?->email)
                                <small>{{ $cert->application->user->email }}</small>
                            @endif
                        </div>
                        <div class="lei-ca-td lei-ca-td--lei">{{ $cert->application->lei_number ?? '—' }}</div>
                        <div class="lei-ca-td lei-ca-td--status">
                            <span class="lei-app-status lei-app-status--{{ $cert->statusTone() }}">
                                <span class="dot"></span>{{ $cert->statusLabel() }}
                            </span>
                        </div>
                        <div class="lei-ca-td lei-ca-td--date">{{ $cert->updated_at->format('M j, Y') }}</div>
                        <div class="lei-ca-td lei-ca-td--action">
                            @include('admin.partials.icon-actions', [
                                'viewUrl' => route('admin.certificates.show', $cert),
                            ])
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="lei-ca-empty">
                <i class="fa-solid fa-inbox" aria-hidden="true"></i>
                <p>No certificates in this queue.</p>
                <p style="font-size:13px;margin-top:8px;">Certificates appear here after an admin approves a registration application.</p>
            </div>
        @endif

        @if ($certificates->hasPages())
            <div class="lei-ca-pager">
                <span>Showing {{ $certificates->firstItem() }}–{{ $certificates->lastItem() }} of {{ $certificates->total() }}</span>
                <div class="lei-ca-pager-links">
                    @if ($certificates->onFirstPage())
                        <span class="disabled">Prev</span>
                    @else
                        <a href="{{ $certificates->previousPageUrl() }}">Prev</a>
                    @endif
                    <span class="active">{{ $certificates->currentPage() }}</span>
                    @if ($certificates->hasMorePages())
                        <a href="{{ $certificates->nextPageUrl() }}">Next</a>
                    @else
                        <span class="disabled">Next</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

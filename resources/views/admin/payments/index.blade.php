@extends('admin.layouts.app')

@section('title', 'Payments & Invoices')
@section('body_class', 'lei-page-payments')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Registry Dashboard</a>
    <span>&rsaquo;</span>
    <span>Payments</span>
@endsection

@section('content')
<div class="lei-pay-page"
     data-export-url="{{ route('admin.payments.export', request()->query()) }}">

    <div class="lei-pay-page-head">
        <div class="lei-pay-page-title">
            <h1>Payments & Invoices</h1>
            <p>Live subscription payments from applicant plan purchases</p>
        </div>
        <div class="lei-pay-page-actions">
            <a href="{{ route('admin.payments.export', request()->query()) }}" class="lei-pay-btn-outline" id="leiPayExportBtn">Export CSV</a>
        </div>
    </div>

    <div class="lei-pay-metrics" id="leiPayMetrics">
        @foreach ($stats as $stat)
            <div class="lei-pay-metric-card" data-stat-key="{{ $stat->key }}">
                <div class="lei-pay-metric-top">
                    <span class="lei-pay-metric-label">{{ strtoupper($stat->label) }}</span>
                    @if ($stat->badge)
                        <span class="lei-pay-metric-badge lei-pay-metric-badge--{{ $stat->badge_tone }}">{{ $stat->badge }}</span>
                    @endif
                </div>
                <div class="lei-pay-metric-value" data-stat-value>{{ $stat->value }}</div>
                @if (!empty($stat->subtitle))
                    <div class="lei-pay-metric-sub" data-stat-sub>{{ $stat->subtitle }}</div>
                @endif
                @if (!empty($stat->sparkline))
                    <div class="lei-pay-sparkline" aria-hidden="true">
                        @foreach ($stat->sparkline as $h)
                            <span style="height: {{ max(4, (int) $h * 0.28) }}px"></span>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="lei-pay-workspace">
        <div class="lei-pay-main-col">
            <div class="lei-pay-panel">
                <div class="lei-pay-panel-head">
                    <div>
                        <h2>Subscription Payments</h2>
                        <p>All plan purchases from the applicant portal</p>
                    </div>
                    <form method="GET" action="{{ route('admin.payments.index') }}" class="lei-pay-filters" id="leiPayFilterForm">
                        @if (request('q'))
                            <input type="hidden" name="q" value="{{ request('q') }}">
                        @endif
                        <select name="period" data-auto-filter>
                            <option value="all" @selected(request('period', 'all') === 'all')>All Time</option>
                            <option value="24h" @selected(request('period') === '24h')>Last 24 Hours</option>
                            <option value="7d" @selected(request('period') === '7d')>Last 7 Days</option>
                            <option value="30d" @selected(request('period') === '30d')>Last 30 Days</option>
                        </select>
                        <select name="status" data-auto-filter>
                            <option value="">All Statuses</option>
                            <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                            <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                        </select>
                    </form>
                </div>

                <div class="lei-pay-table-head">
                    <div class="lei-pay-th">REFERENCE</div>
                    <div class="lei-pay-th lei-pay-th--entity">APPLICANT / PLAN</div>
                    <div class="lei-pay-th">AMOUNT</div>
                    <div class="lei-pay-th">STATUS</div>
                    <div class="lei-pay-th lei-pay-th--action">INVOICE</div>
                </div>

                <div class="lei-pay-table-body" id="leiPayTableBody">
                    @forelse ($subscriptions as $sub)
                        <div class="lei-pay-row">
                            <div class="lei-pay-td lei-pay-td--id">{{ $sub->reference }}</div>
                            <div class="lei-pay-td lei-pay-td--entity">
                                <strong>{{ $sub->user?->name ?? '—' }}</strong>
                                <span>{{ $sub->plan_name }} · {{ $businessSettings->formatDate($sub->starts_at, 'd/m/Y · H:i') }}</span>
                            </div>
                            <div class="lei-pay-td lei-pay-td--amount">{{ $sub->formattedAmount() }}</div>
                            <div class="lei-pay-td">
                                <span class="lei-pay-status lei-pay-status--{{ $sub->payment_status === 'paid' ? 'success' : ($sub->payment_status === 'pending' ? 'pending' : 'failed') }}">
                                    {{ $sub->paymentStatusLabel() }}
                                </span>
                            </div>
                            <div class="lei-pay-td lei-pay-td--action">
                                @if ($sub->payment_status === 'paid')
                                    <a href="{{ route('admin.payments.invoice', $sub) }}" class="lei-pay-invoice-link" title="Download GST invoice">
                                        <i class="fa-solid fa-file-invoice"></i> PDF
                                    </a>
                                @else
                                    <span class="lei-pay-muted">—</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="lei-pay-empty">
                            @if ($totalInDb === 0)
                                No subscription payments yet. Payments appear when applicants purchase plans.
                            @else
                                No subscriptions match the selected filters.
                                <a href="{{ route('admin.payments.index') }}">Clear filters</a>
                            @endif
                        </div>
                    @endforelse
                </div>

                @if ($subscriptions->hasPages())
                    <div class="lei-pay-panel-footer">
                        <span>{{ $subscriptions->firstItem() }}–{{ $subscriptions->lastItem() }} of {{ $subscriptions->total() }}</span>
                        <div class="lei-pay-pager">
                            @if ($subscriptions->onFirstPage())
                                <span class="disabled">Prev</span>
                            @else
                                <a href="{{ $subscriptions->previousPageUrl() }}">Prev</a>
                            @endif
                            <span class="active">{{ $subscriptions->currentPage() }}</span>
                            @if ($subscriptions->hasMorePages())
                                <a href="{{ $subscriptions->nextPageUrl() }}">Next</a>
                            @else
                                <span class="disabled">Next</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="lei-pay-bottom">
                <div class="lei-pay-section-head">
                    <h3>Invoices</h3>
                    <span class="lei-pay-muted">{{ $invoices->count() }} paid subscriptions</span>
                </div>
                @if ($invoices->isNotEmpty())
                    <div class="lei-pay-tax-cards">
                        @foreach ($invoices as $invoice)
                            <div class="lei-pay-tax-card">
                                <div class="lei-pay-tax-icon lei-pay-tax-icon--pdf" aria-hidden="true">
                                    <i class="fa-solid fa-file-invoice"></i>
                                </div>
                                <div class="lei-pay-tax-info">
                                    <strong>Invoice — {{ $invoice->reference }}</strong>
                                    <span>{{ $invoice->user?->name ?? 'Applicant' }} · {{ $invoice->plan_name }} · {{ $invoice->formattedAmount() }}</span>
                                    <span>{{ $businessSettings->formatDate($invoice->starts_at) }}</span>
                                </div>
                                <a href="{{ route('admin.payments.invoice', $invoice) }}" class="lei-pay-tax-dl-btn" title="Download invoice" aria-label="Download invoice">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="lei-pay-tab-note">No paid subscriptions yet — invoices will appear here after applicants complete checkout.</p>
                @endif
            </div>
        </div>

        <aside class="lei-pay-side-col">
            <div class="lei-pay-refund-panel">
                <div class="lei-pay-refund-head">
                    <h2>Pending Payments</h2>
                </div>
                <div class="lei-pay-refund-list">
                    @forelse ($pendingPayments as $sub)
                        <div class="lei-pay-refund-card">
                            <div class="lei-pay-refund-card-top">
                                <span class="lei-pay-refund-code">{{ $sub->reference }}</span>
                                <span class="lei-pay-refund-amt">{{ $sub->formattedAmount() }}</span>
                            </div>
                            <strong>{{ $sub->user?->name ?? 'Applicant' }}</strong>
                            <p>{{ $sub->plan_name }}</p>
                        </div>
                    @empty
                        <div class="lei-pay-refund-empty">No pending payments.</div>
                    @endforelse
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-payments.css') }}?v=4">
<style>
.lei-pay-invoice-link { font-size: 12px; font-weight: 600; color: #1a5fad; text-decoration: none; }
.lei-pay-invoice-link:hover { text-decoration: underline; }
.lei-pay-muted { font-size: 12px; color: #94a3b8; }
.lei-pay-tax-dl-btn { display: flex; align-items: center; justify-content: center; text-decoration: none; color: inherit; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    document.querySelectorAll('[data-auto-filter]').forEach(function (el) {
        el.addEventListener('change', function () {
            el.closest('form').submit();
        });
    });
})();
</script>
@endpush

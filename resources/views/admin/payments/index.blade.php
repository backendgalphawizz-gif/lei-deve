@extends('admin.layouts.app')

@section('title', 'Financial System Health')
@section('body_class', 'lei-page-payments')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Registry Dashboard</a>
    <span>&rsaquo;</span>
    <span>Financial Overview</span>
@endsection

@section('content')
<div class="lei-pay-page"
     data-refund-url="{{ rtrim(config('app.url'), '/') }}/admin/payments/refunds/__ID__/action"
     data-reconcile-url="{{ route('admin.payments.reconcile') }}"
     data-export-url="{{ route('admin.payments.export', request()->query()) }}">

    <div id="leiPayToast" class="lei-pay-toast" hidden></div>

    <div class="lei-pay-page-head">
        <div class="lei-pay-page-title">
            <h1>Financial System Health</h1>
            <p>Real-time registry revenue, refunds, and gateway monitoring</p>
        </div>
        <div class="lei-pay-page-actions">
            <a href="{{ route('admin.payments.export', request()->query()) }}" class="lei-pay-btn-outline" id="leiPayExportBtn">Export Report</a>
            <button type="button" class="lei-pay-btn-primary" id="leiPayReconcileBtn">Reconcile All</button>
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
                        <h2>Transaction Monitoring</h2>
                        <p>Real-time ledger of all registry financial activity</p>
                    </div>
                    <form method="GET" action="{{ route('admin.payments.index') }}" class="lei-pay-filters" id="leiPayFilterForm">
                        @if (request('q'))
                            <input type="hidden" name="q" value="{{ request('q') }}">
                        @endif
                        <select name="gateway" data-auto-filter>
                            <option value="">All Gateways</option>
                            @foreach ($gatewayOptions as $gw)
                                <option value="{{ $gw }}" @selected(request('gateway') === $gw)>{{ ucfirst($gw) }}</option>
                            @endforeach
                        </select>
                        <select name="period" data-auto-filter>
                            <option value="all" @selected(request('period', 'all') === 'all')>All Time</option>
                            <option value="24h" @selected(request('period') === '24h')>Last 24 Hours</option>
                            <option value="7d" @selected(request('period') === '7d')>Last 7 Days</option>
                            <option value="30d" @selected(request('period') === '30d')>Last 30 Days</option>
                        </select>
                        <select name="status" data-auto-filter>
                            <option value="">All Statuses</option>
                            <option value="success" @selected(request('status') === 'success')>Success</option>
                            <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        </select>
                    </form>
                </div>

                <div class="lei-pay-table-head">
                    <div class="lei-pay-th">TRANSACTION ID</div>
                    <div class="lei-pay-th lei-pay-th--entity">ENTITY NAME</div>
                    <div class="lei-pay-th">AMOUNT</div>
                    <div class="lei-pay-th">STATUS</div>
                    <div class="lei-pay-th lei-pay-th--action">ACTION</div>
                </div>

                <div class="lei-pay-table-body" id="leiPayTableBody">
                    @forelse ($transactions as $trx)
                        <div class="lei-pay-row">
                            <div class="lei-pay-td lei-pay-td--id">{{ $trx->transaction_code }}</div>
                            <div class="lei-pay-td lei-pay-td--entity">
                                <strong>{{ $trx->entity_name }}</strong>
                                <span>{{ $businessSettings->formatDate($trx->transacted_at, 'd/m/Y · H:i') }}</span>
                            </div>
                            <div class="lei-pay-td lei-pay-td--amount">{{ $trx->formatted_amount }}</div>
                            <div class="lei-pay-td">
                                <span class="lei-pay-status lei-pay-status--{{ $trx->status }}">{{ $trx->status_label }}</span>
                            </div>
                            <div class="lei-pay-td lei-pay-td--action">
                                <button type="button" class="lei-pay-menu-btn" aria-label="Actions">⋯</button>
                            </div>
                        </div>
                    @empty
                        <div class="lei-pay-empty">
                            @if ($totalInDb === 0)
                                No transactions in database. Run:
                                <code>php artisan db:seed --class=PaymentManagementSeeder</code>
                            @else
                                No transactions match the selected filters.
                                <a href="{{ route('admin.payments.index') }}">Clear all filters</a>
                            @endif
                        </div>
                    @endforelse
                </div>

                <div class="lei-pay-panel-footer">
                    <span id="leiPayTableCount">{{ $transactions->firstItem() ?? 0 }}-{{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} transactions</span>
                    <div class="lei-pay-pager">
                        @if ($transactions->onFirstPage())
                            <span class="disabled">Prev</span>
                        @else
                            <a href="{{ $transactions->previousPageUrl() }}">Prev</a>
                        @endif
                        @for ($p = max(1, $transactions->currentPage() - 1); $p <= min($transactions->lastPage(), $transactions->currentPage() + 1); $p++)
                            @if ($p == $transactions->currentPage())
                                <span class="active">{{ $p }}</span>
                            @else
                                <a href="{{ $transactions->url($p) }}">{{ $p }}</a>
                            @endif
                        @endfor
                        @if ($transactions->hasMorePages())
                            <a href="{{ $transactions->nextPageUrl() }}">Next</a>
                        @else
                            <span class="disabled">Next</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="lei-pay-bottom">
                <div class="lei-pay-tabs" role="tablist">
                    <button type="button" class="lei-pay-tab active" data-tab="invoices">Invoices</button>
                    <button type="button" class="lei-pay-tab" data-tab="tax">Tax Configurations</button>
                    <button type="button" class="lei-pay-tab" data-tab="reports">Reports</button>
                </div>
                <div class="lei-pay-tab-panels">
                    <div class="lei-pay-tab-panel active" data-panel="invoices">
                        <div class="lei-pay-section-head">
                            <h3>Pending Tax Reports</h3>
                            <a href="#" class="lei-pay-tax-calendar" data-prevent>
                                View Tax Calendar
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                            </a>
                        </div>
                        <div class="lei-pay-tax-cards">
                            @foreach ($taxReports->sortBy(fn ($r) => $r->file_type === 'pdf' ? 0 : 1) as $report)
                                <div class="lei-pay-tax-card">
                                    <div class="lei-pay-tax-icon lei-pay-tax-icon--{{ $report->file_type }}" aria-hidden="true">
                                        @if ($report->file_type === 'pdf')
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                                <polyline points="14 2 14 8 20 8"/>
                                                <line x1="8" y1="13" x2="16" y2="13"/>
                                                <line x1="8" y1="17" x2="13" y2="17"/>
                                            </svg>
                                        @else
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                                <line x1="3" y1="9" x2="21" y2="9"/>
                                                <line x1="3" y1="15" x2="21" y2="15"/>
                                                <line x1="9" y1="3" x2="9" y2="21"/>
                                                <line x1="15" y1="3" x2="15" y2="21"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="lei-pay-tax-info">
                                        <strong>{{ $report->filename }}</strong>
                                        <span>Generated: {{ $businessSettings->formatDate($report->generated_at) }} · {{ $report->file_size_display }}</span>
                                    </div>
                                    <button type="button" class="lei-pay-tax-dl-btn" title="Download {{ $report->filename }}" data-prevent aria-label="Download">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                            <polyline points="7 10 12 15 17 10"/>
                                            <line x1="12" y1="15" x2="12" y2="3"/>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="lei-pay-tab-panel" data-panel="tax" hidden>
                        <div class="lei-pay-section-head">
                            <h3>Tax Configurations</h3>
                        </div>
                        <p class="lei-pay-tab-note">Configure VAT regions, filing thresholds, and automated quarterly exports for registry revenue.</p>
                        <ul class="lei-pay-config-list">
                            <li><strong>US Federal:</strong> 21% corporate · Auto-file enabled</li>
                            <li><strong>EU VAT:</strong> Standard 20% · MOSS reporting active</li>
                            <li><strong>UK HMRC:</strong> MTD compatible · Next sync in 12 days</li>
                        </ul>
                    </div>
                    <div class="lei-pay-tab-panel" data-panel="reports" hidden>
                        <div class="lei-pay-section-head">
                            <h3>Scheduled Reports</h3>
                        </div>
                        <p class="lei-pay-tab-note">Scheduled financial reports and reconciliation summaries.</p>
                        <ul class="lei-pay-config-list">
                            <li>Daily Transaction Summary — 06:00 UTC</li>
                            <li>Weekly Refund Analysis — Mondays 08:00 UTC</li>
                            <li>Quarterly Tax Liability — End of quarter + 5 days</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <aside class="lei-pay-side-col">
            <div class="lei-pay-refund-panel">
                <div class="lei-pay-refund-head">
                    <h2>Refund Queue</h2>
                    <span class="lei-pay-priority-badge">HIGH PRIORITY</span>
                </div>
                <div class="lei-pay-refund-list" id="leiPayRefundList">
                    @forelse ($refunds as $refund)
                        <div class="lei-pay-refund-card" data-refund-id="{{ $refund->id }}">
                            <div class="lei-pay-refund-card-top">
                                <span class="lei-pay-refund-code">#{{ $refund->refund_code }}</span>
                                <span class="lei-pay-refund-amt">{{ $refund->formatted_amount }}</span>
                            </div>
                            <strong>{{ $refund->entity_name }}</strong>
                            @if ($refund->reason)
                                <p>{{ $refund->reason }}</p>
                            @endif
                            <div class="lei-pay-refund-actions">
                                <button type="button" class="lei-pay-btn-approve" data-refund-action="approve">Approve</button>
                                <button type="button" class="lei-pay-btn-reject" data-refund-action="reject">Reject</button>
                            </div>
                        </div>
                    @empty
                        <div class="lei-pay-refund-empty">No pending refunds.</div>
                    @endforelse
                </div>
            </div>

            <div class="lei-pay-gateway-panel" id="leiPayGatewayPanel">
                <h2>Gateway Infrastructure</h2>
                <p class="lei-pay-gateway-sub">Monitoring API latency</p>
                @foreach ($gateways as $gw)
                    <div class="lei-pay-gateway-row" data-gateway-key="{{ $gw->gateway_key }}">
                        <div class="lei-pay-gateway-label">
                            <span>{{ $gw->name }}</span>
                            <span class="lei-pay-gateway-ms" data-latency>{{ $gw->latency_ms }}ms</span>
                        </div>
                        <div class="lei-pay-gateway-bar">
                            <span class="lei-pay-gateway-fill lei-pay-gateway-fill--{{ $gw->status }}" style="width: {{ $gw->health_percent }}%" data-health-bar></span>
                        </div>
                    </div>
                @endforeach
                <p class="lei-pay-gateway-refresh">Auto-refresh in <span id="leiPayRefreshSec">8</span>s</p>
            </div>

            <div class="lei-pay-audit-panel">
                <h2>Audit Logs</h2>
                <ul class="lei-pay-audit-list" id="leiPayAuditList">
                    @foreach ($auditLogs as $log)
                        <li>
                            <strong>{{ $log->actor_name }}</strong> {{ $log->description }}
                            <time>{{ $log->occurred_at->diffForHumans() }}</time>
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-payments.css') }}?v=3">
@endpush

@push('scripts')
<script src="{{ asset('js/lei-payments.js') }}?v=2"></script>
@endpush

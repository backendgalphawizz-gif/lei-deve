@extends('admin.layouts.app')

@section('title', 'Audit Logs')
@section('body_class', 'lei-page-audit')

@section('content')
<div class="lei-audit-page"
     data-export-url="{{ route('admin.audit.export') }}"
     data-detail-url="{{ rtrim(config('app.url'), '/') }}/admin/audit/entries/__ID__"
     data-sync-url="{{ route('admin.audit.sync') }}"
     data-filter-url="{{ route('admin.audit.index') }}">

    <div id="leiAuditToast" class="lei-audit-toast" hidden></div>

    @if ($statCards->isEmpty() || !$config)
        <div class="lei-audit-empty">Run <code>php artisan db:seed --class=AuditLogsSeeder</code></div>
    @else

    <div class="lei-audit-stats-row" id="leiAuditStatsRow">
        @foreach ($statCards as $stat)
            <div class="lei-audit-stat-card" data-stat-key="{{ $stat->stat_key }}">
                <div class="lei-audit-stat-icon lei-audit-stat-icon--{{ $stat->icon_tone }}">
                    @if ($stat->icon_tone === 'chart')
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    @elseif ($stat->icon_tone === 'alert')
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    @elseif ($stat->icon_tone === 'shield')
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    @else
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="5" cy="6" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="18" r="2"/><line x1="7" y1="7" x2="10" y2="10"/><line x1="14" y1="14" x2="17" y2="17"/></svg>
                    @endif
                </div>
                <div class="lei-audit-stat-body">
                    <span class="lei-audit-stat-label">{{ $stat->label }}</span>
                    <strong class="lei-audit-stat-value lei-audit-stat-value--{{ $stat->stat_key === 'security_alerts' ? 'alert' : ($stat->stat_key === 'integrity' ? 'integrity' : '') }}">{{ $stat->value }}</strong>
                </div>
                @if ($stat->badge_text)
                    <span class="lei-audit-stat-badge lei-audit-stat-badge--{{ $stat->badge_tone }}">{{ $stat->badge_text }}</span>
                @endif
            </div>
        @endforeach
    </div>

    <div class="lei-audit-filter-bar">
        <div class="lei-audit-filters">
            <label class="lei-audit-filter">
                <span>Severity:</span>
                <select id="leiAuditSeverity" class="lei-audit-select">
                    <option value="all" {{ $filterSeverity === 'all' ? 'selected' : '' }}>All</option>
                    <option value="critical" {{ $filterSeverity === 'critical' ? 'selected' : '' }}>Critical</option>
                    <option value="warning" {{ $filterSeverity === 'warning' ? 'selected' : '' }}>Warning</option>
                    <option value="info" {{ $filterSeverity === 'info' ? 'selected' : '' }}>Info</option>
                </select>
            </label>
            <label class="lei-audit-filter">
                <span>Category:</span>
                <select id="leiAuditCategory" class="lei-audit-select">
                    <option value="all" {{ $filterCategory === 'all' ? 'selected' : '' }}>All</option>
                    <option value="admin" {{ $filterCategory === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="user" {{ $filterCategory === 'user' ? 'selected' : '' }}>User</option>
                    <option value="workflow" {{ $filterCategory === 'workflow' ? 'selected' : '' }}>Workflow</option>
                    <option value="payment" {{ $filterCategory === 'payment' ? 'selected' : '' }}>Payment</option>
                </select>
            </label>
            <button type="button" class="lei-audit-date-btn" id="leiAuditDateBtn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span id="leiAuditDateLabel">{{ $config->date_range_label }}</span>
            </button>
        </div>
        <a href="{{ route('admin.audit.export', ['severity' => $filterSeverity, 'category' => $filterCategory]) }}" class="lei-audit-btn-export" id="leiAuditExport">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export Immutable Archive
        </a>
    </div>

    <div class="lei-audit-card lei-audit-table-card">
        <div class="lei-audit-table">
            <div class="lei-audit-row lei-audit-row--head">
                <span>Timestamp</span>
                <span>Event Category</span>
                <span>Actor/IP</span>
                <span>Action Performed</span>
                <span>Status</span>
                <span>Action</span>
            </div>
            <div id="leiAuditTableBody">
                @forelse ($entries as $entry)
                    <div class="lei-audit-row" data-entry-id="{{ $entry->id }}">
                        <span class="lei-audit-ts">{{ $entry->logged_at }}</span>
                        <span>
                            <span class="lei-audit-pill lei-audit-pill--{{ $entry->category_tone }}">{{ $entry->category_level }}: {{ $entry->category_domain }}</span>
                        </span>
                        <span class="lei-audit-actor">
                            <strong>{{ $entry->actor_name }}</strong>
                            <small>{{ $entry->actor_ip }}</small>
                        </span>
                        <span class="lei-audit-action-text">{{ $entry->action_performed }}</span>
                        <span class="lei-audit-status">
                            <i class="lei-audit-dot lei-audit-dot--{{ $entry->status_tone }}"></i>
                            {{ $entry->status_label }}
                        </span>
                        <span class="lei-audit-row-action">
                            @if ($entry->action_type === 'view_changes')
                                <button type="button" class="lei-audit-btn-view" data-audit-view>View Changes</button>
                            @elseif ($entry->action_type === 'info')
                                <button type="button" class="lei-audit-btn-icon" data-audit-info aria-label="Info">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                                </button>
                            @else
                                <button type="button" class="lei-audit-btn-icon" data-audit-menu aria-label="More">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                                </button>
                            @endif
                        </span>
                    </div>
                @empty
                    <div class="lei-audit-table-empty">No audit entries match the selected filters.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="lei-audit-footer">
        <div class="lei-audit-ledger-pill">
            <span class="lei-audit-ledger-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </span>
            <span class="lei-audit-ledger-text">Logs are stored in an immutable ledger</span>
            <span class="lei-audit-verified">VERIFIED</span>
        </div>
        <p class="lei-audit-telemetry" id="leiAuditTelemetry">
            System Uptime: <strong>{{ $config->uptime_percent }}</strong> | Sync: <strong id="leiAuditSyncMs">{{ $config->sync_ms }}</strong>
        </p>
    </div>

    <div class="lei-audit-modal" id="leiAuditModal" hidden>
        <div class="lei-audit-modal-backdrop" data-close-audit-modal></div>
        <div class="lei-audit-modal-panel">
            <h3>Audit Event Detail</h3>
            <pre id="leiAuditModalBody" class="lei-audit-modal-body"></pre>
            <div class="lei-audit-modal-actions">
                <button type="button" class="lei-audit-btn-outline" data-close-audit-modal>Close</button>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-audit.css') }}?v=3">
@endpush

@push('scripts')
<script src="{{ asset('js/lei-audit.js') }}?v=1"></script>
@endpush

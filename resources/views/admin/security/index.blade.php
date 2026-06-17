@extends('admin.layouts.app')

@section('title', 'Security Management')
@section('body_class', 'lei-page-security')

@section('content')
<div class="lei-sec-page"
     data-policy-url="{{ route('admin.security.policy') }}"
     data-sync-url="{{ route('admin.security.sync') }}"
     data-ip-store-url="{{ route('admin.security.ip.store') }}"
     data-ip-delete-url="{{ rtrim(config('app.url'), '/') }}/admin/security/ip-rules/__ID__"
     data-incident-url="{{ rtrim(config('app.url'), '/') }}/admin/security/incidents/__ID__/action"
     data-clear-info-url="{{ route('admin.security.clear-info') }}"
     data-export-url="{{ route('admin.security.export') }}"
     data-filter-url="{{ route('admin.security.index') }}">

    <div id="leiSecToast" class="lei-sec-toast" hidden></div>

    @if ($statCards->isEmpty() || !$policy)
        <div class="lei-sec-empty">Run <code>php artisan db:seed --class=SecurityManagementSeeder</code></div>
    @else

    <div class="lei-sec-head">
        <div class="lei-sec-head-text">
            <h1>Security Management</h1>
            <p>Global oversight of registry security protocols and real-time threat analysis.</p>
        </div>
        <div class="lei-sec-head-actions">
            <a href="{{ route('admin.security.export') }}" class="lei-sec-btn-outline" id="leiSecExport">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Security Report
            </a>
            <button type="button" class="lei-sec-btn-primary" id="leiSecSync">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                Sync Protocols
            </button>
        </div>
    </div>

    <div class="lei-sec-top-row">
        <div class="lei-sec-stats-grid" id="leiSecStatsGrid">
            @foreach ($statCards as $stat)
                <div class="lei-sec-stat-card" data-stat-key="{{ $stat->stat_key }}">
                    <div class="lei-sec-stat-icon lei-sec-stat-icon--{{ $stat->icon_tone }}">
                        @if ($stat->icon_tone === 'red')
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
                        @elseif ($stat->icon_tone === 'blue')
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        @elseif ($stat->icon_tone === 'orange')
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                        @else
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                        @endif
                    </div>
                    <div class="lei-sec-stat-body">
                        <strong class="lei-sec-stat-value">{{ $stat->value }}</strong>
                        <span>{{ $stat->label }}</span>
                    </div>
                    @if ($stat->badge_text)
                        <span class="lei-sec-stat-badge lei-sec-stat-badge--{{ $stat->badge_tone }}">{{ $stat->badge_text }}</span>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="lei-sec-card lei-sec-threat-card">
            <div class="lei-sec-threat-head">
                <div class="lei-sec-threat-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    <h2>Threat Monitoring Center</h2>
                </div>
                <div class="lei-sec-threat-badges">
                    <span class="lei-sec-count lei-sec-count--critical" id="leiSecCriticalCount">Critical ({{ $policy->critical_count }})</span>
                    <span class="lei-sec-count lei-sec-count--warning" id="leiSecWarningCount">Warning ({{ $policy->warning_count }})</span>
                </div>
            </div>
            <div class="lei-sec-threat-body">
                <div class="lei-sec-threat-list" id="leiSecThreatList">
                    @foreach ($threatEvents as $event)
                        <div class="lei-sec-threat-item lei-sec-threat-item--{{ $event->level_tone }}">
                            <div class="lei-sec-threat-item-inner">
                                <span class="lei-sec-level">{{ $event->level }}</span>
                                <strong>{{ $event->title }}</strong>
                                <p>{{ $event->meta }}</p>
                            </div>
                            @if ($event->time_label)
                                <span class="lei-sec-threat-time">{{ $event->time_label }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="lei-sec-overlay">
                    <div class="lei-sec-overlay-globe">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        <span class="lei-sec-overlay-dot"></span>
                    </div>
                    <p class="lei-sec-overlay-title">Global Security Overlay</p>
                    <p class="lei-sec-overlay-meta" id="leiSecOverlayStatus">Live Sync: {{ $policy->overlay_status }}</p>
                    <p class="lei-sec-overlay-sync" id="leiSecLastSynced">Last sync: {{ $policy->last_synced_at?->diffForHumans() ?? 'Never' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="lei-sec-mid-row">
        <div class="lei-sec-card lei-sec-access-card">
            <div class="lei-sec-card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/></svg>
                <h2>Access Enforcement</h2>
            </div>
            <div class="lei-sec-policy-row lei-sec-policy-row--toggle">
                <div>
                    <strong>Global MFA Enforcement</strong>
                    <span>Force MFA for all admin accounts</span>
                </div>
                <label class="lei-sec-toggle">
                    <input type="checkbox" id="leiSecMfa" {{ $policy->mfa_enabled ? 'checked' : '' }}>
                    <span></span>
                </label>
            </div>
            <div class="lei-sec-policy-row">
                <span>Session Timeout</span>
                <input type="text" id="leiSecSession" class="lei-sec-input-inline" value="{{ $policy->session_timeout }}">
            </div>
            <div class="lei-sec-policy-row">
                <span>Max Login Attempts</span>
                <input type="text" id="leiSecMaxAttempts" class="lei-sec-input-inline" value="{{ $policy->max_login_attempts }}">
            </div>
            <input type="hidden" id="leiSecMfaAdoption" value="{{ $policy->mfa_adoption }}">
            <input type="hidden" id="leiSecFailedLogins" value="{{ $policy->failed_login_count }}">
            <button type="button" class="lei-sec-btn-policy" id="leiSecUpdatePolicy">Update Policy Overrides</button>
        </div>

        <div class="lei-sec-card lei-sec-ip-card">
            <div class="lei-sec-ip-head">
                <div class="lei-sec-card-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    <h2>IP Restriction Management</h2>
                </div>
                <button type="button" class="lei-sec-btn-new-range" id="leiSecNewRange">+ New Range</button>
            </div>
            <div class="lei-sec-ip-table" id="leiSecIpTable">
                <div class="lei-sec-ip-row lei-sec-ip-row--head">
                    <span>STATUS</span>
                    <span>IP RANGE / CIDR</span>
                    <span>LOCATION</span>
                    <span>CONTEXT</span>
                    <span>ACTION</span>
                </div>
                <div id="leiSecIpBody">
                    @foreach ($ipRules as $rule)
                        <div class="lei-sec-ip-row" data-rule-id="{{ $rule->id }}">
                            <span><span class="lei-sec-ip-pill lei-sec-ip-pill--{{ $rule->status_tone }}">{{ $rule->status }}</span></span>
                            <span class="lei-sec-mono">{{ $rule->ip_range }}</span>
                            <span>{{ $rule->location }}</span>
                            <span>{{ $rule->context }}</span>
                            <span>
                                <button type="button" class="lei-sec-trash" data-delete-ip aria-label="Delete">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="lei-sec-card lei-sec-incidents-card">
        <div class="lei-sec-incidents-head">
            <h2><span class="lei-sec-bang">!</span> Security Incident Queue</h2>
            <div class="lei-sec-incidents-filters">
                <select class="lei-sec-select" id="leiSecSeverityFilter">
                    <option value="all" {{ $filterSeverity === 'all' ? 'selected' : '' }}>All Severities</option>
                    <option value="critical" {{ $filterSeverity === 'critical' ? 'selected' : '' }}>Critical</option>
                    <option value="high" {{ $filterSeverity === 'high' ? 'selected' : '' }}>High</option>
                    <option value="info" {{ $filterSeverity === 'info' ? 'selected' : '' }}>Info</option>
                </select>
                <button type="button" class="lei-sec-filter-btn" id="leiSecApplyFilter" aria-label="Filter">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                </button>
                <button type="button" class="lei-sec-clear-link" id="leiSecClearInfo">Clear Info Alerts</button>
            </div>
        </div>
        <div class="lei-sec-inc-table" id="leiSecIncTable">
            <div class="lei-sec-inc-row lei-sec-inc-row--head">
                <span>ID</span>
                <span>INCIDENT TYPE</span>
                <span>SEVERITY</span>
                <span>LAST EVENT</span>
                <span>CURRENT STATUS</span>
                <span>ASSIGNEE</span>
                <span>ACTIONS</span>
            </div>
            <div id="leiSecIncBody">
                @forelse ($incidents as $inc)
                    <div class="lei-sec-inc-row" data-incident-id="{{ $inc->id }}" data-severity="{{ $inc->severity_tone }}">
                        <span class="lei-sec-mono">{{ $inc->incident_id }}</span>
                        <span>
                            <strong>{{ $inc->title }}</strong>
                            <small>{{ $inc->subtitle }}</small>
                        </span>
                        <span><span class="lei-sec-sev lei-sec-sev--{{ $inc->severity_tone }}">{{ $inc->severity }}</span></span>
                        <span class="lei-sec-inc-time">{{ $inc->last_event }}</span>
                        <span class="lei-sec-status"><i class="lei-sec-dot lei-sec-dot--{{ $inc->status_tone }}"></i><span class="lei-sec-inc-status-text">{{ $inc->current_status }}</span></span>
                        <span class="lei-sec-assignee">
                            <span class="lei-sec-avatar">{{ $inc->assignee_initials }}</span>
                            {{ $inc->assignee_name }}
                        </span>
                        <span>
                            <button type="button" class="lei-sec-inc-btn lei-sec-inc-btn--{{ $inc->action_style }}" data-incident-action data-action="{{ $inc->action_key }}">{{ $inc->action_label }}</button>
                        </span>
                    </div>
                @empty
                    <div class="lei-sec-table-empty">No active incidents for this filter.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="lei-sec-footer-row" id="leiSecSummaryRow">
        @foreach ($summaryCards as $summary)
            <div class="lei-sec-summary lei-sec-summary--{{ $summary->border_tone }}" data-summary-title="{{ $summary->title }}">
                <div class="lei-sec-summary-icon lei-sec-summary-icon--{{ $summary->icon_tone }}">
                    @if ($summary->icon_tone === 'yellow')
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
                    @elseif ($summary->icon_tone === 'blue')
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    @else
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
                    @endif
                </div>
                <div>
                    <span class="lei-sec-summary-label">{{ $summary->title }}</span>
                    <strong class="lei-sec-summary-primary">{{ $summary->line_primary }}</strong>
                    <p class="lei-sec-summary-secondary">{{ $summary->line_secondary }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="lei-sec-modal" id="leiSecIpModal" hidden>
        <div class="lei-sec-modal-backdrop" data-close-modal></div>
        <div class="lei-sec-modal-panel">
            <h3>Add IP Range</h3>
            <form id="leiSecIpForm" class="lei-sec-modal-form">
                <label>
                    <span>Status</span>
                    <select name="status_tone" required>
                        <option value="whitelist">Whitelisted</option>
                        <option value="blacklist">Blacklisted</option>
                    </select>
                </label>
                <label>
                    <span>IP Range / CIDR</span>
                    <input type="text" name="ip_range" required placeholder="10.0.4.0/24">
                </label>
                <label>
                    <span>Location</span>
                    <input type="text" name="location" required placeholder="Berlin, DE (Internal)">
                </label>
                <label>
                    <span>Context</span>
                    <input type="text" name="context" required placeholder="Corporate HQ WiFi">
                </label>
                <div class="lei-sec-modal-actions">
                    <button type="button" class="lei-sec-btn-outline" data-close-modal>Cancel</button>
                    <button type="submit" class="lei-sec-btn-primary">Save Range</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-security.css') }}?v=4">
@endpush

@push('scripts')
<script src="{{ asset('js/lei-security.js') }}?v=2"></script>
@endpush

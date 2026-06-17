@extends('admin.layouts.app')

@section('title', 'Advanced Controls')
@section('body_class', 'lei-page-controls')

@section('content')
<div class="lei-ctrl-page"
     data-variable-url="{{ rtrim(config('app.url'), '/') }}/admin/controls/variables/__ID__"
     data-policies-url="{{ route('admin.controls.policies.update') }}"
     data-maintenance-url="{{ route('admin.controls.maintenance') }}"
     data-override-arm-url="{{ route('admin.controls.override.arm') }}"
     data-override-exec-url="{{ route('admin.controls.override.execute') }}"
     data-revoke-url="{{ route('admin.controls.sessions.revoke') }}"
     data-mfa-url="{{ route('admin.controls.mfa.force') }}"
     data-export-url="{{ route('admin.controls.export') }}"
     data-scrub-url="{{ route('admin.controls.scrub') }}">

    <div id="leiCtrlToast" class="lei-ctrl-toast" hidden></div>

    <div class="lei-ctrl-head">
        <div class="lei-ctrl-head-text">
            <h1>Advanced Control Node</h1>
            <p>Authoritative platform governance and critical incident response center.</p>
        </div>
        <div class="lei-ctrl-badges">
            <span class="lei-ctrl-badge lei-ctrl-badge--secure">
                <span class="lei-ctrl-badge-dot"></span>
                System Status: {{ $settings['system_status'] }}
            </span>
            <span class="lei-ctrl-badge lei-ctrl-badge--tier">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                Auth Level: Tier-IV Super Admin
            </span>
        </div>
    </div>

    <section class="lei-ctrl-section">
        <h2 class="lei-ctrl-section-title">CRITICAL INCIDENT COMMAND</h2>
        <div class="lei-ctrl-incident-row">
            <div class="lei-ctrl-incident-card lei-ctrl-incident-card--override">
                <div class="lei-ctrl-card-top">
                    <span class="lei-ctrl-card-icon lei-ctrl-card-icon--red" aria-hidden="true">
                        <svg width="26" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/></svg>
                    </span>
                    <span class="lei-ctrl-critical-badge">CRITICAL ACTION</span>
                </div>
                <h3>Full System Override</h3>
                <p>Immediately bypass standard auth protocols for emergency direct database access.</p>
                <label class="lei-ctrl-arm-box">
                    <input type="checkbox" id="leiOverrideArm" {{ $settings['override_armed'] ? 'checked' : '' }}>
                    <span>Override Arming</span>
                </label>
                <button type="button" class="lei-ctrl-btn-override" id="leiExecuteOverride">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Execute Override
                </button>
            </div>

            <div class="lei-ctrl-incident-card">
                <div class="lei-ctrl-card-top">
                    <span class="lei-ctrl-card-icon lei-ctrl-card-icon--navy" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0 3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
                    </span>
                </div>
                <h3>Emergency Access</h3>
                <p>Real-time global session revocation and mandatory admin credential cycling.</p>
                <button type="button" class="lei-ctrl-btn-danger-outline" id="leiRevokeSessions">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Revoke All Global Sessions
                </button>
                <button type="button" class="lei-ctrl-btn-outline" id="leiForceMfa">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                    Force MFA Re-auth
                </button>
            </div>

            <div class="lei-ctrl-incident-card">
                <div class="lei-ctrl-card-top">
                    <span class="lei-ctrl-card-icon lei-ctrl-card-icon--muted" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                    </span>
                </div>
                <h3>Maintenance Mode</h3>
                <p>Redirect all public traffic to a static maintenance page across all regions.</p>
                <div class="lei-ctrl-maintain-toggle">
                    <span class="lei-ctrl-maintain-label" id="leiMaintainLabel">{{ $settings['maintenance_mode'] ? 'ON' : 'OFF' }}</span>
                    <label class="lei-ctrl-switch lei-ctrl-switch--large">
                        <input type="checkbox" id="leiMaintenanceToggle" {{ $settings['maintenance_mode'] ? 'checked' : '' }}>
                        <span class="lei-ctrl-switch-track"></span>
                    </label>
                    <span class="lei-ctrl-maintain-activate">ACTIVATE</span>
                </div>
            </div>
        </div>
    </section>

    <section class="lei-ctrl-section">
        <h2 class="lei-ctrl-section-title">PLATFORM GOVERNANCE HUB</h2>
        <div class="lei-ctrl-governance-row">
            <div class="lei-ctrl-registry-card">
                <div class="lei-ctrl-registry-head">
                    <h3>Global Configuration Registry</h3>
                    <span id="leiLastChange">Last manual change: {{ $settings['last_manual_change'] }}</span>
                </div>
                <div class="lei-ctrl-table-head">
                    <div class="lei-ctrl-th">VARIABLE NAME</div>
                    <div class="lei-ctrl-th">VALUE</div>
                    <div class="lei-ctrl-th">RISK LEVEL</div>
                    <div class="lei-ctrl-th lei-ctrl-th--action">ACTION</div>
                </div>
                <div class="lei-ctrl-table-body" id="leiCtrlVarTable">
                    @foreach ($variables as $var)
                        <div class="lei-ctrl-row" data-var-id="{{ $var->id }}">
                            <div class="lei-ctrl-td lei-ctrl-td--name">{{ $var->variable_name }}</div>
                            <div class="lei-ctrl-td lei-ctrl-td--value" data-value-cell>{{ $var->value_display }}</div>
                            <div class="lei-ctrl-td">
                                <span class="lei-ctrl-risk lei-ctrl-risk--{{ $var->risk_level }}">{{ $var->risk_label }}</span>
                            </div>
                            <div class="lei-ctrl-td lei-ctrl-th--action">
                                <button type="button" class="lei-ctrl-modify" data-modify-var
                                        data-id="{{ $var->id }}"
                                        data-name="{{ $var->variable_name }}"
                                        data-value="{{ $var->value_display }}">Modify</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="lei-ctrl-policy-card">
                <div class="lei-ctrl-policy-head">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <h3>Policy Enforcement</h3>
                </div>
                <div class="lei-ctrl-policy-list" id="leiPolicyList">
                    @foreach ($policies as $policy)
                        <div class="lei-ctrl-policy-row" data-policy-key="{{ $policy->policy_key }}">
                            <div class="lei-ctrl-policy-info">
                                <strong>{{ $policy->title }}</strong>
                                <span>{{ $policy->description }}</span>
                            </div>
                            <label class="lei-ctrl-switch lei-ctrl-switch--gold">
                                <input type="checkbox" data-policy-toggle {{ $policy->is_enabled ? 'checked' : '' }}>
                                <span class="lei-ctrl-switch-track"></span>
                            </label>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="lei-ctrl-btn-policies" id="leiUpdatePolicies">Update All Policies</button>
            </div>
        </div>
    </section>

    <section class="lei-ctrl-section lei-ctrl-section--audit">
        <h2 class="lei-ctrl-section-title lei-ctrl-section-title--audit">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
            </svg>
            STRATEGIC AUDIT &amp; EXPORT
        </h2>
        <div class="lei-ctrl-audit-row">
            <div class="lei-ctrl-audit-card">
                <div class="lei-ctrl-audit-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </div>
                <div class="lei-ctrl-audit-body">
                    <h3>Full System Audit Export</h3>
                    <p>Generate SHA-256 encrypted archive of all platform activity logs for legal compliance.</p>
                    <div class="lei-ctrl-audit-actions lei-ctrl-audit-actions--row">
                        <button type="button" class="lei-ctrl-btn-export" id="leiStartExport">Start Export Job</button>
                        <a href="#" class="lei-ctrl-link-history" data-prevent>View History</a>
                    </div>
                </div>
            </div>
            <div class="lei-ctrl-audit-card">
                <div class="lei-ctrl-audit-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><line x1="10" y1="11" x2="14" y2="11"/><line x1="12" y1="9" x2="12" y2="15"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </div>
                <div class="lei-ctrl-audit-body">
                    <h3>Data Purge Governance</h3>
                    <p>Enforce GDPR-compliant data retention and initiate secure server-level data scrubbing.</p>
                    <div class="lei-ctrl-audit-actions lei-ctrl-audit-actions--stack">
                        <button type="button" class="lei-ctrl-btn-retention" data-prevent>Configure Retentions</button>
                        <button type="button" class="lei-ctrl-btn-scrub" id="leiInstantScrub">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="10" y1="11" x2="14" y2="11"/></svg>
                            Instant Scrub
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="lei-ctrl-footer">
        <span>&copy; 2024 Executive Oversight Global Registry Systems. All actions are logged and encrypted.</span>
        <div class="lei-ctrl-footer-meta">
            <span>Registry Node: {{ $settings['registry_node'] }}</span>
            <span>IP: {{ $settings['registry_ip'] }}</span>
            <span>SLA: {{ $settings['sla_percent'] }}</span>
        </div>
    </footer>
</div>

<div class="lei-ctrl-modal" id="leiVarModal" hidden>
    <div class="lei-ctrl-modal-backdrop" data-close-modal></div>
    <div class="lei-ctrl-modal-box">
        <h3>Modify Variable</h3>
        <p class="lei-ctrl-modal-var" id="leiModalVarName"></p>
        <input type="text" id="leiModalVarValue" class="lei-ctrl-modal-input">
        <div class="lei-ctrl-modal-actions">
            <button type="button" class="lei-ctrl-btn-outline" data-close-modal>Cancel</button>
            <button type="button" class="lei-ctrl-btn-primary-sm" id="leiModalSave">Save</button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-controls.css') }}?v=4">
@endpush

@push('scripts')
<script src="{{ asset('js/lei-controls.js') }}?v=2"></script>
@endpush

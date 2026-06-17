@extends('admin.layouts.app')

@section('title', 'Backup')
@section('body_class', 'lei-page-backup')

@section('content')
<div class="lei-bkp-page"
     data-manual-url="{{ route('admin.backup.manual') }}"
     data-failover-url="{{ route('admin.backup.failover') }}">

    <div id="leiBkpToast" class="lei-bkp-toast" hidden></div>

    @if (!$metrics)
        <div class="lei-bkp-empty">Run <code>php artisan db:seed --class=BackupManagementSeeder</code></div>
    @else

    <div class="lei-bkp-kpi-row">
        <div class="lei-bkp-kpi">
            <div class="lei-bkp-kpi-icon lei-bkp-kpi-icon--gold">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <span class="lei-bkp-kpi-label">LAST SUCCESSFUL BACKUP</span>
            <strong class="lei-bkp-kpi-value">{{ $metrics->last_backup_time }}</strong>
            <span class="lei-bkp-kpi-sub">Size: {{ $metrics->last_backup_size }}</span>
            <span class="lei-bkp-kpi-badge lei-bkp-kpi-badge--gold">INTEGRITY: {{ $metrics->integrity_label }}</span>
        </div>
        <div class="lei-bkp-kpi">
            <div class="lei-bkp-kpi-icon lei-bkp-kpi-icon--gold">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
            </div>
            <span class="lei-bkp-kpi-label">DR SITE AVAILABILITY</span>
            <strong class="lei-bkp-kpi-value">{{ $metrics->dr_nodes }}</strong>
            <span class="lei-bkp-kpi-sub lei-bkp-kpi-sub--dot"><i class="lei-bkp-dot lei-bkp-dot--green"></i>{{ $metrics->dr_status }}</span>
        </div>
        <div class="lei-bkp-kpi">
            <div class="lei-bkp-kpi-icon lei-bkp-kpi-icon--gold">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <span class="lei-bkp-kpi-label">RPO COMPLIANCE</span>
            <strong class="lei-bkp-kpi-value">{{ $metrics->rpo_minutes }} Mins</strong>
            <div class="lei-bkp-progress"><span style="width: {{ $metrics->rpo_percent }}%"></span></div>
        </div>
        <div class="lei-bkp-kpi lei-bkp-kpi--rto">
            <div class="lei-bkp-kpi-icon lei-bkp-kpi-icon--gold">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <span class="lei-bkp-kpi-label">RTO COMPLIANCE</span>
            <strong class="lei-bkp-kpi-value">{{ str_pad($metrics->rto_hours, 2, '0', STR_PAD_LEFT) }} Hours</strong>
            <div class="lei-bkp-kpi-rto-foot">
                <span class="lei-bkp-kpi-sub">SLA Target: {{ $metrics->rto_sla }}</span>
                <span class="lei-bkp-kpi-exceed">{{ $metrics->rto_badge }}</span>
            </div>
        </div>
    </div>

    <div class="lei-bkp-mid-row">
        <div class="lei-bkp-card lei-bkp-card--dark lei-bkp-failover-card">
            <div class="lei-bkp-fo-head">
                <div class="lei-bkp-fo-head-text">
                    <h2>Global Failover Control</h2>
                    <p>Mission-critical traffic routing and standby management</p>
                </div>
                <div class="lei-bkp-fo-status">
                    <span class="lei-bkp-fo-sync-badge">
                        <span class="lei-bkp-fo-sync-dot"></span>
                        {{ $metrics->is_synced ? 'SYNCED' : 'OUT OF SYNC' }}
                    </span>
                    <span class="lei-bkp-fo-primary">Primary: {{ $metrics->primary_site }}</span>
                </div>
            </div>
            <div class="lei-bkp-fo-body">
                <div class="lei-bkp-fo-diagram">
                    <div class="lei-bkp-fo-node lei-bkp-fo-node--primary">
                        <div class="lei-bkp-fo-node-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                        </div>
                        <span>PRIMARY</span>
                    </div>
                    <div class="lei-bkp-fo-link">
                        <span class="lei-bkp-fo-link-line"></span>
                        <span class="lei-bkp-fo-link-dot"></span>
                    </div>
                    <div class="lei-bkp-fo-node lei-bkp-fo-node--standby">
                        <div class="lei-bkp-fo-node-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"/></svg>
                        </div>
                        <span>STANDBY</span>
                    </div>
                </div>
                <div class="lei-bkp-fo-panel">
                    <div class="lei-bkp-fo-health-box">
                        <div class="lei-bkp-fo-health-head">
                            <span>SITE HEALTH</span>
                            <span class="lei-bkp-fo-latency">LATENCY: {{ $metrics->latency_ms }}ms</span>
                        </div>
                        <div class="lei-bkp-fo-health-bars">
                            <span class="lei-bkp-fo-bar lei-bkp-fo-bar--full"></span>
                            <span class="lei-bkp-fo-bar lei-bkp-fo-bar--full"></span>
                            <span class="lei-bkp-fo-bar lei-bkp-fo-bar--full"></span>
                            <span class="lei-bkp-fo-bar lei-bkp-fo-bar--mid"></span>
                            <span class="lei-bkp-fo-bar lei-bkp-fo-bar--empty"></span>
                        </div>
                        <p class="lei-bkp-fo-sync-note">Last node sync: 2 minutes ago</p>
                    </div>
                    <button type="button" class="lei-bkp-fo-btn-failover" id="leiBkpFailover">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        INITIATE SITE FAILOVER
                    </button>
                    <p class="lei-bkp-fo-warning">Warning: This action will redirect all global registry traffic to secondary nodes. Authorized clearance level 4 required.</p>
                </div>
            </div>
        </div>

        <div class="lei-bkp-card lei-bkp-schedule-card">
            <div class="lei-bkp-sch-head">
                <h2>Backup Schedule</h2>
                <p>Automated policy management</p>
            </div>
            <div class="lei-bkp-sch-body">
                <div class="lei-bkp-sch-row lei-bkp-sch-row--freq">
                    <span class="lei-bkp-sch-label">FREQUENCY</span>
                    <div class="lei-bkp-sch-freq-box">
                        <strong>{{ $metrics->frequency }}</strong>
                        <button type="button" class="lei-bkp-sch-edit" aria-label="Edit frequency">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                    </div>
                </div>
                <div class="lei-bkp-sch-row lei-bkp-sch-row--retention">
                    <div class="lei-bkp-sch-ret-head">
                        <span class="lei-bkp-sch-label">Retention Policy</span>
                        <span class="lei-bkp-sch-immutable">IMMUTABLE</span>
                    </div>
                    <div class="lei-bkp-sch-ret-box">
                        <strong>{{ $metrics->retention }}</strong>
                        <span>Compliant with G-LEI standard 4.2</span>
                    </div>
                </div>
                <div class="lei-bkp-sch-row lei-bkp-sch-row--timer">
                    <span class="lei-bkp-sch-label">NEXT SCHEDULED RUN</span>
                    <div class="lei-bkp-sch-timers">
                        <div class="lei-bkp-sch-timer">
                            <strong id="leiBkpMins">{{ $metrics->next_run_mins }}</strong>
                            <span>MINS</span>
                        </div>
                        <div class="lei-bkp-sch-timer">
                            <strong id="leiBkpSecs">{{ str_pad($metrics->next_run_secs, 2, '0', STR_PAD_LEFT) }}</strong>
                            <span>SECS</span>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="lei-bkp-sch-manual" id="leiBkpManual">Run Manual Backup Now</button>
        </div>
    </div>

    <div class="lei-bkp-card lei-bkp-snapshot-card">
        <div class="lei-bkp-snap-head">
            <div class="lei-bkp-snap-title">
                <h2>Snapshot Registry</h2>
                <p>Point-in-time recovery management</p>
            </div>
            <div class="lei-bkp-table-actions">
                <div class="lei-bkp-filter-wrap">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    <input type="search" placeholder="Filter by date or ID..." class="lei-bkp-filter">
                </div>
                <button type="button" class="lei-bkp-btn-vault">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    EXPLORE VAULT
                </button>
            </div>
        </div>
        <div class="lei-bkp-table-wrap">
            <div class="lei-bkp-table-row lei-bkp-table-row--head">
                <span>SNAPSHOT ID</span>
                <span>TIMESTAMP</span>
                <span>TYPE</span>
                <span>SIZE</span>
                <span>INTEGRITY</span>
                <span>ACTIONS</span>
            </div>
            @foreach ($snapshots as $snap)
                <div class="lei-bkp-table-row">
                    <span class="lei-bkp-mono">{{ str_starts_with($snap->snapshot_id, '#') ? $snap->snapshot_id : '#'.$snap->snapshot_id }}</span>
                    <span>{{ $snap->captured_at->format('M d, Y - H:i:s') }}</span>
                    <span><span class="lei-bkp-type-pill">{{ strtoupper($snap->type === 'full' ? 'FULL SYSTEM' : 'DELTA') }}</span></span>
                    <span>{{ $snap->size_display }}</span>
                    <span class="lei-bkp-verified"><i class="lei-bkp-dot lei-bkp-dot--gold"></i>{{ $snap->integrity_status }}</span>
                    <span><button type="button" class="lei-bkp-link">⋯</button></span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="lei-bkp-bottom-row">
        <div class="lei-bkp-card">
            <div class="lei-bkp-card-head lei-bkp-card-head--split">
                <div>
                    <h2>DR Drill History</h2>
                    <p>Recorded disaster recovery exercises</p>
                </div>
                <a href="#" class="lei-bkp-link-action">Schedule Drill</a>
            </div>
            <div class="lei-bkp-drill-list">
                @foreach ($drills as $drill)
                    <div class="lei-bkp-drill-item">
                        <span class="lei-bkp-drill-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </span>
                        <div class="lei-bkp-drill-text">
                            <strong>{{ $drill->title }}</strong>
                            <span>{{ $drill->meta }}</span>
                        </div>
                        <div class="lei-bkp-drill-meta">
                            <span class="lei-bkp-drill-status">{{ $drill->status }}</span>
                            <span>{{ $drill->completed_on->format('d M Y') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="lei-bkp-card">
            <div class="lei-bkp-card-head lei-bkp-card-head--split">
                <div>
                    <h2>Compliance &amp; Reports</h2>
                    <p>Audit artifacts and regulatory exports</p>
                </div>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div class="lei-bkp-report-grid">
                @foreach ($reports as $report)
                    <div class="lei-bkp-report-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        <div>
                            <strong>{{ $report->title }}</strong>
                            <span>{{ $report->file_meta }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-backup.css') }}?v=5">
@endpush

@push('scripts')
<script src="{{ asset('js/lei-backup.js') }}?v=1"></script>
@endpush

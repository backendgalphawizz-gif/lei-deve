@extends('admin.layouts.app')

@section('title', 'SLA Metrics')
@section('body_class', 'lei-page-sla')

@section('content')
<div class="lei-sla-page"
     data-triggers-url="{{ route('admin.sla.triggers') }}"
     data-backup-url="{{ route('admin.sla.backup-trigger') }}"
     data-clear-info-url="{{ route('admin.sla.clear-info') }}"
     data-incident-url="{{ rtrim(config('app.url'), '/') }}/admin/sla/incidents/__ID__/action">

    <div id="leiSlaToast" class="lei-sla-toast" hidden></div>

    @if ($statusCards->isEmpty() || !$config)
        <div class="lei-sla-empty">Run <code>php artisan db:seed --class=SlaMetricsSeeder</code></div>
    @else

    <div class="lei-sla-status-row">
        @foreach ($statusCards as $card)
            <div class="lei-sla-status-card lei-sla-status-card--{{ $card->border_tone }}">
                <div class="lei-sla-status-top">
                    <span class="lei-sla-status-title">{{ $card->title }}</span>
                    <span class="lei-sla-status-label lei-sla-status-label--{{ $card->status_tone }}">{{ $card->status_label }}</span>
                </div>
                <div class="lei-sla-status-metric">
                    <strong>{{ $card->metric_value }}</strong>
                    <span>{{ $card->metric_label }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="lei-sla-mid-row">
        <div class="lei-sla-card lei-sla-chart-card">
            <div class="lei-sla-chart-head">
                <h2>Infrastructure Load Cluster</h2>
                <div class="lei-sla-chart-legend">
                    <span><i class="lei-sla-legend-sq lei-sla-legend-sq--cpu"></i> CPU</span>
                    <span><i class="lei-sla-legend-sq lei-sla-legend-sq--ram"></i> RAM</span>
                </div>
            </div>
            <div class="lei-sla-chart-body">
                <div class="lei-sla-chart-y">
                    <span>100%</span>
                    <span>50%</span>
                    <span>0%</span>
                </div>
                <div class="lei-sla-chart-bars">
                    @foreach ($infraBars as $bar)
                        <div class="lei-sla-bar-wrap">
                            <div class="lei-sla-bar {{ $bar->is_alert ? 'lei-sla-bar--alert' : '' }}" style="height: {{ $bar->height_percent }}%"></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="lei-sla-side-col">
            <div class="lei-sla-card lei-sla-thresholds-card">
                <h2>Alert Thresholds</h2>
                <div class="lei-sla-threshold-list">
                    <div class="lei-sla-threshold-item">
                        <div class="lei-sla-threshold-meta">
                            <span>CPU Utilization</span>
                            <strong id="leiSlaCpuVal">{{ $config->cpu_threshold }}%</strong>
                        </div>
                        <div class="lei-sla-threshold-track"><span style="width: {{ $config->cpu_threshold }}%"></span></div>
                    </div>
                    <div class="lei-sla-threshold-item">
                        <div class="lei-sla-threshold-meta">
                            <span>RAM Allocation</span>
                            <strong id="leiSlaRamVal">{{ $config->ram_threshold }}%</strong>
                        </div>
                        <div class="lei-sla-threshold-track"><span style="width: {{ $config->ram_threshold }}%"></span></div>
                    </div>
                    <div class="lei-sla-threshold-item">
                        <div class="lei-sla-threshold-meta">
                            <span>Disk Capacity</span>
                            <strong id="leiSlaDiskVal">{{ $config->disk_threshold }}%</strong>
                        </div>
                        <div class="lei-sla-threshold-track"><span style="width: {{ $config->disk_threshold }}%"></span></div>
                    </div>
                </div>
                <button type="button" class="lei-sla-btn-gold" id="leiSlaUpdateTriggers">UPDATE TRIGGERS</button>
            </div>

            <div class="lei-sla-card lei-sla-backup-card">
                <div class="lei-sla-backup-head">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    <h2>Backup Monitor</h2>
                </div>
                <p>Last Success: <strong id="leiSlaBackupLast">{{ $config->backup_last }}</strong></p>
                <p>Next Sync: <strong id="leiSlaBackupNext">In {{ $config->backup_next }}</strong></p>
                <button type="button" class="lei-sla-btn-outline" id="leiSlaManualTrigger">MANUAL TRIGGER</button>
            </div>
        </div>
    </div>

    <div class="lei-sla-health-row">
        <div class="lei-sla-card lei-sla-health-card">
            <h2>API Performance</h2>
            <div class="lei-sla-health-stats">
                <div>
                    <strong>{{ $config->api_latency }}</strong>
                    <span>Avg Latency</span>
                </div>
                <div>
                    <strong class="lei-sla-err">{{ $config->api_err_rate }}</strong>
                    <span>Err Rate</span>
                </div>
            </div>
            <div class="lei-sla-api-track"><span style="width: {{ $config->api_progress }}%"></span></div>
        </div>
        <div class="lei-sla-card lei-sla-health-card">
            <h2>Database Health</h2>
            <div class="lei-sla-health-stats">
                <div>
                    <strong>{{ $config->db_pools }}</strong>
                    <span>Active Pools</span>
                </div>
                <div>
                    <strong>{{ $config->db_peak }}</strong>
                    <span>Peak Query</span>
                </div>
            </div>
            <div class="lei-sla-db-segments">
                @php $segs = $config->db_segments ?? [55, 25, 20]; @endphp
                <span class="lei-sla-seg lei-sla-seg--green" style="flex: {{ $segs[0] ?? 55 }} 1 0"></span>
                <span class="lei-sla-seg lei-sla-seg--orange" style="flex: {{ $segs[1] ?? 25 }} 1 0"></span>
                <span class="lei-sla-seg lei-sla-seg--red" style="flex: {{ $segs[2] ?? 20 }} 1 0"></span>
            </div>
        </div>
    </div>

    <div class="lei-sla-card lei-sla-incidents-card">
        <div class="lei-sla-incidents-head">
            <h2>Active Incidents &amp; Queue</h2>
            <div class="lei-sla-incidents-actions">
                <button type="button" class="lei-sla-btn-export">Export Log</button>
                <button type="button" class="lei-sla-btn-dark" id="leiSlaClearInfo">Clear Info Alerts</button>
            </div>
        </div>
        <div class="lei-sla-table-wrap">
            <div class="lei-sla-table-row lei-sla-table-row--head">
                <span>SEVERITY</span>
                <span>TARGET NODE</span>
                <span>INCIDENT TYPE</span>
                <span>TIME ACTIVE</span>
                <span>ACTIONS</span>
            </div>
            @forelse ($incidents as $incident)
                <div class="lei-sla-table-row" data-incident-id="{{ $incident->id }}">
                    <span><span class="lei-sla-pill lei-sla-pill--{{ $incident->severity_tone }}">{{ $incident->severity }}</span></span>
                    <span>{{ $incident->target_node }}</span>
                    <span>{{ $incident->incident_type }}</span>
                    <span class="lei-sla-mono">{{ $incident->time_active }}</span>
                    <span>
                        <button type="button" class="lei-sla-action-btn" data-action="{{ $incident->action_key }}">{{ $incident->action_label }}</button>
                    </span>
                </div>
            @empty
                <div class="lei-sla-table-empty">No active incidents in queue.</div>
            @endforelse
        </div>
    </div>
    @endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-sla.css') }}?v=1">
@endpush

@push('scripts')
<script src="{{ asset('js/lei-sla.js') }}?v=1"></script>
@endpush

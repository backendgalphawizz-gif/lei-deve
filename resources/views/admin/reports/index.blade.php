@extends('admin.layouts.app')

@section('title', 'Reports & Analytics')
@section('body_class', 'lei-page-reports')

@section('content')
@php
    $chartH = 200;
    $chartW = 600;
    $padX = 48;
    $padY = 28;
    $baseY = $chartH - $padY;
    $plotH = $chartH - ($padY * 2);
    $count = max(1, $chartPoints->count());
    $stepX = ($chartW - $padX * 2) / max(1, $count - 1);
    $toY = fn ($v) => $baseY - (($v / 100) * $plotH);
    $currentPath = '';
    $prevPath = '';
    $areaPath = '';
    $dotPoints = [];
    foreach ($chartPoints as $i => $p) {
        $x = (int) round($padX + ($i * $stepX));
        $yc = (int) round($toY($p->current_value));
        $yp = (int) round($toY($p->previous_value));
        $dotPoints[] = ['x' => $x, 'y' => $yc];
        if ($i === 0) {
            $currentPath .= "M{$x},{$yc} ";
            $prevPath .= "M{$x},{$yp} ";
            $areaPath .= "M{$x},{$yc} ";
        } else {
            $currentPath .= "H{$x} V{$yc} ";
            $prevPath .= "H{$x} V{$yp} ";
            $areaPath .= "H{$x} V{$yc} ";
        }
    }
    if ($chartPoints->isNotEmpty()) {
        $lastX = (int) round($padX + (($count - 1) * $stepX));
        $areaPath .= "L{$lastX},{$baseY} L{$padX},{$baseY} Z";
    }
@endphp
<div class="lei-rep-page"
     data-refresh-url="{{ route('admin.reports.refresh') }}"
     data-period-url="{{ route('admin.reports.period') }}"
     data-scheduled-url="{{ route('admin.reports.scheduled') }}"
     data-generate-url="{{ route('admin.reports.generate') }}"
     data-export-pdf="{{ route('admin.reports.export', 'pdf') }}"
     data-export-xlsx="{{ route('admin.reports.export', 'xlsx') }}"
     data-export-csv="{{ route('admin.reports.export', 'csv') }}"
     data-download-url="{{ rtrim(config('app.url'), '/') }}/admin/reports/generated/__ID__/download"
     data-delete-url="{{ rtrim(config('app.url'), '/') }}/admin/reports/generated/__ID__"
     data-regenerate-url="{{ rtrim(config('app.url'), '/') }}/admin/reports/generated/__ID__/regenerate"
     data-filter-url="{{ route('admin.reports.index') }}">

    <div id="leiRepToast" class="lei-rep-toast" hidden></div>

    @if ($statCards->isEmpty() || !$config)
        <div class="lei-rep-empty">Run <code>php artisan db:seed --class=ReportsAnalyticsSeeder</code></div>
    @else

    <div class="lei-rep-head">
        <div class="lei-rep-head-text">
            <h1>Reports &amp; Analytics</h1>
            <p>Real-time business intelligence and system oversight metrics.</p>
        </div>
        <div class="lei-rep-head-actions">
            <button type="button" class="lei-rep-btn-outline" id="leiRepPeriodBtn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span id="leiRepPeriodLabel">{{ $config->period_label }}</span>
            </button>
            <button type="button" class="lei-rep-btn-primary" id="leiRepRefresh">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                Refresh Data
            </button>
        </div>
    </div>

    <div class="lei-rep-stats-row" id="leiRepStatsRow">
        @foreach ($statCards as $stat)
            <div class="lei-rep-stat-card" data-stat-key="{{ $stat->stat_key }}">
                <div class="lei-rep-stat-top">
                    <div class="lei-rep-stat-icon lei-rep-stat-icon--{{ $stat->icon_tone }}">
                        @if ($stat->icon_tone === 'gold')
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        @elseif ($stat->icon_tone === 'sky')
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        @elseif ($stat->icon_tone === 'nodes')
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="5" cy="6" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="18" r="2"/><line x1="7" y1="7" x2="10" y2="10"/><line x1="14" y1="14" x2="17" y2="17"/></svg>
                        @else
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                        @endif
                    </div>
                    @if ($stat->trend_text)
                        <span class="lei-rep-trend lei-rep-trend--{{ $stat->trend_tone }}">{{ $stat->trend_text }}</span>
                    @endif
                </div>
                <strong class="lei-rep-stat-value">{{ $stat->value }}</strong>
                <span class="lei-rep-stat-label">{{ $stat->label }}</span>
                @if ($stat->description)
                    <span class="lei-rep-stat-desc">{{ $stat->description }}</span>
                @endif
            </div>
        @endforeach
    </div>

    <div class="lei-rep-card lei-rep-analytics-card">
        <div class="lei-rep-tabs">
            @foreach (['operational' => 'Operational', 'financial' => 'Financial', 'application' => 'Application', 'audit' => 'Audit'] as $key => $label)
                <button type="button" class="lei-rep-tab {{ $activeTab === $key ? 'lei-rep-tab--active' : '' }}" data-tab="{{ $key }}">{{ $label }}</button>
            @endforeach
        </div>
        <div class="lei-rep-analytics-body">
            <div class="lei-rep-chart-panel">
                <div class="lei-rep-chart-head">
                    <h2>Throughput Performance Trends</h2>
                    <div class="lei-rep-legend">
                        <span><i class="lei-rep-legend-dot lei-rep-legend-dot--current"></i> Current Period</span>
                        <span><i class="lei-rep-legend-dot lei-rep-legend-dot--prev"></i> Previous Period</span>
                    </div>
                </div>
                <div class="lei-rep-chart-wrap" id="leiRepChartWrap">
                    <div class="lei-rep-chart-box">
                        <svg class="lei-rep-chart-svg" viewBox="0 0 {{ $chartW }} {{ $chartH }}" preserveAspectRatio="xMidYMid meet" id="leiRepChartSvg" aria-hidden="true">
                            <line class="lei-rep-chart-baseline" x1="{{ $padX }}" y1="{{ $baseY }}" x2="{{ $chartW - $padX }}" y2="{{ $baseY }}"/>
                            <path class="lei-rep-chart-area" d="{{ trim($areaPath) }}" id="leiRepChartArea"/>
                            <path class="lei-rep-chart-line lei-rep-chart-line--prev" d="{{ trim($prevPath) }}" id="leiRepChartPrev"/>
                            <path class="lei-rep-chart-line lei-rep-chart-line--current" d="{{ trim($currentPath) }}" id="leiRepChartCurrent"/>
                            @foreach ($dotPoints as $dot)
                                <circle class="lei-rep-chart-dot" cx="{{ $dot['x'] }}" cy="{{ $dot['y'] }}" r="5"/>
                            @endforeach
                        </svg>
                    </div>
                    <div class="lei-rep-chart-labels">
                        @foreach ($chartPoints as $p)
                            <span>{{ $p->day_label }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="lei-rep-sla-panel">
                <div class="lei-rep-sla-box">
                    <h2>Aggregate SLA Status</h2>
                    <div class="lei-rep-donut-wrap">
                        <svg class="lei-rep-donut" viewBox="0 0 120 120" width="148" height="148">
                            <circle cx="60" cy="60" r="46" fill="none" stroke="#e5e0d5" stroke-width="14"/>
                            <circle cx="60" cy="60" r="46" fill="none" stroke="#8b6e3c" stroke-width="14"
                                    stroke-dasharray="289" stroke-dashoffset="{{ 289 * (1 - (($config->sla_percent ?? 99) / 100)) }}" transform="rotate(-90 60 60)" id="leiRepDonutRing"/>
                        </svg>
                        <div class="lei-rep-donut-center">
                            <strong id="leiRepSlaPercent">{{ $config->sla_percent }}%</strong>
                            <span>OPTIMIZED</span>
                        </div>
                    </div>
                    <ul class="lei-rep-sla-list">
                        <li><span>Critical Incidents</span><strong id="leiRepCritical">{{ $config->critical_incidents }}</strong></li>
                        <li><span>Warning Alerts</span><strong class="lei-rep-sla-warn" id="leiRepWarnings">{{ $config->warning_alerts }}</strong></li>
                        <li><span>Resolution Time</span><strong id="leiRepResolution">{{ $config->resolution_time }}</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="lei-rep-mid-row">
        <div class="lei-rep-card lei-rep-builder-card">
            <div class="lei-rep-builder-head">
                <div class="lei-rep-builder-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                    <h2>Precision Report Builder</h2>
                </div>
                <div class="lei-rep-toggle-wrap">
                    <span>Scheduled Reports</span>
                    <label class="lei-rep-toggle">
                        <input type="checkbox" id="leiRepScheduled" {{ $config->scheduled_enabled ? 'checked' : '' }}>
                        <span></span>
                    </label>
                </div>
            </div>
            <div class="lei-rep-builder-fields">
                <label>
                    <span>DATE RANGE</span>
                    <select id="leiRepDateRange" class="lei-rep-select">
                        <option {{ $config->builder_date_range === 'Last 30 Days' ? 'selected' : '' }}>Last 30 Days</option>
                        <option>Last 7 Days</option>
                        <option>Last 90 Days</option>
                    </select>
                </label>
                <label>
                    <span>CATEGORY</span>
                    <select id="leiRepCategory" class="lei-rep-select">
                        <option {{ $config->builder_category === 'All Categories' ? 'selected' : '' }}>All Categories</option>
                        <option>Financial</option>
                        <option>Operational</option>
                    </select>
                </label>
                <label>
                    <span>ENTITY</span>
                    <select id="leiRepEntity" class="lei-rep-select">
                        <option {{ $config->builder_entity === 'Global Domain' ? 'selected' : '' }}>Global Domain</option>
                        <option>Node A</option>
                        <option>Node B</option>
                    </select>
                </label>
            </div>
            <div class="lei-rep-builder-foot">
                <span id="leiRepNextRun">Next scheduled run: {{ $config->next_scheduled }}</span>
                <button type="button" class="lei-rep-btn-primary" id="leiRepGenerate">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    Generate Report
                </button>
            </div>
        </div>

        <div class="lei-rep-card lei-rep-export-card">
            <h2>Universal Export Suite</h2>
            <p>Export institutional reports for regulatory and board review workflows.</p>
            <div class="lei-rep-export-btns">
                <a href="{{ route('admin.reports.export', 'pdf') }}" class="lei-rep-export-btn" data-export="pdf">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Institutional PDF
                </a>
                <a href="{{ route('admin.reports.export', 'xlsx') }}" class="lei-rep-export-btn" data-export="xlsx">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                    Structured Excel (.xlsx)
                </a>
                <a href="{{ route('admin.reports.export', 'csv') }}" class="lei-rep-export-btn" data-export="csv">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                    Raw Data CSV
                </a>
            </div>
        </div>
    </div>

    <div class="lei-rep-card lei-rep-table-card">
        <div class="lei-rep-table-head">
            <h2>Recent Generated Reports</h2>
            <button type="button" class="lei-rep-link" id="leiRepViewAll">View All History</button>
        </div>
        <div class="lei-rep-table">
            <div class="lei-rep-row lei-rep-row--head">
                <span>REPORT NAME</span>
                <span>PARAMETERS</span>
                <span>GENERATED DATE</span>
                <span>STATUS</span>
                <span>ACTIONS</span>
            </div>
            <div id="leiRepTableBody">
                @foreach ($generated as $row)
                    <div class="lei-rep-row" data-report-id="{{ $row->id }}" data-tone="{{ $row->status_tone }}">
                        <span class="lei-rep-report-name">{{ $row->report_name }}</span>
                        <span>{{ $row->parameters }}</span>
                        <span>{{ $row->generated_date }}</span>
                        <span>
                            @if ($row->status_tone === 'expired')
                                <button type="button" class="lei-rep-regen" data-regenerate>Re-generate</button>
                            @else
                                <span class="lei-rep-status lei-rep-status--{{ $row->status_tone }}">{{ $row->status }}</span>
                            @endif
                        </span>
                        <span class="lei-rep-row-actions">
                            @if ($row->status_tone !== 'expired')
                                <button type="button" class="lei-rep-icon-btn" data-download aria-label="Download">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                </button>
                            @endif
                            <button type="button" class="lei-rep-icon-btn" data-delete aria-label="Delete">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-reports.css') }}?v=5">
@endpush

@push('scripts')
<script src="{{ asset('js/lei-reports.js') }}?v=3"></script>
@endpush

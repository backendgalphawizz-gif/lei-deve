@extends('admin.layouts.app')

@section('title', 'System Overview')

@section('content')
    @php
        $totalApps = $snapshots->get('total_applications');
        $pending = $snapshots->get('pending_approvals');
        $activeUsers = $snapshots->get('active_users');
        $payments = $snapshots->get('payments_24h');
    @endphp

    <div class="lei-page-header">
        <div>
            <h2>{{ $businessSettings->dashboard_title ?? 'System Overview' }}</h2>
            <p>{{ $businessSettings->dashboard_subtitle ?? 'Real-time operational dashboard and registry status.' }}</p>
        </div>
        <div class="lei-header-actions">
            <a href="{{ route('admin.reports.export', ['type' => 'csv']) }}" class="lei-btn-navy">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="7 10 12 15 17 10" />
                    <line x1="12" y1="15" x2="12" y2="3" />
                </svg>
                Export Reports
            </a>
            <a href="{{ route('admin.reports.index') }}" class="lei-btn-outline lei-btn-dropdown">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
                {{ $businessSettings->dashboard_period_label ?? 'Last 24 Hours' }}
                <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <polyline points="6 9 12 15 18 9" />
                </svg>
            </a>
        </div>
    </div>

    @if ($alerts->isNotEmpty())
        <div class="lei-alerts-grid">
            @foreach ($alerts as $alert)
                <div class="lei-alert {{ $alert->type === 'security' ? 'security' : 'sla' }}">
                    <div class="lei-alert-icon-wrap">
                        @if ($alert->type === 'security')
                            <i class="fa-solid fa-shield-halved"></i>
                        @else
                            <i class="fa-solid fa-exclamation"></i>
                        @endif
                    </div>
                    <div class="lei-alert-body">
                        @if ($alert->title)
                            <strong>{{ $alert->title }}</strong>
                        @endif
                        <p>{{ $alert->message }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="lei-stats-grid">
        @if ($totalApps)
            <div class="lei-stat-card lei-stat-card--chart">
                <div class="lei-stat-top">
                    <span class="label">{{ $totalApps->label }}</span>
                    <div class="value-row">
                        <span class="value">{{ $totalApps->value_display }}</span>
                        @if ($totalApps->trend_label)
                            <span class="lei-trend-pill">{{ $totalApps->trend_label }}</span>
                        @endif
                    </div>
                </div>
                @if (!empty($totalApps->meta['sparkline']))
                    <div class="lei-sparkline">
                        @foreach ($totalApps->meta['sparkline'] as $h)
                            <span style="height: {{ $h }}%"></span>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        @if ($pending)
            <div class="lei-stat-card">
                <div class="lei-stat-top">
                    <div class="lei-stat-head">
                        <span class="label">{{ $pending->label }}</span>
                        @if ($pending->badge)
                            <span class="lei-badge-urgent">{{ $pending->badge }}</span>
                        @endif
                    </div>
                    <span class="value">{{ $pending->value_display }}</span>
                    @if (!empty($pending->meta['subtitle']))
                        <div class="lei-stat-sub">{{ $pending->meta['subtitle'] }}</div>
                    @endif
                </div>
                @php $assignees = $pending->meta['assignees'] ?? []; @endphp
                @if (!empty($assignees))
                    <div class="lei-avatars">
                        @foreach ($assignees as $person)
                            <span style="background:{{ $person['color'] ?? '#5b7fa6' }}">{{ $person['initials'] }}</span>
                        @endforeach
                        @if (!empty($pending->meta['extra_count']))
                            <span class="lei-avatar-more">+{{ $pending->meta['extra_count'] }}</span>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        @if ($activeUsers)
            <div class="lei-stat-card">
                <div class="lei-stat-top">
                    <span class="label">{{ $activeUsers->label }}</span>
                    <div class="value-row">
                        <span class="value">{{ $activeUsers->value_display }}</span>
                        <span class="lei-stat-icon-muted">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="lei-progress-wrap">
                    <div class="row">
                        <span>{{ $activeUsers->meta['subtitle'] ?? 'Peak load' }}</span>
                        <span>{{ $activeUsers->meta['progress'] ?? 84 }}%</span>
                    </div>
                    <div class="lei-progress-bar">
                        <span style="width: {{ $activeUsers->meta['progress'] ?? 84 }}%"></span>
                    </div>
                </div>
            </div>
        @endif

        @if ($payments)
            <div class="lei-stat-card">
                <div class="lei-stat-top">
                    <span class="label">{{ $payments->label }}</span>
                    <div class="value-row">
                        <span class="value">{{ $payments->value_display }}</span>
                        <span class="lei-stat-icon-green">
                            <i class="fa-solid fa-money-bills"></i>
                        </span>
                    </div>
                </div>
                <div class="lei-payment-boxes">
                    <span class="lei-pay-box rev">Revenue
                        {{ $payments->meta['revenue'] ?? \App\Support\CurrencyFormatter::formatSignedCompact(8200) }}</span>
                    <span class="lei-pay-box ref">Refunds
                        {{ $payments->meta['refunds'] ?? \App\Support\CurrencyFormatter::formatSignedCompact(-1100) }}</span>
                </div>
            </div>
        @endif
    </div>

    <div class="lei-charts-row">
        <div class="lei-chart-card">
            <h3>Application Trends Visualization</h3>
            <div class="lei-chart-legend">
                <span class="main">Main Registry</span>
                <span class="partner">Partner API</span>
            </div>
            <div class="lei-chart-canvas-wrap">
                <canvas id="trendsChart" height="300"></canvas>
            </div>
        </div>

        <div class="lei-health-card">
            <h3>System Health</h3>
            <div class="lei-health-list">
                @foreach ($services as $service)
                    <div class="lei-health-item">
                        <span class="lei-health-name">{{ $service->service_name }}</span>
                        <span class="lei-health-status {{ $service->status === 'warning' ? 'warn' : 'ok' }}">
                            @if ($service->status === 'warning')
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2L1 21h22L12 2zm0 4l7.53 13H4.47L12 6zm-1 5v4h2v-4h-2zm0 6v2h2v-2h-2z" />
                                </svg>
                            @else
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            @endif
                            {{ \App\Support\CurrencyFormatter::formatNumber($service->uptime_percent, 2) }}%
                        </span>
                    </div>
                @endforeach
            </div>
            <div class="lei-load-footer">
                <div class="row">
                    <span>Current Load Average</span>
                    <span>Moderate ({{ $loadAverage }}%)</span>
                </div>
                <div class="lei-progress-bar">
                    <span style="width: {{ $loadAverage }}%"></span>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const trendData = @json($chartData);

        function drawChart() {
            const canvas = document.getElementById('trendsChart');
            if (!canvas || !trendData.length) return;

            const wrap = canvas.parentElement;
            const ctx = canvas.getContext('2d');
            const dpr = window.devicePixelRatio || 1;
            const w = wrap.clientWidth;
            const h = 300;
            canvas.width = w * dpr;
            canvas.height = h * dpr;
            canvas.style.width = w + 'px';
            canvas.style.height = h + 'px';
            ctx.scale(dpr, dpr);

            const pad = { top: 24, right: 16, bottom: 44, left: 44 };
            const chartW = w - pad.left - pad.right;
            const chartH = h - pad.top - pad.bottom;
            const maxVal = Math.max(...trendData.map(d => Math.max(d.main + d.partner, d.main))) * 1.15;
            const groupW = chartW / trendData.length;
            const barW = groupW * 0.38;

            ctx.clearRect(0, 0, w, h);

            for (let i = 0; i <= 4; i++) {
                const gy = pad.top + (chartH / 4) * i;
                ctx.strokeStyle = '#eef1f5';
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(pad.left, gy);
                ctx.lineTo(w - pad.right, gy);
                ctx.stroke();
            }

            trendData.forEach((d, i) => {
                const cx = pad.left + i * groupW + groupW / 2;
                const x = cx - barW / 2;
                const baseY = pad.top + chartH;
                const totalH = (d.main + d.partner) / maxVal * chartH;
                const mainH = d.main / maxVal * chartH;

                ctx.fillStyle = '#e4eaf2';
                ctx.beginPath();
                ctx.roundRect(x, baseY - totalH, barW, totalH, [4, 4, 0, 0]);
                ctx.fill();

                ctx.fillStyle = '#0f3057';
                ctx.beginPath();
                ctx.roundRect(x, baseY - mainH, barW, mainH, [4, 4, 0, 0]);
                ctx.fill();

                ctx.fillStyle = '#8b95a5';
                ctx.font = '11px Segoe UI, system-ui, sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText(d.label, cx, h - 16);
            });
        }

        drawChart();

        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(drawChart, 150);
        });
    </script>
@endpush
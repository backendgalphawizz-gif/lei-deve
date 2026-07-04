@extends('admin.layouts.app')

@section('title', 'System Overview')

@section('content')
    @php
        $totalApps = $snapshots->get('total_applications');
        $monthlyApps = $snapshots->get('monthly_applications');
        $renewals = $snapshots->get('renewals_this_month');
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
            <a href="{{ route('admin.applications.index') }}" class="lei-btn-navy">View Applications</a>
            <a href="{{ route('admin.payments.index') }}" class="lei-btn-outline">Payments</a>
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

    <div class="lei-stats-grid lei-stats-grid--6">
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
                    @if (!empty($totalApps->meta['subtitle']))
                        <div class="lei-stat-sub">{{ $totalApps->meta['subtitle'] }}</div>
                    @endif
                </div>
                @if (!empty($totalApps->meta['sparkline']))
                    <div class="lei-sparkline">
                        @foreach ($totalApps->meta['sparkline'] as $h)
                            <span style="height: {{ max(8, $h) }}%"></span>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        @if ($monthlyApps)
            <div class="lei-stat-card">
                <div class="lei-stat-top">
                    <span class="label">{{ $monthlyApps->label }}</span>
                    <span class="value">{{ $monthlyApps->value_display }}</span>
                    <div class="lei-stat-sub">{{ $monthlyApps->meta['subtitle'] ?? $monthlyApps->trend_label }}</div>
                    @if (isset($monthlyApps->meta['last_month']))
                        <div class="lei-stat-sub">Last month: {{ $monthlyApps->meta['last_month'] }}</div>
                    @endif
                </div>
            </div>
        @endif

        @if ($renewals)
            <div class="lei-stat-card">
                <div class="lei-stat-top">
                    <div class="lei-stat-head">
                        <span class="label">{{ $renewals->label }}</span>
                        @if ($renewals->badge)
                            <span class="lei-badge-urgent">{{ $renewals->badge }}</span>
                        @endif
                    </div>
                    <span class="value">{{ $renewals->value_display }}</span>
                    <div class="lei-stat-sub">{{ $renewals->meta['subtitle'] ?? '' }}</div>
                </div>
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
                    <div class="lei-stat-sub">{{ $pending->meta['subtitle'] ?? '' }}</div>
                </div>
            </div>
        @endif

        @if ($activeUsers)
            <div class="lei-stat-card">
                <div class="lei-stat-top">
                    <span class="label">{{ $activeUsers->label }}</span>
                    <span class="value">{{ $activeUsers->value_display }}</span>
                    <div class="lei-stat-sub">{{ $activeUsers->meta['subtitle'] ?? '' }}</div>
                </div>
            </div>
        @endif

        @if ($payments)
            <div class="lei-stat-card">
                <div class="lei-stat-top">
                    <span class="label">{{ $payments->label }}</span>
                    <span class="value">{{ $payments->value_display }}</span>
                    <div class="lei-stat-sub">{{ $payments->meta['subtitle'] ?? '' }}</div>
                </div>
            </div>
        @endif
    </div>

    <div class="lei-charts-row">
        <div class="lei-chart-card">
            <h3>Application Trends (Last 12 Months)</h3>
            <p style="margin:0 0 12px;font-size:13px;color:#64748b;">Applications received per month — live data from registry records.</p>
            <div class="lei-chart-canvas-wrap">
                <canvas id="trendsChart" height="300"></canvas>
            </div>
        </div>

        <div class="lei-health-card">
            <h3>Renewals Due This Month</h3>
            <p style="margin:0 0 12px;font-size:13px;color:#64748b;">Approved LEIs expiring in {{ now()->format('F Y') }}.</p>
            @if ($renewalsDue->isNotEmpty())
                <div class="lei-dash-renew-list">
                    @foreach ($renewalsDue as $app)
                        <div class="lei-dash-renew-item">
                            <div>
                                <strong>{{ $app->entity_name }}</strong>
                                <span class="lei-dash-renew-lei">{{ $app->lei_number ?? '—' }}</span>
                            </div>
                            <span class="lei-dash-renew-date">{{ $app->expiry_date?->format('M j') }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p style="font-size:13px;color:#94a3b8;padding:24px 0;text-align:center;">No renewals due this month.</p>
            @endif
        </div>
    </div>

    <div class="lei-chart-card" style="margin-top:20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h3 style="margin:0;">Recent Applications</h3>
            <a href="{{ route('admin.applications.index') }}" class="lei-btn-outline lei-btn-sm">View all</a>
        </div>
        @if ($recentApplications->isNotEmpty())
            <div class="lei-dash-app-table">
                <div class="lei-dash-app-head">
                    <span>Entity</span>
                    <span>Ref</span>
                    <span>Status</span>
                    <span>Submitted</span>
                </div>
                @foreach ($recentApplications as $app)
                    <a href="{{ route('admin.applications.index') }}?q={{ urlencode($app->application_code) }}" class="lei-dash-app-row">
                        <span><strong>{{ $app->entity_name }}</strong><small>{{ $app->user?->email }}</small></span>
                        <span class="mono">{{ $app->application_code }}</span>
                        <span><span class="lei-app-status lei-app-status--{{ $app->status_tone }}"><span class="dot"></span>{{ $app->status_label }}</span></span>
                        <span>{{ $app->submitted_on?->format('M j, Y') ?? $app->created_at->format('M j, Y') }}</span>
                    </a>
                @endforeach
            </div>
        @else
            <p style="font-size:13px;color:#94a3b8;padding:24px 0;text-align:center;">No applications yet.</p>
        @endif
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
            const maxVal = Math.max(1, ...trendData.map(d => d.main)) * 1.15;
            const groupW = chartW / trendData.length;
            const barW = Math.min(groupW * 0.6, 36);

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
                const mainH = (d.main / maxVal) * chartH;

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

@push('styles')
<style>
.lei-stats-grid--6 { grid-template-columns: repeat(3, 1fr); }
@media (max-width: 1100px) { .lei-stats-grid--6 { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 640px) { .lei-stats-grid--6 { grid-template-columns: 1fr; } }
.lei-dash-renew-list { display: flex; flex-direction: column; gap: 8px; max-height: 280px; overflow-y: auto; }
.lei-dash-renew-item { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 10px 12px; border: 1px solid #eef2f6; border-radius: 8px; font-size: 13px; }
.lei-dash-renew-item strong { display: block; font-size: 13px; color: #0f172a; }
.lei-dash-renew-lei { font-family: monospace; font-size: 11px; color: #64748b; }
.lei-dash-renew-date { font-size: 12px; font-weight: 600; color: #d97706; white-space: nowrap; }
.lei-dash-app-table { border: 1px solid #eef2f6; border-radius: 10px; overflow: hidden; }
.lei-dash-app-head, .lei-dash-app-row { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 12px; padding: 12px 16px; align-items: center; font-size: 13px; }
.lei-dash-app-head { background: #f8fafc; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #64748b; }
.lei-dash-app-row { border-top: 1px solid #eef2f6; text-decoration: none; color: inherit; }
.lei-dash-app-row:hover { background: #f8fafc; }
.lei-dash-app-row small { display: block; font-size: 11px; color: #94a3b8; margin-top: 2px; }
.lei-dash-app-row .mono { font-family: monospace; font-size: 12px; }
</style>
@endpush

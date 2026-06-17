<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationTrendMetric;
use App\Models\DashboardSnapshot;
use App\Models\ServiceHealthCheck;
use App\Models\LeiBusinessSetting;
use App\Models\SystemAlert;
use App\Services\DashboardStatsService;

class DashboardController extends Controller
{
    public function index(DashboardStatsService $dashboardStats)
    {
        $dashboardStats->syncLiveSnapshots();

        $alerts = SystemAlert::active()->orderByDesc('created_at')->limit(5)->get();
        $snapshots = DashboardSnapshot::orderBy('id')->get()->keyBy('metric_key');
        $trends = ApplicationTrendMetric::orderBy('year')->orderBy('month')->get();
        $services = ServiceHealthCheck::orderBy('sort_order')->get();

        $loadAverage = (int) round($services->avg('load_percent') ?: 42);

        $monthNames = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $chartData = $trends->map(function ($trend) use ($monthNames) {
            return [
                'label' => $monthNames[$trend->month] ?? (string) $trend->month,
                'main' => $trend->main_registry_count,
                'partner' => $trend->partner_api_count,
            ];
        })->values()->all();

        return view('admin.dashboard.index', [
            'alerts' => $alerts,
            'snapshots' => $snapshots,
            'trends' => $trends,
            'services' => $services,
            'loadAverage' => $loadAverage,
            'chartData' => $chartData,
            'businessSettings' => LeiBusinessSetting::current(),
        ]);
    }
}

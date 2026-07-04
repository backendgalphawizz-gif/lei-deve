<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DashboardSnapshot;
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
        $chartData = $dashboardStats->applicationTrendChart(12);
        $renewalsDue = $dashboardStats->renewalsDueThisMonth(15);
        $recentApplications = $dashboardStats->recentApplications(10);

        return view('admin.dashboard.index', [
            'alerts' => $alerts,
            'snapshots' => $snapshots,
            'chartData' => $chartData,
            'renewalsDue' => $renewalsDue,
            'recentApplications' => $recentApplications,
            'businessSettings' => LeiBusinessSetting::current(),
        ]);
    }
}

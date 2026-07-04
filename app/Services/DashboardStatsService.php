<?php

namespace App\Services;

use App\Models\DashboardSnapshot;
use App\Models\LeiApplication;
use App\Models\LeiSubscription;
use App\Models\User;
use App\Support\CurrencyFormatter;
use Carbon\Carbon;

class DashboardStatsService
{
    public function syncLiveSnapshots(): void
    {
        $totalApps = LeiApplication::count();
        $monthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        $monthlyApps = LeiApplication::where('created_at', '>=', $monthStart)->count();
        $lastMonthApps = LeiApplication::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $monthTrend = $lastMonthApps > 0
            ? round((($monthlyApps - $lastMonthApps) / $lastMonthApps) * 100, 1)
            : ($monthlyApps > 0 ? 100.0 : 0.0);

        $pending = LeiApplication::whereIn('status', ['new', 'under_review', 'pending', 'review', 'clarification'])->count();
        $activeUsers = User::where('is_active', true)->where('role', 'applicant')->count();
        $approvedApps = LeiApplication::where('status', 'approved')->count();

        $renewalsThisMonth = LeiApplication::query()
            ->where('status', 'approved')
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        $sparkline = $this->monthlySparkline(6);

        $this->upsert('total_applications', [
            'label' => 'Total Applications',
            'value_display' => CurrencyFormatter::formatNumber($totalApps),
            'value_numeric' => $totalApps,
            'trend_label' => ($monthTrend >= 0 ? '^ ' : 'v ').abs($monthTrend).'% vs last month',
            'trend_percent' => $monthTrend,
            'badge' => null,
            'meta' => [
                'sparkline' => $sparkline,
                'subtitle' => $approvedApps.' approved · '.$monthlyApps.' this month',
            ],
        ]);

        $this->upsert('monthly_applications', [
            'label' => 'This Month',
            'value_display' => (string) $monthlyApps,
            'value_numeric' => $monthlyApps,
            'trend_label' => now()->format('F Y'),
            'trend_percent' => $monthTrend,
            'badge' => null,
            'meta' => [
                'subtitle' => 'Applications received in '.now()->format('F'),
                'last_month' => $lastMonthApps,
            ],
        ]);

        $this->upsert('renewals_this_month', [
            'label' => 'Renewals Due',
            'value_display' => (string) $renewalsThisMonth,
            'value_numeric' => $renewalsThisMonth,
            'trend_label' => now()->format('M Y'),
            'trend_percent' => null,
            'badge' => $renewalsThisMonth > 0 ? 'THIS MONTH' : null,
            'meta' => [
                'subtitle' => 'Approved LEIs expiring in '.now()->format('F'),
            ],
        ]);

        $this->upsert('pending_approvals', [
            'label' => 'Pending Approvals',
            'value_display' => (string) $pending,
            'value_numeric' => $pending,
            'trend_label' => null,
            'trend_percent' => null,
            'badge' => $pending > 0 ? 'URGENT' : null,
            'meta' => [
                'subtitle' => 'Awaiting admin review',
            ],
        ]);

        $this->upsert('active_users', [
            'label' => 'Active Applicants',
            'value_display' => CurrencyFormatter::formatNumber($activeUsers),
            'value_numeric' => $activeUsers,
            'trend_label' => null,
            'trend_percent' => null,
            'badge' => null,
            'meta' => [
                'subtitle' => 'Verified applicant accounts',
                'progress' => min(100, max(0, (int) round(($activeUsers / max($totalApps, 1)) * 100))),
            ],
        ]);

        $payments24h = (float) LeiSubscription::query()
            ->where('payment_status', 'paid')
            ->where('starts_at', '>=', now()->subDay())
            ->sum('amount');

        $paymentsMonth = (float) LeiSubscription::query()
            ->where('payment_status', 'paid')
            ->where('starts_at', '>=', $monthStart)
            ->sum('amount');

        $this->upsert('payments_24h', [
            'label' => 'Payments (24h)',
            'value_display' => CurrencyFormatter::format($payments24h, 0),
            'value_numeric' => $payments24h,
            'trend_label' => null,
            'trend_percent' => null,
            'badge' => null,
            'meta' => [
                'revenue' => CurrencyFormatter::formatSignedCompact($paymentsMonth),
                'refunds' => CurrencyFormatter::formatSignedCompact(0),
                'subtitle' => CurrencyFormatter::format($paymentsMonth, 0).' collected this month',
            ],
        ]);
    }

    /**
     * @return array<int, int>
     */
    public function monthlySparkline(int $months = 6): array
    {
        $points = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $points[] = LeiApplication::query()
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
        }

        $max = max(1, ...$points);

        return array_map(fn (int $count) => (int) round(($count / $max) * 100), $points);
    }

    /**
     * @return array<int, array{label: string, main: int, partner: int}>
     */
    public function applicationTrendChart(int $months = 12): array
    {
        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = LeiApplication::query()
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $data[] = [
                'label' => $date->format('M'),
                'main' => $count,
                'partner' => 0,
            ];
        }

        return $data;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, LeiApplication>
     */
    public function renewalsDueThisMonth(int $limit = 10)
    {
        return LeiApplication::query()
            ->with('user')
            ->where('status', 'approved')
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->orderBy('expiry_date')
            ->limit($limit)
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, LeiApplication>
     */
    public function recentApplications(int $limit = 8)
    {
        return LeiApplication::query()
            ->with('user')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    private function upsert(string $key, array $data): void
    {
        DashboardSnapshot::updateOrCreate(['metric_key' => $key], $data);
    }
}

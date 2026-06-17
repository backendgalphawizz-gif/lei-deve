<?php

namespace App\Services;

use App\Models\DashboardSnapshot;
use App\Models\LeiApplication;
use App\Models\User;
use App\Support\CurrencyFormatter;

class DashboardStatsService
{
    public function syncLiveSnapshots(): void
    {
        $totalApps = LeiApplication::count();
        $pending = LeiApplication::whereIn('status', ['new', 'under_review', 'pending', 'review'])->count();
        $activeUsers = User::where('is_active', true)->where('role', '!=', 'super_admin')->count();

        $assignees = User::query()
            ->where('is_active', true)
            ->whereNotNull('name')
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get()
            ->map(fn (User $u) => [
                'initials' => $u->initials,
                'color' => $u->avatar_color,
            ])
            ->all();

        $this->upsert('total_applications', [
            'label' => 'Total Applications',
            'value_display' => CurrencyFormatter::formatNumber($totalApps),
            'value_numeric' => $totalApps,
            'trend_label' => DashboardSnapshot::where('metric_key', 'total_applications')->value('trend_label'),
            'trend_percent' => DashboardSnapshot::where('metric_key', 'total_applications')->value('trend_percent'),
            'badge' => null,
            'meta' => DashboardSnapshot::where('metric_key', 'total_applications')->value('meta') ?? ['sparkline' => [35, 50, 42, 65, 58, 78, 72, 85, 68, 90]],
        ]);

        $this->upsert('pending_approvals', [
            'label' => 'Pending Approvals',
            'value_display' => (string) $pending,
            'value_numeric' => $pending,
            'trend_label' => null,
            'trend_percent' => null,
            'badge' => $pending > 0 ? 'URGENT' : null,
            'meta' => [
                'subtitle' => 'Requires manual validation',
                'assignees' => $assignees,
                'extra_count' => max(0, $pending - count($assignees)),
            ],
        ]);

        $progress = min(100, max(10, (int) round(($activeUsers / max($totalApps, 1)) * 100)) ?: 84);

        $this->upsert('active_users', [
            'label' => 'Active Users',
            'value_display' => CurrencyFormatter::formatNumber($activeUsers),
            'value_numeric' => $activeUsers,
            'trend_label' => null,
            'trend_percent' => null,
            'badge' => null,
            'meta' => ['subtitle' => 'Peak load', 'progress' => $progress],
        ]);

        $existingPayments = DashboardSnapshot::where('metric_key', 'payments_24h')->first();
        $paymentTotal = (float) ($existingPayments?->value_numeric ?? 142500);

        $this->upsert('payments_24h', [
            'label' => 'Payments (24h)',
            'value_display' => CurrencyFormatter::format($paymentTotal, 0),
            'value_numeric' => $paymentTotal,
            'trend_label' => null,
            'trend_percent' => null,
            'badge' => null,
            'meta' => [
                'revenue' => CurrencyFormatter::formatSignedCompact(8200),
                'refunds' => CurrencyFormatter::formatSignedCompact(-1100),
            ],
        ]);
    }

    private function upsert(string $key, array $data): void
    {
        DashboardSnapshot::updateOrCreate(
            ['metric_key' => $key],
            $data
        );
    }
}

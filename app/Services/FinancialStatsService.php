<?php

namespace App\Services;

use App\Models\LeiSubscription;
use App\Support\CurrencyFormatter;
use Carbon\Carbon;

class FinancialStatsService
{
    /**
     * @return array<int, object>
     */
    public function summaryCards(): array
    {
        $revenue = (float) LeiSubscription::where('payment_status', 'paid')->sum('amount');
        $prevRevenue = (float) LeiSubscription::where('payment_status', 'paid')
            ->where('starts_at', '<', Carbon::now()->subDays(30))
            ->where('starts_at', '>=', Carbon::now()->subDays(60))
            ->sum('amount');
        $trend = $prevRevenue > 0
            ? round((($revenue - $prevRevenue) / $prevRevenue) * 100, 1)
            : ($revenue > 0 ? 100.0 : 0.0);

        $pendingPayments = LeiSubscription::where('payment_status', 'pending')->count();
        $paidCount = LeiSubscription::where('payment_status', 'paid')->count();
        $total = LeiSubscription::count();
        $rate = $total > 0 ? round(($paidCount / $total) * 100, 1) : 0.0;

        $monthRevenue = (float) LeiSubscription::where('payment_status', 'paid')
            ->where('starts_at', '>=', now()->startOfMonth())
            ->sum('amount');

        $gstEstimate = round($monthRevenue * 0.18, 2);

        return [
            (object) [
                'key' => 'revenue',
                'label' => 'Total Registry Revenue',
                'value' => CurrencyFormatter::format($revenue),
                'badge' => ($trend >= 0 ? '+' : '').$trend.'%',
                'badge_tone' => $trend >= 0 ? 'green' : 'red',
                'subtitle' => CurrencyFormatter::format($monthRevenue, 0).' this month',
            ],
            (object) [
                'key' => 'refunds',
                'label' => 'Pending Payments',
                'value' => $pendingPayments.' Pending',
                'badge' => $pendingPayments > 0 ? 'ACTION' : null,
                'badge_tone' => $pendingPayments > 0 ? 'red' : null,
                'subtitle' => $paidCount.' paid subscriptions',
            ],
            (object) [
                'key' => 'gateway',
                'label' => 'Payment Success Rate',
                'value' => CurrencyFormatter::formatNumber($rate, 1).'%',
                'badge' => 'LIVE',
                'badge_tone' => 'green',
                'subtitle' => $total.' total subscriptions',
                'sparkline' => $this->sparkline(),
            ],
            (object) [
                'key' => 'tax',
                'label' => 'GST Collected (Est.)',
                'value' => CurrencyFormatter::format($gstEstimate),
                'badge' => null,
                'badge_tone' => null,
                'subtitle' => now()->format('F Y').' · 18% on paid plans',
            ],
        ];
    }

    /**
     * @return array<int, int>
     */
    public function sparkline(): array
    {
        $rows = LeiSubscription::query()
            ->selectRaw("DATE(starts_at) as day, SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as ok, COUNT(*) as total")
            ->where('starts_at', '>=', Carbon::now()->subDays(10))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        if ($rows->isEmpty()) {
            return [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        }

        return $rows->map(fn ($r) => $r->total > 0 ? (int) round(($r->ok / $r->total) * 100) : 0)->values()->all();
    }

    public function gatewayMetrics(): array
    {
        return [];
    }
}

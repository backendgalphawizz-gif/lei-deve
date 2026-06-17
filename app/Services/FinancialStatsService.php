<?php

namespace App\Services;

use App\Support\CurrencyFormatter;
use App\Models\LeiFinancialTransaction;
use App\Models\LeiPaymentGateway;
use App\Models\LeiRefundRequest;
use Carbon\Carbon;
class FinancialStatsService
{
    /**
     * @return array<int, object>
     */
    public function summaryCards(): array
    {
        $revenue = (float) LeiFinancialTransaction::where('status', 'success')->sum('amount');
        $prevRevenue = (float) LeiFinancialTransaction::where('status', 'success')
            ->where('transacted_at', '<', Carbon::now()->subDays(30))
            ->where('transacted_at', '>=', Carbon::now()->subDays(60))
            ->sum('amount');
        $trend = $prevRevenue > 0
            ? round((($revenue - $prevRevenue) / $prevRevenue) * 100, 1)
            : 12.4;

        $pendingRefunds = LeiRefundRequest::where('status', 'pending')->count();
        $avgHours = (float) (LeiRefundRequest::where('status', 'pending')->avg('avg_response_hours') ?? 4.2);

        $total = LeiFinancialTransaction::count();
        $success = LeiFinancialTransaction::where('status', 'success')->count();
        $rate = $total > 0 ? round(($success / $total) * 100, 2) : 99.94;

        $taxLiability = round($revenue * 0.114, 2);

        return [
            (object) [
                'key' => 'revenue',
                'label' => 'Total Registry Revenue',
                'value' => CurrencyFormatter::format($revenue),
                'badge' => ($trend >= 0 ? '+' : '').$trend.'%',
                'badge_tone' => $trend >= 0 ? 'green' : 'red',
                'subtitle' => null,
            ],
            (object) [
                'key' => 'refunds',
                'label' => 'Pending Refunds Queue',
                'value' => $pendingRefunds.' Requests',
                'badge' => 'URGENT',
                'badge_tone' => 'red',
                'subtitle' => 'Avg. Response Time: '.CurrencyFormatter::formatNumber($avgHours, 1).'h',
            ],
            (object) [
                'key' => 'gateway',
                'label' => 'Gateway Success Rate',
                'value' => CurrencyFormatter::formatNumber($rate, 2).'%',
                'badge' => 'LIVE',
                'badge_tone' => 'green',
                'subtitle' => null,
                'sparkline' => $this->sparkline(),
            ],
            (object) [
                'key' => 'tax',
                'label' => 'Estimated Tax Liability',
                'value' => CurrencyFormatter::format($taxLiability),
                'badge' => null,
                'badge_tone' => null,
                'subtitle' => 'Q3 2023 · Next filing due in 12 days',
            ],
        ];
    }

    /**
     * @return array<int, int>
     */
    public function sparkline(): array
    {
        $rows = LeiFinancialTransaction::query()
            ->selectRaw("DATE(transacted_at) as day, SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as ok, COUNT(*) as total")
            ->where('transacted_at', '>=', Carbon::now()->subDays(10))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        if ($rows->isEmpty()) {
            return [88, 91, 90, 93, 95, 94, 96, 99, 98, 100];
        }

        return $rows->map(fn ($r) => $r->total > 0 ? (int) round(($r->ok / $r->total) * 100) : 99)->values()->all();
    }

    public function gatewayMetrics(): array
    {
        return LeiPaymentGateway::orderBy('sort_order')->get()->all();
    }
}

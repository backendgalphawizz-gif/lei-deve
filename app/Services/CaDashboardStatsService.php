<?php

namespace App\Services;

use App\Models\LeiCertificate;

class CaDashboardStatsService
{
    /**
     * @return array{pending: int, signed: int, rejected: int, signed_this_month: int, signed_today: int, total: int, avg_signing_days: float|null}
     */
    public function summary(): array
    {
        $pending = LeiCertificate::whereIn('status', ['unsigned', 'pending_ca'])->count();
        $signed = LeiCertificate::where('status', 'signed')->count();
        $rejected = LeiCertificate::where('status', 'rejected')->count();
        $signedThisMonth = LeiCertificate::query()
            ->where('status', 'signed')
            ->where('signed_at', '>=', now()->startOfMonth())
            ->count();
        $signedToday = LeiCertificate::query()
            ->where('status', 'signed')
            ->where('signed_at', '>=', now()->startOfDay())
            ->count();

        return [
            'pending' => $pending,
            'signed' => $signed,
            'rejected' => $rejected,
            'signed_this_month' => $signedThisMonth,
            'signed_today' => $signedToday,
            'total' => LeiCertificate::count(),
            'avg_signing_days' => $this->averageSigningDays(),
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, LeiCertificate>
     */
    public function recentSigned(int $limit = 5)
    {
        return LeiCertificate::query()
            ->with(['application.user', 'signer'])
            ->where('status', 'signed')
            ->whereNotNull('signed_at')
            ->orderByDesc('signed_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array<int, array{label: string, count: int}>
     */
    public function signingTrend(int $months = 6): array
    {
        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $data[] = [
                'label' => $date->format('M'),
                'count' => LeiCertificate::query()
                    ->where('status', 'signed')
                    ->whereYear('signed_at', $date->year)
                    ->whereMonth('signed_at', $date->month)
                    ->count(),
            ];
        }

        return $data;
    }

    public function averageSigningDays(): ?float
    {
        $certificates = LeiCertificate::query()
            ->where('status', 'signed')
            ->whereNotNull('signed_at')
            ->get(['created_at', 'signed_at']);

        if ($certificates->isEmpty()) {
            return null;
        }

        $totalDays = $certificates->sum(
            fn (LeiCertificate $certificate) => $certificate->created_at->diffInDays($certificate->signed_at)
        );

        return round($totalDays / $certificates->count(), 1);
    }
}

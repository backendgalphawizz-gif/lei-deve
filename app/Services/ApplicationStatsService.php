<?php

namespace App\Services;

use App\Models\LeiApplication;
use Carbon\Carbon;

class ApplicationStatsService
{
    /**
     * @return array<int, object{key: string, label: string, value: int, badge: ?string, badge_tone: ?string, icon: ?string}>
     */
    public function compute(): array
    {
        $counts = LeiApplication::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $since = Carbon::now()->subDays(7);
        $recent = LeiApplication::query()
            ->where('created_at', '>=', $since)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            $this->stat('new', 'New', $counts, $recent),
            $this->stat('pending', 'Pending', $counts, $recent, false),
            $this->stat('under_review', 'Under Review', $counts, $recent, false, 'load'),
            $this->stat('clarification', 'Clarification', $counts, $recent, true),
            $this->stat('approved', 'Approved', $counts, $recent, false, 'check'),
            $this->stat('rejected', 'Rejected', $counts, $recent, false, 'x'),
        ];
    }

    private function stat(
        string $key,
        string $label,
        $counts,
        $recent,
        bool $invertTrend = false,
        ?string $iconOrLoad = null
    ): object {
        $value = (int) ($counts[$key] ?? 0);
        $recentCount = (int) ($recent[$key] ?? 0);
        $badge = null;
        $badgeTone = null;
        $icon = null;

        if ($iconOrLoad === 'check') {
            $icon = 'check';
        } elseif ($iconOrLoad === 'x') {
            $icon = 'x';
        } elseif ($iconOrLoad === 'load') {
            $badge = $value > 50 ? 'High Load' : 'Normal';
            $badgeTone = $value > 50 ? 'orange' : 'muted';
        } elseif ($recentCount > 0 && $value > 0) {
            $pct = (int) round(($recentCount / max($value, 1)) * 100);
            $sign = $invertTrend ? '-' : '+';
            $badge = "{$sign}{$pct}%";
            $badgeTone = $invertTrend ? 'red' : 'green';
        } else {
            $badge = '--';
            $badgeTone = 'muted';
        }

        return (object) [
            'key' => $key,
            'label' => $label,
            'value' => $value,
            'badge' => $badge,
            'badge_tone' => $badgeTone,
            'icon' => $icon,
        ];
    }
}

<?php

namespace App\Services;

use App\Support\CurrencyFormatter;
use App\Models\User;
use Carbon\Carbon;

class UserManagementStatsService
{
    /**
     * @return array<int, object{metric_key: string, label: string, value_display: string, badge: ?string, badge_tone: ?string, accent: string}>
     */
    public function compute(): array
    {
        $baseQuery = User::query()->where('role', '!=', 'super_admin');

        $lockedNow = (clone $baseQuery)->where('account_status', 'locked')->count();
        $lockedSince = (clone $baseQuery)
            ->where('account_status', 'locked')
            ->where('updated_at', '>=', Carbon::now()->subHours(24))
            ->count();

        $mfaPending = (clone $baseQuery)
            ->whereIn('mfa_status', ['pending', 'warning'])
            ->count();

        $activeUsers = (clone $baseQuery)->where('account_status', 'active')->count();
        $sessionEstimate = max($activeUsers * 12, $activeUsers);

        return [
            (object) [
                'metric_key' => 'locked_accounts',
                'label' => 'Locked Accounts',
                'value_display' => (string) $lockedNow,
                'badge' => $lockedSince > 0
                    ? "+{$lockedSince} from last 24h"
                    : 'No change 24h',
                'badge_tone' => 'danger',
                'accent' => 'red',
            ],
            (object) [
                'metric_key' => 'mfa_pending',
                'label' => 'MFA Pending',
                'value_display' => (string) $mfaPending,
                'badge' => $mfaPending > 0 ? "Requires {$mfaPending} Attention" : 'All clear',
                'badge_tone' => 'warning',
                'accent' => 'gold',
            ],
            (object) [
                'metric_key' => 'active_sessions',
                'label' => 'Active Sessions',
                'value_display' => CurrencyFormatter::formatNumber($sessionEstimate),
                'badge' => $sessionEstimate > 1000 ? 'High Load' : 'Normal',
                'badge_tone' => 'info',
                'accent' => 'blue',
            ],
        ];
    }
}

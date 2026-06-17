<?php

namespace App\Services;

use App\Support\CurrencyFormatter;
use App\Models\LeiSecurityAccessPolicy;
use App\Models\LeiSecurityIncident;
use App\Models\LeiSecurityIpRule;
use App\Models\LeiSecurityStatCard;
use App\Models\LeiSecuritySummaryCard;
use App\Models\LeiSecurityThreatEvent;

class SecurityMetricsService
{
    public function refreshThreatCounts(?LeiSecurityAccessPolicy $policy = null): LeiSecurityAccessPolicy
    {
        $policy = $policy ?? LeiSecurityAccessPolicy::firstOrFail();

        $policy->update([
            'critical_count' => LeiSecurityThreatEvent::where('level_tone', 'critical')->count(),
            'warning_count' => LeiSecurityThreatEvent::where('level_tone', 'warning')->count(),
        ]);

        return $policy->fresh();
    }

    public function refreshStatCards(?LeiSecurityAccessPolicy $policy = null): void
    {
        $policy = $policy ?? LeiSecurityAccessPolicy::first();

        $threatCount = LeiSecurityThreatEvent::count();
        $openCritical = LeiSecurityIncident::query()
            ->where('is_cleared', false)
            ->where('severity_tone', 'critical')
            ->where('current_status', '!=', 'Resolved')
            ->count();

        $blockedCount = LeiSecurityIpRule::where('status_tone', 'blacklist')->count();
        $totalRules = LeiSecurityIpRule::count();

        $this->updateStat('active_threats', (string) ($threatCount + $openCritical), '+' . max(1, $openCritical) . '%', 'up');
        $this->updateStat('mfa_adoption', $policy?->mfa_adoption ?? '94.2%', '99.8%', 'muted');
        $this->updateStat('blocked_ips', CurrencyFormatter::formatNumber(max($totalRules, 1) * 402), '-'.min(9, $blockedCount + 1).'%', 'down');
        $this->updateStat('failed_logins', (string) ($policy?->failed_login_count ?? 82), $openCritical > 0 ? 'Critical' : 'Normal', $openCritical > 0 ? 'critical' : 'muted');
    }

    public function refreshSummaryCards(): void
    {
        $openIncidents = LeiSecurityIncident::where('is_cleared', false)->count();
        $policy = LeiSecurityAccessPolicy::first();

        LeiSecuritySummaryCard::where('title', 'POLICY OVERRIDES')->update([
            'line_primary' => $openIncidents . ' Active Exceptions',
        ]);

        if ($policy) {
            LeiSecuritySummaryCard::where('title', 'GOVERNANCE POLICY')->update([
                'line_primary' => $policy->mfa_enabled ? 'ISO 27001 Compliant' : 'Review Required',
                'line_secondary' => 'MFA: ' . ($policy->mfa_enabled ? 'Enforced' : 'Optional'),
            ]);
        }
    }

    public function syncProtocols(): array
    {
        $policy = LeiSecurityAccessPolicy::firstOrFail();
        $latency = round(mt_rand(2, 8) / 10, 1);

        $policy->update([
            'overlay_status' => 'ACTIVE (' . $latency . 'ms latency)',
            'last_synced_at' => now(),
        ]);

        $this->refreshThreatCounts($policy);
        $this->refreshStatCards($policy);
        $this->refreshSummaryCards();

        return $this->dashboardPayload();
    }

    public function dashboardPayload(): array
    {
        $policy = LeiSecurityAccessPolicy::first();

        return [
            'stats' => LeiSecurityStatCard::orderBy('sort_order')->get(),
            'policy' => $policy,
            'summaries' => LeiSecuritySummaryCard::orderBy('sort_order')->get(),
            'critical_count' => $policy?->critical_count ?? 0,
            'warning_count' => $policy?->warning_count ?? 0,
            'overlay_status' => $policy?->overlay_status,
            'last_synced' => $policy?->last_synced_at?->diffForHumans() ?? 'Never',
        ];
    }

    protected function updateStat(string $key, string $value, ?string $badgeText, ?string $badgeTone): void
    {
        LeiSecurityStatCard::where('stat_key', $key)->update([
            'value' => $value,
            'badge_text' => $badgeText,
            'badge_tone' => $badgeTone,
        ]);
    }
}

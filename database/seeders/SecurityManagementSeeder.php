<?php

namespace Database\Seeders;

use App\Models\AdminMenuItem;
use App\Models\LeiSecurityAccessPolicy;
use App\Models\LeiSecurityIncident;
use App\Models\LeiSecurityIpRule;
use App\Models\LeiSecurityStatCard;
use App\Models\LeiSecuritySummaryCard;
use App\Models\LeiSecurityThreatEvent;
use App\Services\SecurityMetricsService;
use Illuminate\Database\Seeder;

class SecurityManagementSeeder extends Seeder
{
    public function run(): void
    {
        AdminMenuItem::where('label', 'Security')->update([
            'route_name' => 'admin.security.index',
        ]);

        LeiSecurityStatCard::query()->delete();
        foreach ([
            ['active_threats', '14', 'Active Threats', 'red', '+12%', 'up', 1],
            ['mfa_adoption', '94.2%', 'MFA Adoption', 'blue', '99.8%', 'muted', 2],
            ['blocked_ips', '1,208', 'Blocked IPs', 'slate', '-5%', 'down', 3],
            ['failed_logins', '82', 'Failed Logins', 'orange', 'Critical', 'critical', 4],
        ] as $row) {
            LeiSecurityStatCard::create([
                'stat_key' => $row[0],
                'value' => $row[1],
                'label' => $row[2],
                'icon_tone' => $row[3],
                'badge_text' => $row[4],
                'badge_tone' => $row[5],
                'sort_order' => $row[6],
            ]);
        }

        LeiSecurityThreatEvent::query()->delete();
        LeiSecurityThreatEvent::create([
            'level' => 'CRITICAL',
            'level_tone' => 'critical',
            'title' => 'Attempted Brute Force - SG Cluster',
            'meta' => 'IP: 185.122.4.19 • Source: Moscow, RU',
            'time_label' => '2 mins ago',
            'sort_order' => 1,
        ]);
        LeiSecurityThreatEvent::create([
            'level' => 'WARNING',
            'level_tone' => 'warning',
            'title' => 'Anomalous Data Access Pattern',
            'meta' => 'User: LEI_ADM_99 • Action: Mass Download',
            'time_label' => '18 mins ago',
            'sort_order' => 2,
        ]);
        LeiSecurityThreatEvent::create([
            'level' => 'INFO',
            'level_tone' => 'info',
            'title' => 'Session Key Rotation Completed',
            'meta' => 'Global Keyring 0x4FF82... Rotated',
            'time_label' => '45 mins ago',
            'sort_order' => 3,
        ]);

        LeiSecurityAccessPolicy::query()->delete();
        LeiSecurityAccessPolicy::create([
            'mfa_adoption' => '94.2%',
            'failed_login_count' => 82,
            'last_synced_at' => now(),
        ]);

        LeiSecurityIpRule::query()->delete();
        foreach ([
            ['WHITELISTED', 'whitelist', '10.0.4.0/24', 'Berlin, DE (Internal)', 'Corporate HQ WiFi', 1],
            ['BLACKLISTED', 'blacklist', '185.122.4.0/24', 'Moscow, RU', 'Repeated Brute Force', 2],
            ['WHITELISTED', 'whitelist', '192.168.10.1/32', 'London, UK', 'Secure VPN Gateway', 3],
        ] as $row) {
            LeiSecurityIpRule::create([
                'status' => $row[0],
                'status_tone' => $row[1],
                'ip_range' => $row[2],
                'location' => $row[3],
                'context' => $row[4],
                'sort_order' => $row[5],
            ]);
        }

        LeiSecurityIncident::query()->delete();
        foreach ([
            ['#SEC-4092', 'Credential Stuffing Attempt', 'Target: API Endpoint /v1/auth', 'Critical', 'critical', '2 mins ago', 'Investigating', 'red', 'J. Doe', 'JD', 'MANAGE', 'manage', 'manage', 1],
            ['#SEC-4088', 'Unauthorized Key Export', 'KMS User: System_Service_X', 'High', 'high', '45 mins ago', 'Mitigated', 'orange', 'S. Miller', 'SM', 'INVESTIGATE', 'view', 'investigate', 2],
            ['#SEC-4081', 'Malicious Document Upload', 'Registry Submission: LEI-9921', 'Info', 'info', '3 hours ago', 'Resolved', 'grey', 'Automated', 'AU', 'DISMISS', 'audit', 'dismiss', 3],
        ] as $row) {
            LeiSecurityIncident::create([
                'incident_id' => $row[0],
                'title' => $row[1],
                'subtitle' => $row[2],
                'severity' => $row[3],
                'severity_tone' => $row[4],
                'last_event' => $row[5],
                'current_status' => $row[6],
                'status_tone' => $row[7],
                'assignee_name' => $row[8],
                'assignee_initials' => $row[9],
                'action_label' => $row[10],
                'action_style' => $row[11],
                'action_key' => $row[12],
                'sort_order' => $row[13],
            ]);
        }

        LeiSecuritySummaryCard::query()->delete();
        foreach ([
            ['ENCRYPTION HEALTH', 'yellow', 'yellow', 'AES-256-GCM Active', 'Next Key Rotation: 12 days', 1],
            ['GOVERNANCE POLICY', 'blue', 'blue', 'ISO 27001 Compliant', 'Audit Score: 98/100', 2],
            ['POLICY OVERRIDES', 'red', 'red', '2 Active Exceptions', 'Requires Level 5 Approval', 3],
        ] as $row) {
            LeiSecuritySummaryCard::create([
                'title' => $row[0],
                'border_tone' => $row[1],
                'icon_tone' => $row[2],
                'line_primary' => $row[3],
                'line_secondary' => $row[4],
                'sort_order' => $row[5],
            ]);
        }

        app(SecurityMetricsService::class)->refreshThreatCounts();
        app(SecurityMetricsService::class)->refreshStatCards();
        app(SecurityMetricsService::class)->refreshSummaryCards();
    }
}

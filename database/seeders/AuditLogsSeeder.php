<?php

namespace Database\Seeders;

use App\Models\AdminMenuItem;
use App\Models\LeiAuditConfig;
use App\Models\LeiAuditLogEntry;
use App\Models\LeiAuditStatCard;
use Illuminate\Database\Seeder;

class AuditLogsSeeder extends Seeder
{
    public function run(): void
    {
        AdminMenuItem::where('label', 'Audit Logs')->update([
            'route_name' => 'admin.audit.index',
        ]);

        LeiAuditStatCard::query()->delete();
        foreach ([
            ['total_events', '42,891', 'Total Events (24h)', 'chart', '+12% vs prev.', 'up', 1],
            ['security_alerts', '03', 'Security Alerts', 'alert', 'Action Req.', 'critical', 2],
            ['integrity', 'Healthy', 'Integrity Status', 'shield', 'Immutable', 'gold', 3],
            ['audit_nodes', '12 / 12', 'Active Audit Nodes', 'nodes', 'Active', 'green', 4],
        ] as $row) {
            LeiAuditStatCard::create([
                'stat_key' => $row[0],
                'value' => $row[1],
                'label' => $row[2],
                'icon_tone' => $row[3],
                'badge_text' => $row[4],
                'badge_tone' => $row[5],
                'sort_order' => $row[6],
            ]);
        }

        LeiAuditConfig::query()->delete();
        LeiAuditConfig::create([
            'uptime_percent' => '99.9992%',
            'sync_ms' => '12ms',
            'date_range_label' => 'Oct 24, 2023 - Today',
        ]);

        LeiAuditLogEntry::query()->delete();
        $rows = [
            [
                '2023-10-24 14:02:11',
                'CRITICAL', 'ADMIN', 'critical',
                'System Admin', '192.168.1.45',
                'Updated Global Tax Config (VAT Increase)',
                'Committed', 'committed',
                'view_changes',
                "Field: global_vat_rate\nBefore: 19.0%\nAfter: 21.0%\nApproved by: System Admin",
                1,
            ],
            [
                '2023-10-24 13:58:44',
                'INFO', 'USER', 'info',
                'Registry Clerk', '10.0.4.22',
                'User role assignment: LEI_Ops_Analyst',
                'Logged', 'logged',
                'info',
                null,
                2,
            ],
            [
                '2023-10-24 13:41:02',
                'WARNING', 'WORKFLOW', 'warning',
                'Workflow Engine', 'internal-svc',
                'SLA breach threshold exceeded on LEI-8841',
                'Alert Sent', 'alert',
                'menu',
                null,
                3,
            ],
            [
                '2023-10-24 12:15:33',
                'INFO', 'PAYMENT', 'info',
                'Payment Gateway', '185.122.4.19',
                'Settlement batch archived: BATCH-2023-10-24-A',
                'Archived', 'archived',
                'menu',
                null,
                4,
            ],
        ];

        foreach ($rows as $r) {
            LeiAuditLogEntry::create([
                'logged_at' => $r[0],
                'category_level' => $r[1],
                'category_domain' => $r[2],
                'category_tone' => $r[3],
                'actor_name' => $r[4],
                'actor_ip' => $r[5],
                'action_performed' => $r[6],
                'status_label' => $r[7],
                'status_tone' => $r[8],
                'action_type' => $r[9],
                'changes_detail' => $r[10],
                'sort_order' => $r[11],
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\AdminMenuItem;
use App\Models\LeiSlaConfig;
use App\Models\LeiSlaIncident;
use App\Models\LeiSlaInfraBar;
use App\Models\LeiSlaStatusCard;
use Illuminate\Database\Seeder;

class SlaMetricsSeeder extends Seeder
{
    public function run(): void
    {
        AdminMenuItem::where('label', 'SLA Metrics')->update([
            'route_name' => 'admin.sla.index',
        ]);

        LeiSlaStatusCard::query()->delete();
        foreach ([
            ['Server Cluster', 'Optimal', 'green', 'UPTIME', '99.98%', 'green', 1],
            ['API Gateway', 'Active', 'green', 'UPTIME', '99.99%', 'green', 2],
            ['SQL Database', 'Congestion', 'orange', 'UPTIME', '98.42%', 'orange', 3],
            ['Backup Integrity', 'Verified', 'green', 'HEALTH', '100%', 'green', 4],
        ] as $row) {
            LeiSlaStatusCard::create([
                'title' => $row[0],
                'status_label' => $row[1],
                'status_tone' => $row[2],
                'metric_label' => $row[3],
                'metric_value' => $row[4],
                'border_tone' => $row[5],
                'sort_order' => $row[6],
            ]);
        }

        LeiSlaInfraBar::query()->delete();
        $heights = [42, 58, 35, 72, 88, 45, 62, 38, 55, 48, 91, 40];
        foreach ($heights as $i => $h) {
            LeiSlaInfraBar::create([
                'height_percent' => $h,
                'is_alert' => $i === 10,
                'sort_order' => $i + 1,
            ]);
        }

        LeiSlaConfig::query()->delete();
        LeiSlaConfig::create([
            'cpu_threshold' => 85,
            'ram_threshold' => 90,
            'disk_threshold' => 95,
            'backup_last' => '22 mins ago',
            'backup_next' => '03:37:12',
            'api_latency' => '42ms',
            'api_err_rate' => '0.04%',
            'api_progress' => 72,
            'db_pools' => '124',
            'db_peak' => '1.2s',
            'db_segments' => [55, 25, 20],
        ]);

        LeiSlaIncident::query()->delete();
        foreach ([
            ['CRITICAL', 'critical', 'Node-04 / Main-SQL', 'Memory Leakage (Threshold Exceeded)', '00:14:22', 'ACKNOWLEDGE', 'acknowledge', 1],
            ['HIGH', 'high', 'API-Gateway-02', 'Latent Response (TLS Handshake Delay)', '00:08:15', 'INVESTIGATE', 'investigate', 2],
            ['INFO', 'info', 'Registry-Auth-v3', 'System Restart (Scheduled Maintenance)', '00:02:44', 'DISMISS', 'dismiss', 3],
        ] as $row) {
            LeiSlaIncident::create([
                'severity' => $row[0],
                'severity_tone' => $row[1],
                'target_node' => $row[2],
                'incident_type' => $row[3],
                'time_active' => $row[4],
                'action_label' => $row[5],
                'action_key' => $row[6],
                'sort_order' => $row[7],
            ]);
        }
    }
}

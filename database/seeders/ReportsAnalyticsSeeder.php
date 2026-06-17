<?php

namespace Database\Seeders;

use App\Models\AdminMenuItem;
use App\Models\LeiReportsChartPoint;
use App\Models\LeiReportsConfig;
use App\Models\LeiReportsGenerated;
use App\Models\LeiReportsStatCard;
use Illuminate\Database\Seeder;

class ReportsAnalyticsSeeder extends Seeder
{
    public function run(): void
    {
        AdminMenuItem::where('label', 'Reports & Analytics')->update([
            'route_name' => 'admin.reports.index',
        ]);

        LeiReportsStatCard::query()->delete();
        foreach ([
            ['throughput', '1,284,502', 'Total Throughput', 'Transactions processed per 24h', 'blue', '+12.4%', 'up', 1],
            ['financial_yield', '₹4.2 Cr', 'Financial Yield', 'Gross revenue generated (MTD)', 'gold', '+8.1%', 'up', 2],
            ['sla_compliance', '99.8%', 'SLA Compliance', 'System-wide availability target', 'sky', '-0.2%', 'down', 3],
            ['active_entities', '12,402', 'Active Entities', 'Unique active service nodes', 'nodes', 'Stable', 'muted', 4],
        ] as $row) {
            LeiReportsStatCard::create([
                'stat_key' => $row[0],
                'value' => $row[1],
                'label' => $row[2],
                'description' => $row[3],
                'icon_tone' => $row[4],
                'trend_text' => $row[5],
                'trend_tone' => $row[6],
                'sort_order' => $row[7],
            ]);
        }

        LeiReportsConfig::query()->delete();
        LeiReportsConfig::create([
            'sla_percent' => 99,
            'warning_alerts' => 4,
        ]);

        LeiReportsChartPoint::query()->delete();
        foreach ([
            ['MON', 52, 38, 1],
            ['TUE', 58, 42, 2],
            ['WED', 64, 46, 3],
            ['THU', 70, 50, 4],
            ['FRI', 78, 54, 5],
            ['SAT', 74, 52, 6],
            ['SUN', 82, 58, 7],
        ] as $row) {
            LeiReportsChartPoint::create([
                'day_label' => $row[0],
                'current_value' => $row[1],
                'previous_value' => $row[2],
                'sort_order' => $row[3],
            ]);
        }

        LeiReportsGenerated::query()->delete();
        foreach ([
            ['Q3 Financial Audit Summary', 'July - Sept 2023', 'Oct 24, 2023', 'READY', 'ready', 1],
            ['SLA Compliance Matrix - Node A', 'Last 24 Hours', 'Oct 24, 2023', 'PROCESSING', 'processing', 2],
            ['Security Incident Log', 'Sept 1 - Sept 30', 'Oct 01, 2023', 'EXPIRED', 'expired', 3],
        ] as $row) {
            LeiReportsGenerated::create([
                'report_name' => $row[0],
                'parameters' => $row[1],
                'generated_date' => $row[2],
                'status' => $row[3],
                'status_tone' => $row[4],
                'sort_order' => $row[5],
            ]);
        }
    }
}

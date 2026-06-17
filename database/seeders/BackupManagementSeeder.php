<?php

namespace Database\Seeders;

use App\Models\AdminMenuItem;
use App\Models\LeiBackupMetric;
use App\Models\LeiBackupSnapshot;
use App\Models\LeiComplianceReport;
use App\Models\LeiDrDrill;
use Illuminate\Database\Seeder;

class BackupManagementSeeder extends Seeder
{
    public function run(): void
    {
        AdminMenuItem::where('label', 'Backup')->update([
            'route_name' => 'admin.backup.index',
        ]);

        LeiBackupMetric::query()->delete();
        LeiBackupMetric::create([]);

        LeiBackupSnapshot::query()->delete();
        LeiBackupSnapshot::create([
            'snapshot_id' => '#SN-982-012A',
            'captured_at' => '2023-10-24 14:02:11',
            'type' => 'delta',
            'size_display' => '42.1 GB',
            'integrity_status' => 'Verified',
            'sort_order' => 1,
        ]);
        LeiBackupSnapshot::create([
            'snapshot_id' => '#SN-981-008F',
            'captured_at' => '2023-10-24 13:02:11',
            'type' => 'full',
            'size_display' => '4.2 TB',
            'integrity_status' => 'Verified',
            'sort_order' => 2,
        ]);

        LeiDrDrill::query()->delete();
        LeiDrDrill::create([
            'title' => 'Full Site Migration Simulation',
            'meta' => 'Completed in 1h 14m • US-EAST-1 to EU-WEST-2',
            'status' => 'SUCCESS',
            'completed_on' => '2023-10-12',
            'sort_order' => 1,
        ]);
        LeiDrDrill::create([
            'title' => 'Partial Data Restore Test',
            'meta' => 'Completed in 42m • US-EAST-1 internal',
            'status' => 'SUCCESS',
            'completed_on' => '2023-09-28',
            'sort_order' => 2,
        ]);

        LeiComplianceReport::query()->delete();
        foreach ([
            ['Q3 Recovery Audit', 'PDF • 14.2 MB', 1],
            ['Annual DR Certification', 'PDF • 8.6 MB', 2],
            ['Immutable Vault Manifest', 'CSV • 2.1 MB', 3],
            ['SOC2 Backup Appendix', 'PDF • 22.4 MB', 4],
        ] as [$title, $meta, $order]) {
            LeiComplianceReport::create([
                'title' => $title,
                'file_meta' => $meta,
                'sort_order' => $order,
            ]);
        }
    }
}

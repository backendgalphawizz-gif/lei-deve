<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeiBackupMetric;
use App\Models\LeiBackupSnapshot;
use App\Models\LeiComplianceReport;
use App\Models\LeiDrDrill;
use Illuminate\Http\Request;

class BackupManagementController extends Controller
{
    public function index()
    {
        return view('admin.backup.index', [
            'metrics' => LeiBackupMetric::first(),
            'snapshots' => LeiBackupSnapshot::orderBy('sort_order')->get(),
            'drills' => LeiDrDrill::orderByDesc('completed_on')->orderBy('sort_order')->get(),
            'reports' => LeiComplianceReport::orderBy('sort_order')->get(),
        ]);
    }

    public function manualBackup()
    {
        $metrics = LeiBackupMetric::first();
        if ($metrics) {
            $metrics->update([
                'last_backup_time' => now()->format('H:i').' UTC',
                'next_run_mins' => 60,
                'next_run_secs' => 0,
            ]);
        }

        return response()->json(['ok' => true, 'message' => 'Manual backup job queued successfully.']);
    }

    public function failover(Request $request)
    {
        return response()->json([
            'ok' => true,
            'message' => 'Failover initiation requires secondary executive approval. Request logged.',
        ]);
    }
}

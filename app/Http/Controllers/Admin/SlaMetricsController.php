<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeiSlaConfig;
use App\Models\LeiSlaIncident;
use App\Models\LeiSlaInfraBar;
use App\Models\LeiSlaStatusCard;
use Illuminate\Http\Request;

class SlaMetricsController extends Controller
{
    public function index()
    {
        return view('admin.sla.index', [
            'statusCards' => LeiSlaStatusCard::orderBy('sort_order')->get(),
            'infraBars' => LeiSlaInfraBar::orderBy('sort_order')->get(),
            'config' => LeiSlaConfig::first(),
            'incidents' => LeiSlaIncident::orderBy('sort_order')->get(),
        ]);
    }

    public function updateTriggers(Request $request)
    {
        $data = $request->validate([
            'cpu_threshold' => 'nullable|integer|min:0|max:100',
            'ram_threshold' => 'nullable|integer|min:0|max:100',
            'disk_threshold' => 'nullable|integer|min:0|max:100',
        ]);

        $config = LeiSlaConfig::first();
        if ($config) {
            $config->update(array_filter($data, fn ($v) => $v !== null));
        }

        return response()->json(['ok' => true, 'message' => 'Alert thresholds updated successfully.']);
    }

    public function manualTrigger()
    {
        $config = LeiSlaConfig::first();
        if ($config) {
            $config->update([
                'backup_last' => 'Just now',
                'backup_next' => 'In 04:00:00',
            ]);
        }

        return response()->json(['ok' => true, 'message' => 'Manual backup sync triggered.']);
    }

    public function incidentAction(Request $request, LeiSlaIncident $incident)
    {
        $request->validate(['action' => 'required|string|max:32']);

        if ($request->action === 'dismiss' || $request->action === 'acknowledge') {
            $incident->delete();
        }

        return response()->json(['ok' => true, 'message' => 'Incident action recorded.']);
    }

    public function clearInfoAlerts()
    {
        LeiSlaIncident::where('severity_tone', 'info')->delete();

        return response()->json(['ok' => true, 'message' => 'Info-level alerts cleared.']);
    }
}

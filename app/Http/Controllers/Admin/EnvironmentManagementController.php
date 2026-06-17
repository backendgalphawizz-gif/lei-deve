<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeiActivePipeline;
use App\Models\LeiDeploymentRecord;
use App\Models\LeiPendingRelease;
use App\Models\LeiRegistryEnvironment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnvironmentManagementController extends Controller
{
    public function index(Request $request)
    {
        $environments = LeiRegistryEnvironment::orderBy('sort_order')->get();
        $pipeline = LeiActivePipeline::where('is_active', true)->latest()->first();
        $deployments = LeiDeploymentRecord::orderByDesc('deployed_at')->limit(10)->get();
        $releases = LeiPendingRelease::where('approval_status', 'pending')->orderBy('sort_order')->get();
        $artifacts = \App\Models\LeiDeploymentArtifact::orderBy('sort_order')->limit(4)->get();

        return view('admin.environment.index', compact(
            'environments',
            'pipeline',
            'deployments',
            'releases',
            'artifacts'
        ));
    }

    public function triggerDeployment(Request $request)
    {
        $pipeline = LeiActivePipeline::where('is_active', true)->first();
        if ($pipeline) {
            $pipeline->update([
                'progress_percent' => min(100, $pipeline->progress_percent + 12),
                'progress_label' => 'Step progress: Deployment initiated by '.(auth()->user()->name ?? 'Admin'),
            ]);
        }

        return response()->json(['ok' => true, 'message' => 'Deployment pipeline triggered.']);
    }

    public function command(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:force_sync,rollback,lockout'],
        ]);

        $msg = match ($validated['action']) {
            'force_sync' => 'Force sync to PRODUCTION queued.',
            'rollback' => 'Manual rollback scheduled for review.',
            'lockout' => 'Emergency lockout activated. All deploy keys revoked.',
        };

        return response()->json(['ok' => true, 'message' => $msg]);
    }

    public function releaseAction(Request $request, LeiPendingRelease $release)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:approve,schedule,review'],
        ]);

        if ($validated['action'] === 'approve') {
            $release->update(['approval_status' => 'approved']);
        } elseif ($validated['action'] === 'schedule') {
            $release->update(['approval_status' => 'scheduled']);
        }

        return response()->json([
            'ok' => true,
            'message' => match ($validated['action']) {
                'approve' => "{$release->title} approved for deployment.",
                'schedule' => "{$release->title} scheduled for next maintenance window.",
                default => 'Release notes opened.',
            },
            'removed' => in_array($validated['action'], ['approve', 'schedule']),
        ]);
    }

    public function exportHistory(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Environment', 'Version', 'Administrator', 'Timestamp', 'Status']);
            LeiDeploymentRecord::orderByDesc('deployed_at')->each(function ($row) use ($handle) {
                fputcsv($handle, [
                    strtoupper($row->environment),
                    $row->version,
                    $row->administrator,
                    $row->deployed_at->format('M d, H:i:s'),
                    $row->status_label,
                ]);
            });
            fclose($handle);
        }, 'deployment-history-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }
}

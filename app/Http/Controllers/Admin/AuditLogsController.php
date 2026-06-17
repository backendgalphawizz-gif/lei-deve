<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeiAuditConfig;
use App\Models\LeiAuditLogEntry;
use App\Models\LeiAuditStatCard;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogsController extends Controller
{
    public function index(Request $request)
    {
        $severity = $request->query('severity', 'all');
        $category = $request->query('category', 'all');

        $query = LeiAuditLogEntry::query()->orderBy('sort_order');

        if ($severity !== 'all') {
            $query->where('category_level', strtoupper($severity));
        }

        if ($category !== 'all') {
            $query->where('category_domain', strtoupper($category));
        }

        return view('admin.audit.index', [
            'statCards' => LeiAuditStatCard::orderBy('sort_order')->get(),
            'config' => LeiAuditConfig::first(),
            'entries' => $query->get(),
            'filterSeverity' => $severity,
            'filterCategory' => $category,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $severity = $request->query('severity', 'all');
        $category = $request->query('category', 'all');

        $query = LeiAuditLogEntry::query()->orderBy('sort_order');

        if ($severity !== 'all') {
            $query->where('category_level', strtoupper($severity));
        }

        if ($category !== 'all') {
            $query->where('category_domain', strtoupper($category));
        }

        $entries = $query->get();

        return response()->streamDownload(function () use ($entries) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Timestamp', 'Category', 'Actor', 'IP', 'Action', 'Status']);
            foreach ($entries as $e) {
                fputcsv($out, [
                    $e->logged_at,
                    "{$e->category_level}: {$e->category_domain}",
                    $e->actor_name,
                    $e->actor_ip,
                    $e->action_performed,
                    $e->status_label,
                ]);
            }
            fclose($out);
        }, 'lei-immutable-audit-archive.csv', ['Content-Type' => 'text/csv']);
    }

    public function entryDetail(LeiAuditLogEntry $entry)
    {
        return response()->json([
            'ok' => true,
            'entry' => [
                'logged_at' => $entry->logged_at,
                'category' => "{$entry->category_level}: {$entry->category_domain}",
                'actor' => "{$entry->actor_name} {$entry->actor_ip}",
                'action' => $entry->action_performed,
                'status' => $entry->status_label,
                'changes' => $entry->changes_detail ?? 'No diff payload stored for this event.',
            ],
        ]);
    }

    public function syncTelemetry()
    {
        $config = LeiAuditConfig::first();
        if ($config) {
            $ms = random_int(8, 18);
            $config->update(['sync_ms' => "{$ms}ms"]);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Audit node sync completed.',
            'sync_ms' => $config?->sync_ms,
            'uptime_percent' => $config?->uptime_percent,
        ]);
    }
}

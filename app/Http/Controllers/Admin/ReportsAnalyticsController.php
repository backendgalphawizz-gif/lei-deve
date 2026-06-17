<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeiReportsChartPoint;
use App\Models\LeiReportsConfig;
use App\Models\LeiReportsGenerated;
use App\Models\LeiReportsStatCard;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $config = LeiReportsConfig::first();
        $tab = $request->query('tab', $config?->active_tab ?? 'operational');
        if ($config && $tab !== $config->active_tab) {
            $config->update(['active_tab' => $tab]);
        }

        return view('admin.reports.index', [
            'statCards' => LeiReportsStatCard::orderBy('sort_order')->get(),
            'config' => $config,
            'chartPoints' => LeiReportsChartPoint::orderBy('sort_order')->get(),
            'generated' => LeiReportsGenerated::orderBy('sort_order')->get(),
            'activeTab' => $tab,
        ]);
    }

    public function refresh(Request $request)
    {
        $config = LeiReportsConfig::first();
        if ($config) {
            $config->update([
                'warning_alerts' => random_int(2, 6),
                'sla_percent' => random_int(97, 99),
            ]);
        }

        LeiReportsChartPoint::all()->each(function ($point) {
            $point->update([
                'current_value' => min(100, max(20, $point->current_value + random_int(-8, 12))),
            ]);
        });

        return response()->json([
            'ok' => true,
            'message' => 'Analytics data refreshed.',
            'dashboard' => $this->dashboardPayload(),
        ]);
    }

    public function updatePeriod(Request $request)
    {
        $data = $request->validate(['period' => 'required|string|max:32']);
        $config = LeiReportsConfig::first();
        if ($config) {
            $config->update(['period_label' => $data['period']]);
        }

        return response()->json(['ok' => true, 'message' => 'Period updated.', 'period' => $data['period']]);
    }

    public function toggleScheduled(Request $request)
    {
        $config = LeiReportsConfig::first();
        if ($config) {
            $config->update(['scheduled_enabled' => ! $config->scheduled_enabled]);
        }

        return response()->json([
            'ok' => true,
            'message' => $config->scheduled_enabled ? 'Scheduled reports enabled.' : 'Scheduled reports paused.',
            'enabled' => $config->scheduled_enabled,
        ]);
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'date_range' => 'nullable|string|max:32',
            'category' => 'nullable|string|max:32',
            'entity' => 'nullable|string|max:32',
        ]);

        $config = LeiReportsConfig::first();
        if ($config) {
            $config->update(array_filter([
                'builder_date_range' => $data['date_range'] ?? null,
                'builder_category' => $data['category'] ?? null,
                'builder_entity' => $data['entity'] ?? null,
            ]));
        }

        $report = LeiReportsGenerated::create([
            'report_name' => 'Custom Analytics Export',
            'parameters' => ($data['date_range'] ?? 'Last 30 Days').' • '.($data['category'] ?? 'All Categories'),
            'generated_date' => now()->format('M d, Y'),
            'status' => 'PROCESSING',
            'status_tone' => 'processing',
            'sort_order' => 0,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Report generation started.',
            'report' => $this->formatReport($report),
        ]);
    }

    public function export(string $type): StreamedResponse
    {
        $filename = match ($type) {
            'pdf' => 'lei-institutional-report.pdf',
            'xlsx' => 'lei-structured-export.xlsx',
            default => 'lei-raw-data.csv',
        };

        if ($type === 'csv') {
            return response()->streamDownload(function () {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Metric', 'Value']);
                foreach (LeiReportsStatCard::orderBy('sort_order')->get() as $s) {
                    fputcsv($out, [$s->label, $s->value]);
                }
                fclose($out);
            }, $filename, ['Content-Type' => 'text/csv']);
        }

        return response()->streamDownload(function () use ($type) {
            echo "LEI Registry Services — {$type} export placeholder\n";
        }, $filename);
    }

    public function downloadReport(LeiReportsGenerated $report)
    {
        if ($report->status_tone === 'expired') {
            return response()->json(['message' => 'Report expired. Use Re-generate.'], 422);
        }

        return response()->streamDownload(function () use ($report) {
            echo "Report: {$report->report_name}\nParameters: {$report->parameters}\n";
        }, str_replace(' ', '-', strtolower($report->report_name)).'.csv');
    }

    public function deleteReport(LeiReportsGenerated $report)
    {
        $report->delete();

        return response()->json(['ok' => true, 'message' => 'Report removed.']);
    }

    public function regenerate(LeiReportsGenerated $report)
    {
        $report->update([
            'status' => 'READY',
            'status_tone' => 'ready',
            'generated_date' => now()->format('M d, Y'),
        ]);

        return response()->json(['ok' => true, 'message' => 'Report re-queued.', 'report' => $this->formatReport($report)]);
    }

    private function dashboardPayload(): array
    {
        $config = LeiReportsConfig::first();

        return [
            'stats' => LeiReportsStatCard::orderBy('sort_order')->get()->map(fn ($s) => [
                'stat_key' => $s->stat_key,
                'value' => $s->value,
                'trend_text' => $s->trend_text,
                'trend_tone' => $s->trend_tone,
            ]),
            'sla_percent' => $config?->sla_percent,
            'critical_incidents' => $config?->critical_incidents,
            'warning_alerts' => $config?->warning_alerts,
            'resolution_time' => $config?->resolution_time,
            'chart' => LeiReportsChartPoint::orderBy('sort_order')->get()->map(fn ($p) => [
                'day_label' => $p->day_label,
                'current_value' => $p->current_value,
                'previous_value' => $p->previous_value,
            ]),
        ];
    }

    private function formatReport(LeiReportsGenerated $report): array
    {
        return [
            'id' => $report->id,
            'report_name' => $report->report_name,
            'parameters' => $report->parameters,
            'generated_date' => $report->generated_date,
            'status' => $report->status,
            'status_tone' => $report->status_tone,
        ];
    }
}

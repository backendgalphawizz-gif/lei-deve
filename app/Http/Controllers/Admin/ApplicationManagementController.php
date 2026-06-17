<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeiApplication;
use App\Models\LeiApplicationAuditEvent;
use App\Services\ApplicationStatsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->filteredQuery($request);
        $activeUnderReview = LeiApplication::where('status', 'under_review')->count();
        $applications = $query->paginate(4)->withQueryString();

        $teams = LeiApplication::query()
            ->whereNotNull('assigned_team')
            ->distinct()
            ->orderBy('assigned_team')
            ->pluck('assigned_team');

        $selected = $this->resolveSelected($request, $applications);
        $stats = app(ApplicationStatsService::class)->compute();

        return view('admin.applications.index', compact(
            'applications',
            'stats',
            'teams',
            'selected',
            'activeUnderReview'
        ));
    }

    public function show(LeiApplication $application)
    {
        $application->load('auditEvents');

        return response()->json([
            'application' => $this->applicationPayload($application),
            'audit_events' => $this->auditPayload($application),
            'html' => view('admin.applications.partials.detail', compact('application'))->render(),
        ]);
    }

    public function detail(LeiApplication $application)
    {
        $application->load('auditEvents');

        return view('admin.applications.partials.detail', compact('application'));
    }

    public function action(Request $request, LeiApplication $application)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:approve,clarify,reject,reassign'],
            'team' => ['nullable', 'string', 'max:80'],
        ]);

        $actor = auth()->user()->name ?? 'System Admin';

        DB::transaction(function () use ($validated, $application, $actor) {
            $description = match ($validated['action']) {
                'approve' => "Application approved by {$actor}",
                'clarify' => "Clarification requested by {$actor}",
                'reject' => "Application rejected by {$actor}",
                'reassign' => 'Application reassigned to '.($validated['team'] ?? 'Unassigned')." by {$actor}",
            };

            $status = match ($validated['action']) {
                'approve' => 'approved',
                'clarify' => 'clarification',
                'reject' => 'rejected',
                'reassign' => $application->status,
            };

            $application->update([
                'status' => $status,
                'assigned_team' => $validated['action'] === 'reassign'
                    ? ($validated['team'] ?? $application->assigned_team)
                    : $application->assigned_team,
            ]);

            LeiApplicationAuditEvent::where('lei_application_id', $application->id)
                ->update(['is_highlight' => false]);

            LeiApplicationAuditEvent::create([
                'lei_application_id' => $application->id,
                'occurred_at' => now(),
                'description' => $description,
                'actor' => $actor,
                'is_highlight' => true,
                'sort_order' => 0,
            ]);
        });

        $application->refresh()->load('auditEvents');

        return response()->json([
            'success' => true,
            'message' => 'Application updated successfully.',
            'application' => $this->applicationPayload($application),
            'html' => view('admin.applications.partials.detail', compact('application'))->render(),
            'stats' => app(ApplicationStatsService::class)->compute(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $query = $this->filteredQuery($request);
        $filename = 'lei-applications-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Entity', 'Country', 'Type', 'Status', 'Priority', 'Team', 'Submitted']);

            $query->orderByDesc('submitted_on')->chunk(100, function ($rows) use ($handle) {
                foreach ($rows as $app) {
                    fputcsv($handle, [
                        $app->application_code,
                        $app->entity_name,
                        $app->country,
                        $app->issuance_type,
                        $app->status,
                        $app->priority,
                        $app->assigned_team,
                        $app->submitted_on->format('Y-m-d'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function filteredQuery(Request $request)
    {
        $query = LeiApplication::query()->orderByDesc('submitted_on');

        if ($status = $request->string('status')->trim()->toString()) {
            if (in_array($status, ['new', 'pending', 'under_review', 'clarification', 'approved', 'rejected'], true)) {
                $query->where('status', $status);
            }
        }

        if ($team = $request->string('team')->trim()->toString()) {
            $query->where('assigned_team', $team);
        }

        if ($priority = $request->string('priority')->trim()->toString()) {
            if (in_array($priority, ['high', 'med', 'low'], true)) {
                $query->where('priority', $priority);
            }
        }

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('application_code', 'like', "%{$search}%")
                    ->orWhere('entity_name', 'like', "%{$search}%");
            });
        }

        if ($range = $request->string('date_range')->trim()->toString()) {
            if (preg_match('/([A-Za-z]{3}\s+\d{1,2},\s+\d{4})\s*-\s*([A-Za-z]{3}\s+\d{1,2},\s+\d{4})/', $range, $m)) {
                try {
                    $from = Carbon::parse($m[1])->startOfDay();
                    $to = Carbon::parse($m[2])->endOfDay();
                    $query->whereBetween('submitted_on', [$from, $to]);
                } catch (\Throwable) {
                    // ignore invalid date
                }
            }
        }

        return $query;
    }

    private function resolveSelected(Request $request, $applications): ?LeiApplication
    {
        if ($request->filled('selected')) {
            $found = LeiApplication::with('auditEvents')
                ->where('application_code', $request->string('selected')->toString())
                ->first();
            if ($found) {
                return $found;
            }
        }

        if ($applications->isNotEmpty()) {
            return LeiApplication::with('auditEvents')->find($applications->first()->id);
        }

        return null;
    }

    private function applicationPayload(LeiApplication $application): array
    {
        return [
            'id' => $application->id,
            'application_code' => $application->application_code,
            'entity_name' => $application->entity_name,
            'country' => $application->country,
            'issuance_type' => $application->issuance_type,
            'status' => $application->status,
            'status_label' => $application->status_label,
            'status_tone' => $application->status_tone,
            'priority' => $application->priority,
            'assigned_team' => $application->assigned_team,
            'submitted_on' => $application->submitted_on->format('M d, Y'),
        ];
    }

    private function auditPayload(LeiApplication $application): array
    {
        return $application->auditEvents->map(fn ($e) => [
            'occurred_at' => $e->occurred_at->format('M d, H:i'),
            'description' => $e->description,
            'actor' => $e->actor,
            'is_highlight' => $e->is_highlight,
        ])->values()->all();
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeiSecurityAccessPolicy;
use App\Models\LeiSecurityIncident;
use App\Models\LeiSecurityIpRule;
use App\Models\LeiSecurityStatCard;
use App\Models\LeiSecuritySummaryCard;
use App\Models\LeiSecurityThreatEvent;
use App\Services\SecurityMetricsService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecurityManagementController extends Controller
{
    public function __construct(
        protected SecurityMetricsService $metrics
    ) {}

    public function index(Request $request)
    {
        $severity = $request->query('severity');

        $incidents = LeiSecurityIncident::query()
            ->where('is_cleared', false)
            ->when($severity && $severity !== 'all', fn ($q) => $q->where('severity_tone', $severity))
            ->orderBy('sort_order')
            ->get();

        return view('admin.security.index', [
            'statCards' => LeiSecurityStatCard::orderBy('sort_order')->get(),
            'threatEvents' => LeiSecurityThreatEvent::orderBy('sort_order')->get(),
            'policy' => LeiSecurityAccessPolicy::first(),
            'ipRules' => LeiSecurityIpRule::orderBy('sort_order')->get(),
            'incidents' => $incidents,
            'summaryCards' => LeiSecuritySummaryCard::orderBy('sort_order')->get(),
            'filterSeverity' => $severity ?? 'all',
        ]);
    }

    public function updatePolicy(Request $request)
    {
        $data = $request->validate([
            'mfa_enabled' => 'nullable|boolean',
            'session_timeout' => 'nullable|string|max:24',
            'max_login_attempts' => 'nullable|string|max:24',
            'mfa_adoption' => 'nullable|string|max:16',
            'failed_login_count' => 'nullable|integer|min:0|max:9999',
        ]);

        $policy = LeiSecurityAccessPolicy::firstOrFail();
        $policy->update(array_filter($data, fn ($v) => $v !== null));

        $this->metrics->refreshStatCards($policy);
        $this->metrics->refreshSummaryCards();

        return response()->json([
            'ok' => true,
            'message' => 'Access policy overrides updated.',
            'dashboard' => $this->metrics->dashboardPayload(),
        ]);
    }

    public function syncProtocols()
    {
        $dashboard = $this->metrics->syncProtocols();

        return response()->json([
            'ok' => true,
            'message' => 'Security protocols synchronized successfully.',
            'dashboard' => $dashboard,
        ]);
    }

    public function storeIpRule(Request $request)
    {
        $data = $request->validate([
            'status_tone' => 'required|in:whitelist,blacklist',
            'ip_range' => 'required|string|max:32',
            'location' => 'required|string|max:64',
            'context' => 'required|string|max:64',
        ]);

        $rule = LeiSecurityIpRule::create([
            'status' => $data['status_tone'] === 'whitelist' ? 'WHITELISTED' : 'BLACKLISTED',
            'status_tone' => $data['status_tone'],
            'ip_range' => $data['ip_range'],
            'location' => $data['location'],
            'context' => $data['context'],
            'sort_order' => (LeiSecurityIpRule::max('sort_order') ?? 0) + 1,
        ]);

        $this->metrics->refreshStatCards();

        return response()->json([
            'ok' => true,
            'message' => 'IP range added successfully.',
            'rule' => $rule,
            'dashboard' => $this->metrics->dashboardPayload(),
        ]);
    }

    public function deleteIpRule(LeiSecurityIpRule $rule)
    {
        $rule->delete();
        $this->metrics->refreshStatCards();

        return response()->json([
            'ok' => true,
            'message' => 'IP range removed from restriction list.',
            'dashboard' => $this->metrics->dashboardPayload(),
        ]);
    }

    public function incidentAction(Request $request, LeiSecurityIncident $incident)
    {
        $data = $request->validate([
            'action' => 'required|in:manage,investigate,dismiss,audit',
        ]);

        match ($data['action']) {
            'manage' => $incident->update([
                'current_status' => 'Investigating',
                'status_tone' => 'red',
                'action_label' => 'VIEW',
                'action_style' => 'view',
                'action_key' => 'audit',
            ]),
            'investigate' => $incident->update([
                'current_status' => 'Under Review',
                'status_tone' => 'orange',
            ]),
            'audit' => $incident->update([
                'current_status' => 'Audited',
                'status_tone' => 'grey',
                'action_label' => 'AUDIT',
                'action_style' => 'audit',
            ]),
            'dismiss' => $incident->update(['is_cleared' => true]),
            default => null,
        };

        $this->metrics->refreshStatCards();
        $this->metrics->refreshSummaryCards();

        return response()->json([
            'ok' => true,
            'message' => 'Incident action recorded.',
            'removed' => $data['action'] === 'dismiss',
            'incident' => $incident->fresh(),
            'dashboard' => $this->metrics->dashboardPayload(),
        ]);
    }

    public function clearInfoAlerts()
    {
        LeiSecurityIncident::where('severity_tone', 'info')->update(['is_cleared' => true]);

        $this->metrics->refreshStatCards();
        $this->metrics->refreshSummaryCards();

        return response()->json([
            'ok' => true,
            'message' => 'Info-level alerts cleared.',
            'dashboard' => $this->metrics->dashboardPayload(),
        ]);
    }

    public function exportReport(): StreamedResponse
    {
        $filename = 'security-incidents-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Title', 'Severity', 'Status', 'Assignee', 'Last Event']);

            LeiSecurityIncident::where('is_cleared', false)
                ->orderBy('sort_order')
                ->each(function (LeiSecurityIncident $inc) use ($handle) {
                    fputcsv($handle, [
                        $inc->incident_id,
                        $inc->title,
                        $inc->severity,
                        $inc->current_status,
                        $inc->assignee_name,
                        $inc->last_event,
                    ]);
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}

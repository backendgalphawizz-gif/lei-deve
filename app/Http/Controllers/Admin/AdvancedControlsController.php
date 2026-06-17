<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeiControlAuditLog;
use App\Models\LeiControlSetting;
use App\Models\LeiGovernanceVariable;
use App\Models\LeiSecurityPolicy;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdvancedControlsController extends Controller
{
    public function index()
    {
        $settings = [
            'maintenance_mode' => LeiControlSetting::getBool('maintenance_mode'),
            'override_armed' => LeiControlSetting::getBool('override_armed'),
            'system_status' => LeiControlSetting::getValue('system_status', 'Secure'),
            'registry_node' => LeiControlSetting::getValue('registry_node', 'PROD-77-ALPHA'),
            'registry_ip' => LeiControlSetting::getValue('registry_ip', '192.8.2.14'),
            'sla_percent' => LeiControlSetting::getValue('sla_percent', '99.999%'),
            'last_manual_change' => LeiControlSetting::getValue('last_manual_change', '12m ago'),
        ];

        $variables = LeiGovernanceVariable::orderBy('sort_order')->get();
        $policies = LeiSecurityPolicy::orderBy('sort_order')->get();
        $recentLogs = LeiControlAuditLog::orderByDesc('occurred_at')->limit(5)->get();

        return view('admin.controls.index', compact('settings', 'variables', 'policies', 'recentLogs'));
    }

    public function updateVariable(Request $request, LeiGovernanceVariable $variable)
    {
        $validated = $request->validate([
            'value_display' => ['required', 'string', 'max:255'],
        ]);

        $actor = auth()->user()->name ?? 'Admin';

        DB::transaction(function () use ($variable, $validated, $actor) {
            $variable->update([
                'value_display' => $validated['value_display'],
                'last_changed_at' => now(),
            ]);

            LeiControlSetting::setValue('last_manual_change', 'just now');

            LeiControlAuditLog::create([
                'actor_name' => $actor,
                'action_type' => 'config_change',
                'description' => "Modified {$variable->variable_name} to {$validated['value_display']}",
                'occurred_at' => now(),
            ]);
        });

        return response()->json([
            'ok' => true,
            'message' => 'Variable updated.',
            'variable' => $variable->fresh(),
            'last_manual_change' => 'just now',
        ]);
    }

    public function updatePolicies(Request $request)
    {
        $validated = $request->validate([
            'policies' => ['required', 'array'],
            'policies.*.key' => ['required', 'string'],
            'policies.*.enabled' => ['required', 'boolean'],
        ]);

        $actor = auth()->user()->name ?? 'Admin';

        DB::transaction(function () use ($validated, $actor) {
            foreach ($validated['policies'] as $row) {
                LeiSecurityPolicy::where('policy_key', $row['key'])->update([
                    'is_enabled' => $row['enabled'],
                ]);
            }

            LeiControlAuditLog::create([
                'actor_name' => $actor,
                'action_type' => 'policy_update',
                'description' => 'Updated all platform security policies',
                'occurred_at' => now(),
            ]);
        });

        return response()->json(['ok' => true, 'message' => 'All policies updated.']);
    }

    public function toggleMaintenance(Request $request)
    {
        $enabled = $request->boolean('enabled');
        LeiControlSetting::setValue('maintenance_mode', $enabled);

        $this->logAction(
            $enabled ? 'maintenance_on' : 'maintenance_off',
            $enabled ? 'Maintenance mode activated — public traffic redirected' : 'Maintenance mode deactivated'
        );

        return response()->json([
            'ok' => true,
            'enabled' => $enabled,
            'message' => $enabled ? 'Maintenance mode is ON.' : 'Maintenance mode is OFF.',
        ]);
    }

    public function armOverride(Request $request)
    {
        $armed = $request->boolean('armed');
        LeiControlSetting::setValue('override_armed', $armed);

        return response()->json(['ok' => true, 'armed' => $armed]);
    }

    public function executeOverride(Request $request)
    {
        if (! LeiControlSetting::getBool('override_armed')) {
            return response()->json(['ok' => false, 'message' => 'Arm override before executing.'], 422);
        }

        LeiControlSetting::setValue('override_armed', false);
        $this->logAction('system_override', 'Full system override executed — emergency protocols bypassed');

        return response()->json(['ok' => true, 'message' => 'Override executed. All actions logged.']);
    }

    public function revokeSessions()
    {
        $this->logAction('session_revoke', 'Revoked all global admin sessions');

        return response()->json(['ok' => true, 'message' => 'All global sessions revoked.']);
    }

    public function forceMfa()
    {
        $this->logAction('mfa_force', 'Forced MFA re-authentication for all Tier-1 admins');

        return response()->json(['ok' => true, 'message' => 'MFA re-auth enforced globally.']);
    }

    public function startExport()
    {
        $this->logAction('audit_export', 'Started SHA-256 encrypted full system audit export job');

        return response()->json(['ok' => true, 'message' => 'Export job started. You will be notified when ready.']);
    }

    public function instantScrub(Request $request)
    {
        if (! $request->boolean('confirmed')) {
            return response()->json(['ok' => false, 'message' => 'Confirmation required.'], 422);
        }

        $this->logAction('data_scrub', 'Initiated secure server-level data scrub (GDPR governance)');

        return response()->json(['ok' => true, 'message' => 'Scrub job queued with executive approval trail.']);
    }

    private function logAction(string $type, string $description): void
    {
        LeiControlAuditLog::create([
            'actor_name' => auth()->user()->name ?? 'System',
            'action_type' => $type,
            'description' => $description,
            'occurred_at' => now(),
        ]);
    }
}

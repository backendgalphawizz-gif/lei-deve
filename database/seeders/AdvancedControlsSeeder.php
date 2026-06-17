<?php

namespace Database\Seeders;

use App\Models\AdminMenuItem;
use App\Models\LeiControlSetting;
use App\Models\LeiGovernanceVariable;
use App\Models\LeiSecurityPolicy;
use App\Models\LeiControlAuditLog;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AdvancedControlsSeeder extends Seeder
{
    public function run(): void
    {
        AdminMenuItem::where('label', 'Advanced Controls')->update([
            'route_name' => 'admin.controls.index',
        ]);

        $settings = [
            'maintenance_mode' => '0',
            'override_armed' => '0',
            'system_status' => 'Secure',
            'registry_node' => 'PROD-77-ALPHA',
            'registry_ip' => '192.8.2.14',
            'sla_percent' => '99.999%',
            'last_manual_change' => '12m ago',
        ];

        foreach ($settings as $key => $value) {
            LeiControlSetting::updateOrCreate(['setting_key' => $key], ['value' => $value]);
        }

        LeiGovernanceVariable::whereNotIn('variable_name', [
            'AUTH_TIMEOUT_MS',
            'ENCRYPTION_LAYER_v4',
            'RATE_LIMIT_GLOBAL',
        ])->delete();

        $variables = [
            ['AUTH_TIMEOUT_MS', '3600000', 'medium', 1],
            ['ENCRYPTION_LAYER_v4', 'Active (AES-256)', 'critical', 2],
            ['RATE_LIMIT_GLOBAL', '5000 req/sec', 'low', 3],
        ];

        foreach ($variables as [$name, $val, $risk, $order]) {
            LeiGovernanceVariable::updateOrCreate(
                ['variable_name' => $name],
                [
                    'value_display' => $val,
                    'risk_level' => $risk,
                    'sort_order' => $order,
                    'last_changed_at' => Carbon::now()->subMinutes(12),
                ]
            );
        }

        $policies = [
            ['mandatory_mfa', 'Mandatory MFA', 'All users including read-only', true, 1],
            ['ip_whitelisting', 'IP Whitelisting', 'Restrict admin to VPN nodes', false, 2],
            ['strict_audit_logging', 'Strict Audit Logging', 'Log every keystroke/query', true, 3],
        ];

        foreach ($policies as [$key, $title, $desc, $on, $order]) {
            LeiSecurityPolicy::updateOrCreate(
                ['policy_key' => $key],
                [
                    'title' => $title,
                    'description' => $desc,
                    'is_enabled' => $on,
                    'sort_order' => $order,
                ]
            );
        }

        if (LeiControlAuditLog::count() === 0) {
            LeiControlAuditLog::insert([
                ['actor_name' => 'Admin Sarah W.', 'action_type' => 'config_change', 'description' => 'Modified RATE_LIMIT_GLOBAL to 5000 req/sec', 'occurred_at' => Carbon::now()->subMinutes(12), 'created_at' => now(), 'updated_at' => now()],
                ['actor_name' => 'System', 'action_type' => 'policy_update', 'description' => 'Strict audit logging enabled globally', 'occurred_at' => Carbon::now()->subHours(1), 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }
}

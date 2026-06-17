<?php

namespace Database\Seeders;

use App\Models\AdminMenuItem;
use App\Models\AdminRole;
use App\Models\Organization;
use App\Models\RoleModulePermission;
use App\Models\SystemModule;
use App\Models\User;
use App\Models\UserManagementStat;
use App\Models\UserModulePermission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserManagementSeeder extends Seeder
{
    public function run(): void
    {
        $orgs = [
            ['Federal Assets Hub', 'FAH'],
            ['Goldman Compliance', 'GC'],
            ['Asia-Pacific Registry', 'APR'],
            ['EU Central Node', 'EUC'],
            ['Partner API Gateway', 'PAG'],
        ];

        foreach ($orgs as [$name, $code]) {
            Organization::updateOrCreate(['code' => $code], ['name' => $name, 'is_active' => true]);
        }

        $roles = [
            ['Regional Admin', 'regional_admin', 'Full area access'],
            ['Data Analyst', 'data_analyst', 'SLA & Metrics focus'],
            ['Global Auditor', 'global_auditor', 'Read-only oversight'],
            ['Security Analyst', 'security_analyst', 'Security & audit focus'],
        ];

        foreach ($roles as [$name, $slug, $desc]) {
            AdminRole::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'description' => $desc,
                'is_active' => true,
            ]);
        }

        $modules = [
            ['Application Management', 'applications', 'Registry applications and LEI flow', 1],
            ['System Health', 'system_health', 'SLA monitoring & server diagnostics', 2],
            ['Payment Processing', 'payments', 'Transaction history and reconciliation', 3],
            ['User Monitoring', 'user_monitoring', 'Log oversight and session audits', 4],
        ];

        foreach ($modules as [$name, $slug, $desc, $order]) {
            SystemModule::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'description' => $desc,
                'sort_order' => $order,
            ]);
        }

        $mods = SystemModule::orderBy('sort_order')->get();

        $defaults = [
            'regional_admin' => [
                ['read' => 1, 'write' => 1, 'delete' => 0],
                ['read' => 1, 'write' => 1, 'delete' => 0],
                ['read' => 1, 'write' => 1, 'delete' => 0],
                ['read' => 1, 'write' => 0, 'delete' => 0],
            ],
            'data_analyst' => [
                ['read' => 1, 'write' => 0, 'delete' => 0],
                ['read' => 1, 'write' => 1, 'delete' => 0],
                ['read' => 0, 'write' => 0, 'delete' => 0],
                ['read' => 1, 'write' => 0, 'delete' => 0],
            ],
            'global_auditor' => [
                ['read' => 1, 'write' => 0, 'delete' => 0],
                ['read' => 1, 'write' => 0, 'delete' => 0],
                ['read' => 1, 'write' => 0, 'delete' => 0],
                ['read' => 1, 'write' => 0, 'delete' => 0],
            ],
            'security_analyst' => [
                ['read' => 1, 'write' => 1, 'delete' => 0],
                ['read' => 1, 'write' => 1, 'delete' => 0],
                ['read' => 0, 'write' => 0, 'delete' => 0],
                ['read' => 1, 'write' => 1, 'delete' => 0],
            ],
        ];

        foreach (AdminRole::where('is_active', true)->get() as $role) {
            $matrix = $defaults[$role->slug] ?? array_fill(0, count($mods), ['read' => 0, 'write' => 0, 'delete' => 0]);
            foreach ($mods as $i => $mod) {
                $p = $matrix[$i] ?? ['read' => 0, 'write' => 0, 'delete' => 0];
                RoleModulePermission::updateOrCreate(
                    ['admin_role_id' => $role->id, 'system_module_id' => $mod->id],
                    ['can_read' => $p['read'], 'can_write' => $p['write'], 'can_delete' => $p['delete']]
                );
            }
        }

        UserManagementStat::query()->delete();
        UserManagementStat::insert([
            ['metric_key' => 'locked_accounts', 'label' => 'Locked Accounts', 'value_display' => '1224h', 'badge' => '+2 from last', 'badge_tone' => 'danger', 'created_at' => now(), 'updated_at' => now()],
            ['metric_key' => 'mfa_pending', 'label' => 'MFA Pending', 'value_display' => '48', 'badge' => 'Attention', 'badge_tone' => 'warning', 'created_at' => now(), 'updated_at' => now()],
            ['metric_key' => 'active_sessions', 'label' => 'Active Sessions', 'value_display' => '1,429', 'badge' => 'High Load', 'badge_tone' => 'info', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $sampleUsers = [
            ['James Davidson', 'j.davidson@registry-ops.int', 'global_auditor', 'FAH', 'active', 'enabled'],
            ['Elena Rodriguez', 'e.rod@registry-ops.int', 'regional_admin', 'GC', 'active', 'enabled'],
            ['Marcus Chen', 'm.chen@registry-ops.int', 'security_analyst', 'APR', 'locked', 'warning'],
            ['Sarah Mitchell', 's.mitchell@registry-ops.int', 'data_analyst', 'EUC', 'active', 'pending'],
            ['David Okonkwo', 'd.okonkwo@registry-ops.int', 'regional_admin', 'PAG', 'active', 'disabled'],
            ['Priya Sharma', 'p.sharma@registry-ops.int', 'data_analyst', 'FAH', 'active', 'enabled'],
            ['Thomas Weber', 't.weber@registry-ops.int', 'global_auditor', 'GC', 'active', 'enabled'],
            ['Lisa Park', 'l.park@registry-ops.int', 'security_analyst', 'APR', 'locked', 'disabled'],
        ];

        foreach ($sampleUsers as [$name, $email, $roleSlug, $orgCode, $status, $mfa]) {
            $role = AdminRole::where('slug', $roleSlug)->first();
            $org = Organization::where('code', $orgCode)->first();
            if (! $role || ! $org) {
                continue;
            }

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'system_id' => $email,
                    'password' => Hash::make('Temp@12345'),
                    'role' => 'admin',
                    'organization_id' => $org->id,
                    'admin_role_id' => $role->id,
                    'account_status' => $status,
                    'mfa_status' => $mfa,
                    'is_active' => $status !== 'locked',
                ]
            );

            $rolePerms = RoleModulePermission::where('admin_role_id', $role->id)->get();
            foreach ($rolePerms as $rp) {
                UserModulePermission::updateOrCreate(
                    ['user_id' => $user->id, 'system_module_id' => $rp->system_module_id],
                    ['can_read' => $rp->can_read, 'can_write' => $rp->can_write, 'can_delete' => $rp->can_delete]
                );
            }
        }

        AdminMenuItem::where('label', 'User Management')->update(['route_name' => 'admin.users.index']);
    }
}

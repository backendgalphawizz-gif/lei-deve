<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CA_EMAIL = 'ca@registry-ops.int';

    private const DEFAULT_PASSWORD = '12345678';

    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('admin_roles') || ! Schema::hasTable('organizations')) {
            return;
        }

        $orgId = $this->ensureOrganization();
        $roleId = $this->ensureCaRole();
        if (! $orgId || ! $roleId) {
            return;
        }

        $this->ensureCaRolePermissions($roleId);

        $userId = $this->ensureCaUser($orgId, $roleId);
        if ($userId) {
            $this->syncUserPermissionsFromRole($userId, $roleId);
        }
    }

    public function down(): void
    {
        // Keep CA account on rollback.
    }

    private function ensureOrganization(): ?int
    {
        DB::table('organizations')->updateOrInsert(
            ['code' => 'FAH'],
            [
                'name' => 'Federal Assets Hub',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return DB::table('organizations')->where('code', 'FAH')->value('id');
    }

    private function ensureCaRole(): ?int
    {
        DB::table('admin_roles')->updateOrInsert(
            ['slug' => 'certificate_authority'],
            [
                'name' => 'Certificate Authority',
                'description' => 'Digital signing of LEI certificates (ISO 17442-2)',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return DB::table('admin_roles')->where('slug', 'certificate_authority')->value('id');
    }

    private function ensureCaRolePermissions(int $roleId): void
    {
        if (! Schema::hasTable('system_modules') || ! Schema::hasTable('role_module_permissions')) {
            return;
        }

        $modules = [
            ['Application Management', 'applications', 'Registry applications and LEI flow', 1],
            ['System Health', 'system_health', 'SLA monitoring & server diagnostics', 2],
            ['Payment Processing', 'payments', 'Transaction history and reconciliation', 3],
            ['User Monitoring', 'user_monitoring', 'Log oversight and session audits', 4],
            ['Certificate Signing', 'certificate_signing', 'CA queue and digital certificate signing', 5],
        ];

        foreach ($modules as [$name, $slug, $description, $order]) {
            DB::table('system_modules')->updateOrInsert(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'description' => $description,
                    'sort_order' => $order,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $moduleIds = DB::table('system_modules')->orderBy('sort_order')->pluck('id')->all();
        $matrix = [
            ['read' => 1, 'write' => 0, 'delete' => 0],
            ['read' => 0, 'write' => 0, 'delete' => 0],
            ['read' => 0, 'write' => 0, 'delete' => 0],
            ['read' => 0, 'write' => 0, 'delete' => 0],
            ['read' => 1, 'write' => 1, 'delete' => 0],
        ];

        foreach ($moduleIds as $index => $moduleId) {
            $perm = $matrix[$index] ?? ['read' => 0, 'write' => 0, 'delete' => 0];

            DB::table('role_module_permissions')->updateOrInsert(
                ['admin_role_id' => $roleId, 'system_module_id' => $moduleId],
                [
                    'can_read' => $perm['read'],
                    'can_write' => $perm['write'],
                    'can_delete' => $perm['delete'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function ensureCaUser(int $orgId, int $roleId): ?int
    {
        $existing = DB::table('users')->where('email', self::CA_EMAIL)->first();

        $payload = [
            'name' => 'CA Officer',
            'system_id' => self::CA_EMAIL,
            'role' => 'admin',
            'organization_id' => $orgId,
            'admin_role_id' => $roleId,
            'account_status' => 'active',
            'mfa_status' => 'enabled',
            'is_active' => true,
            'updated_at' => now(),
        ];

        if (! $existing) {
            DB::table('users')->insert(array_merge($payload, [
                'email' => self::CA_EMAIL,
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'created_at' => now(),
            ]));
        } else {
            // Only set password when missing — do not reset an existing CA password on re-migrate.
            if (empty($existing->password)) {
                $payload['password'] = Hash::make(self::DEFAULT_PASSWORD);
            }
            DB::table('users')->where('id', $existing->id)->update($payload);
        }

        return DB::table('users')->where('email', self::CA_EMAIL)->value('id');
    }

    private function syncUserPermissionsFromRole(int $userId, int $roleId): void
    {
        if (! Schema::hasTable('user_module_permissions') || ! Schema::hasTable('role_module_permissions')) {
            return;
        }

        $rolePerms = DB::table('role_module_permissions')
            ->where('admin_role_id', $roleId)
            ->get();

        foreach ($rolePerms as $perm) {
            DB::table('user_module_permissions')->updateOrInsert(
                ['user_id' => $userId, 'system_module_id' => $perm->system_module_id],
                [
                    'can_read' => $perm->can_read,
                    'can_write' => $perm->can_write,
                    'can_delete' => $perm->can_delete,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
};

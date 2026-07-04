<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('admin_menu_items')) {
            return;
        }

        DB::table('admin_menu_items')->whereIn('label', ['Certificate Authority', 'Website Management'])->delete();

        $payload = [
            'route_name' => 'admin.certificates.index',
            'icon' => 'certificate',
            'sort_order' => 4,
            'is_active' => true,
            'updated_at' => now(),
        ];

        $updated = DB::table('admin_menu_items')
            ->where('label', 'CA Management')
            ->update($payload);

        if ($updated === 0) {
            DB::table('admin_menu_items')->insert(array_merge($payload, [
                'label' => 'CA Management',
                'created_at' => now(),
            ]));
        }

        if (Schema::hasTable('admin_roles')) {
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
        }

        if (Schema::hasTable('system_modules')) {
            DB::table('system_modules')->updateOrInsert(
                ['slug' => 'certificate_signing'],
                [
                    'name' => 'Certificate Signing',
                    'description' => 'CA queue and digital certificate signing',
                    'sort_order' => 5,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        // Intentionally keep menu data on rollback.
    }
};

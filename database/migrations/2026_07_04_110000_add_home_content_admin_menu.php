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

        DB::table('admin_menu_items')->updateOrInsert(
            ['label' => 'Homepage LEI Content'],
            [
                'route_name' => 'admin.home-content.index',
                'icon' => 'pages',
                'sort_order' => 21,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        // Keep menu entry on rollback.
    }
};

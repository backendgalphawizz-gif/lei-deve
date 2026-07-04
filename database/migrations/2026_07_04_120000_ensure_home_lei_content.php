<?php

use Database\Seeders\HomeLeiContentSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lei_home_lei_blocks')) {
            return;
        }

        (new HomeLeiContentSeeder)->run();
    }

    public function down(): void
    {
        // Keep homepage content on rollback.
    }
};

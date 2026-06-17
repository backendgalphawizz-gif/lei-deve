<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lei_support_tickets', function (Blueprint $table) {
            $table->timestamp('closed_at')->nullable()->after('is_urgent');
            $table->timestamp('assigned_at')->nullable()->after('closed_at');
        });
    }

    public function down(): void
    {
        Schema::table('lei_support_tickets', function (Blueprint $table) {
            $table->dropColumn(['closed_at', 'assigned_at']);
        });
    }
};

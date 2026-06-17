<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lei_security_stat_cards', function (Blueprint $table) {
            $table->string('stat_key', 32)->nullable()->after('id');
        });

        Schema::table('lei_security_access_policies', function (Blueprint $table) {
            $table->string('mfa_adoption', 16)->default('94.2%')->after('mfa_enabled');
            $table->unsignedSmallInteger('failed_login_count')->default(82)->after('max_login_attempts');
            $table->timestamp('last_synced_at')->nullable()->after('overlay_status');
        });

        Schema::table('lei_security_incidents', function (Blueprint $table) {
            $table->boolean('is_cleared')->default(false)->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('lei_security_incidents', function (Blueprint $table) {
            $table->dropColumn('is_cleared');
        });

        Schema::table('lei_security_access_policies', function (Blueprint $table) {
            $table->dropColumn(['mfa_adoption', 'failed_login_count', 'last_synced_at']);
        });

        Schema::table('lei_security_stat_cards', function (Blueprint $table) {
            $table->dropColumn('stat_key');
        });
    }
};

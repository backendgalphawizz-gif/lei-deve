<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lei_business_settings', function (Blueprint $table) {
            $table->string('dashboard_title', 120)->default('System Overview')->after('header_notification_count');
            $table->string('dashboard_subtitle', 255)->nullable()->after('dashboard_title');
            $table->string('dashboard_period_label', 64)->default('Last 24 Hours')->after('dashboard_subtitle');
        });
    }

    public function down(): void
    {
        Schema::table('lei_business_settings', function (Blueprint $table) {
            $table->dropColumn(['dashboard_title', 'dashboard_subtitle', 'dashboard_period_label']);
        });
    }
};

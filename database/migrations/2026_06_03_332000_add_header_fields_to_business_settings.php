<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lei_business_settings', function (Blueprint $table) {
            $table->string('welcome_prefix', 32)->default('Welcome,')->after('search_placeholder');
            $table->string('header_subtitle', 150)->nullable()->after('welcome_prefix');
            $table->boolean('header_show_logo')->default(false)->after('header_subtitle');
            $table->string('header_logo_source', 16)->default('sidebar')->after('header_show_logo');
            $table->boolean('header_show_notifications')->default(true)->after('header_logo_source');
            $table->unsignedTinyInteger('header_notification_count')->default(1)->after('header_show_notifications');
        });
    }

    public function down(): void
    {
        Schema::table('lei_business_settings', function (Blueprint $table) {
            $table->dropColumn([
                'welcome_prefix',
                'header_subtitle',
                'header_show_logo',
                'header_logo_source',
                'header_show_notifications',
                'header_notification_count',
            ]);
        });
    }
};

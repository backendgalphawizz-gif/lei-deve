<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lei_business_settings', function (Blueprint $table) {
            $table->string('breadcrumb_root', 64)->default('Registry')->after('portal_title');
            $table->string('search_placeholder', 120)->default('Global Search...')->after('breadcrumb_root');
        });
    }

    public function down(): void
    {
        Schema::table('lei_business_settings', function (Blueprint $table) {
            $table->dropColumn(['breadcrumb_root', 'search_placeholder']);
        });
    }
};

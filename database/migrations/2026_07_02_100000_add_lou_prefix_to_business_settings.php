<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lei_business_settings', function (Blueprint $table) {
            $table->string('lou_prefix', 4)->default('5493')->after('registry_authority');
        });
    }

    public function down(): void
    {
        Schema::table('lei_business_settings', function (Blueprint $table) {
            $table->dropColumn('lou_prefix');
        });
    }
};

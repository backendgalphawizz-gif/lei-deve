<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lei_business_settings', function (Blueprint $table) {
            $table->unsignedSmallInteger('renewal_window_days')
                ->default(90)
                ->after('currency_symbol');
        });
    }

    public function down(): void
    {
        Schema::table('lei_business_settings', function (Blueprint $table) {
            $table->dropColumn('renewal_window_days');
        });
    }
};

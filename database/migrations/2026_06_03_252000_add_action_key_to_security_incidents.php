<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lei_security_incidents', function (Blueprint $table) {
            $table->string('action_key', 32)->default('manage')->after('action_style');
        });
    }

    public function down(): void
    {
        Schema::table('lei_security_incidents', function (Blueprint $table) {
            $table->dropColumn('action_key');
        });
    }
};

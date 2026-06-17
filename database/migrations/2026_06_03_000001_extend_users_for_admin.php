<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('system_id', 64)->unique()->nullable()->after('id');
            $table->string('role', 32)->default('super_admin')->after('password');
            $table->string('avatar')->nullable()->after('role');
            $table->string('tier', 16)->default('tier_1')->after('avatar');
            $table->boolean('is_active')->default(true)->after('tier');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['system_id', 'role', 'avatar', 'tier', 'is_active', 'last_login_at']);
        });
    }
};

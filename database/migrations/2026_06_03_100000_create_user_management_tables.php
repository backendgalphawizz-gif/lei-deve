<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 32)->unique()->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('admin_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug', 64)->unique();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('system_modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug', 64)->unique();
            $table->string('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('role_module_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('system_module_id')->constrained()->cascadeOnDelete();
            $table->boolean('can_read')->default(false);
            $table->boolean('can_write')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->timestamps();
            $table->unique(['admin_role_id', 'system_module_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('role')->constrained()->nullOnDelete();
            $table->foreignId('admin_role_id')->nullable()->after('organization_id')->constrained()->nullOnDelete();
            $table->enum('account_status', ['active', 'locked', 'pending'])->default('active')->after('admin_role_id');
            $table->enum('mfa_status', ['enabled', 'pending', 'disabled', 'warning'])->default('pending')->after('account_status');
            $table->string('job_title')->nullable()->after('mfa_status');
        });

        Schema::create('user_module_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('system_module_id')->constrained()->cascadeOnDelete();
            $table->boolean('can_read')->default(false);
            $table->boolean('can_write')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'system_module_id']);
        });

        Schema::create('user_management_stats', function (Blueprint $table) {
            $table->id();
            $table->string('metric_key', 64)->unique();
            $table->string('label');
            $table->string('value_display');
            $table->string('badge')->nullable();
            $table->string('badge_tone', 16)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_management_stats');
        Schema::dropIfExists('user_module_permissions');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropForeign(['admin_role_id']);
            $table->dropColumn(['organization_id', 'admin_role_id', 'account_status', 'mfa_status', 'job_title']);
        });
        Schema::dropIfExists('role_module_permissions');
        Schema::dropIfExists('system_modules');
        Schema::dropIfExists('admin_roles');
        Schema::dropIfExists('organizations');
    }
};

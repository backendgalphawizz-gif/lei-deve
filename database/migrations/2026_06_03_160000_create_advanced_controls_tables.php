<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lei_control_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 64)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('lei_governance_variables', function (Blueprint $table) {
            $table->id();
            $table->string('variable_name', 80)->unique();
            $table->string('value_display');
            $table->enum('risk_level', ['low', 'medium', 'critical'])->default('medium');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('last_changed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lei_security_policies', function (Blueprint $table) {
            $table->id();
            $table->string('policy_key', 64)->unique();
            $table->string('title');
            $table->string('description')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lei_control_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('actor_name');
            $table->string('action_type', 64);
            $table->text('description');
            $table->timestamp('occurred_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lei_control_audit_logs');
        Schema::dropIfExists('lei_security_policies');
        Schema::dropIfExists('lei_governance_variables');
        Schema::dropIfExists('lei_control_settings');
    }
};

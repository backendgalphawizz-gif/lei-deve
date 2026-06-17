<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lei_security_stat_cards', function (Blueprint $table) {
            $table->id();
            $table->string('value', 32);
            $table->string('label', 64);
            $table->string('icon_tone', 16)->default('red');
            $table->string('badge_text', 32)->nullable();
            $table->string('badge_tone', 16)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lei_security_threat_events', function (Blueprint $table) {
            $table->id();
            $table->string('level', 16);
            $table->string('level_tone', 16);
            $table->string('title');
            $table->text('meta');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lei_security_access_policies', function (Blueprint $table) {
            $table->id();
            $table->boolean('mfa_enabled')->default(true);
            $table->string('session_timeout', 16)->default('15 min');
            $table->string('max_login_attempts', 16)->default('3 attempts');
            $table->unsignedSmallInteger('critical_count')->default(3);
            $table->unsignedSmallInteger('warning_count')->default(12);
            $table->string('overlay_status', 64)->default('ACTIVE (0.4ms latency)');
            $table->timestamps();
        });

        Schema::create('lei_security_ip_rules', function (Blueprint $table) {
            $table->id();
            $table->string('status', 16);
            $table->string('status_tone', 16);
            $table->string('ip_range', 32);
            $table->string('location', 64);
            $table->string('context', 64);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lei_security_incidents', function (Blueprint $table) {
            $table->id();
            $table->string('incident_id', 16);
            $table->string('title');
            $table->string('subtitle');
            $table->string('severity', 16);
            $table->string('severity_tone', 16);
            $table->string('last_event', 32);
            $table->string('current_status', 32);
            $table->string('status_tone', 16);
            $table->string('assignee_name', 32);
            $table->string('assignee_initials', 8);
            $table->string('action_label', 16);
            $table->string('action_style', 16)->default('manage');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lei_security_summary_cards', function (Blueprint $table) {
            $table->id();
            $table->string('title', 32);
            $table->string('border_tone', 16);
            $table->string('icon_tone', 16);
            $table->string('line_primary');
            $table->string('line_secondary');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lei_security_summary_cards');
        Schema::dropIfExists('lei_security_incidents');
        Schema::dropIfExists('lei_security_ip_rules');
        Schema::dropIfExists('lei_security_access_policies');
        Schema::dropIfExists('lei_security_threat_events');
        Schema::dropIfExists('lei_security_stat_cards');
    }
};

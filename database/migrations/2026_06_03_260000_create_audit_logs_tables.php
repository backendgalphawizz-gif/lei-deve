<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lei_audit_stat_cards', function (Blueprint $table) {
            $table->id();
            $table->string('stat_key', 32)->unique();
            $table->string('value', 32);
            $table->string('label', 64);
            $table->string('icon_tone', 16)->default('chart');
            $table->string('badge_text', 48)->nullable();
            $table->string('badge_tone', 16)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lei_audit_config', function (Blueprint $table) {
            $table->id();
            $table->string('uptime_percent', 16)->default('99.9992%');
            $table->string('sync_ms', 16)->default('12ms');
            $table->string('date_range_label', 64)->default('Oct 24, 2023 - Today');
            $table->timestamps();
        });

        Schema::create('lei_audit_log_entries', function (Blueprint $table) {
            $table->id();
            $table->string('logged_at', 32);
            $table->string('category_level', 16);
            $table->string('category_domain', 16);
            $table->string('category_tone', 16);
            $table->string('actor_name', 64);
            $table->string('actor_ip', 64);
            $table->string('action_performed');
            $table->string('status_label', 32);
            $table->string('status_tone', 16);
            $table->string('action_type', 16)->default('menu');
            $table->text('changes_detail')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lei_audit_log_entries');
        Schema::dropIfExists('lei_audit_config');
        Schema::dropIfExists('lei_audit_stat_cards');
    }
};

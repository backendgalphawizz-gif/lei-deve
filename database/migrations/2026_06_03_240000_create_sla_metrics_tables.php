<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lei_sla_status_cards', function (Blueprint $table) {
            $table->id();
            $table->string('title', 64);
            $table->string('status_label', 32);
            $table->string('status_tone', 16)->default('green');
            $table->string('metric_label', 32)->default('UPTIME');
            $table->string('metric_value', 32);
            $table->string('border_tone', 16)->default('green');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lei_sla_infra_bars', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('height_percent')->default(50);
            $table->boolean('is_alert')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lei_sla_config', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('cpu_threshold')->default(85);
            $table->unsignedTinyInteger('ram_threshold')->default(90);
            $table->unsignedTinyInteger('disk_threshold')->default(95);
            $table->string('backup_last', 64)->default('22 mins ago');
            $table->string('backup_next', 64)->default('03:37:12');
            $table->string('api_latency', 16)->default('42ms');
            $table->string('api_err_rate', 16)->default('0.04%');
            $table->unsignedTinyInteger('api_progress')->default(72);
            $table->string('db_pools', 16)->default('124');
            $table->string('db_peak', 16)->default('1.2s');
            $table->json('db_segments')->nullable();
            $table->timestamps();
        });

        Schema::create('lei_sla_incidents', function (Blueprint $table) {
            $table->id();
            $table->string('severity', 16);
            $table->string('severity_tone', 16);
            $table->string('target_node', 64);
            $table->string('incident_type', 128);
            $table->string('time_active', 16);
            $table->string('action_label', 32);
            $table->string('action_key', 32);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lei_sla_incidents');
        Schema::dropIfExists('lei_sla_config');
        Schema::dropIfExists('lei_sla_infra_bars');
        Schema::dropIfExists('lei_sla_status_cards');
    }
};

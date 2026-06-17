<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lei_reports_stat_cards', function (Blueprint $table) {
            $table->id();
            $table->string('stat_key', 32)->unique();
            $table->string('value', 32);
            $table->string('label', 64);
            $table->string('description', 128)->nullable();
            $table->string('icon_tone', 16)->default('blue');
            $table->string('trend_text', 32)->nullable();
            $table->string('trend_tone', 16)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lei_reports_config', function (Blueprint $table) {
            $table->id();
            $table->string('period_label', 32)->default('Last 30 Days');
            $table->string('active_tab', 16)->default('operational');
            $table->boolean('scheduled_enabled')->default(true);
            $table->string('next_scheduled', 64)->default('Monday, 08:00 AM');
            $table->string('builder_date_range', 32)->default('Last 30 Days');
            $table->string('builder_category', 32)->default('All Categories');
            $table->string('builder_entity', 32)->default('Global Domain');
            $table->unsignedTinyInteger('sla_percent')->default(99);
            $table->unsignedSmallInteger('critical_incidents')->default(0);
            $table->unsignedSmallInteger('warning_alerts')->default(4);
            $table->string('resolution_time', 16)->default('12m 45s');
            $table->timestamps();
        });

        Schema::create('lei_reports_chart_points', function (Blueprint $table) {
            $table->id();
            $table->string('day_label', 8);
            $table->unsignedTinyInteger('current_value')->default(50);
            $table->unsignedTinyInteger('previous_value')->default(40);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lei_reports_generated', function (Blueprint $table) {
            $table->id();
            $table->string('report_name');
            $table->string('parameters');
            $table->string('generated_date', 32);
            $table->string('status', 16);
            $table->string('status_tone', 16);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lei_reports_generated');
        Schema::dropIfExists('lei_reports_chart_points');
        Schema::dropIfExists('lei_reports_config');
        Schema::dropIfExists('lei_reports_stat_cards');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lei_backup_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('last_backup_time', 16)->default('14:02 UTC');
            $table->string('last_backup_size', 16)->default('4.2 TB');
            $table->string('integrity_label', 32)->default('100%');
            $table->string('dr_nodes', 32)->default('03 Nodes Active');
            $table->string('dr_status', 64)->default('Global Standby Operational');
            $table->unsignedSmallInteger('rpo_minutes')->default(15);
            $table->unsignedTinyInteger('rpo_percent')->default(35);
            $table->unsignedSmallInteger('rto_hours')->default(2);
            $table->string('rto_sla', 16)->default('4h');
            $table->string('rto_badge', 32)->default('Exceeding');
            $table->string('primary_site', 32)->default('US-EAST-1');
            $table->boolean('is_synced')->default(true);
            $table->unsignedSmallInteger('latency_ms')->default(14);
            $table->string('frequency', 64)->default('Hourly Differential');
            $table->string('retention', 32)->default('7 Years Archive');
            $table->unsignedSmallInteger('next_run_mins')->default(24);
            $table->unsignedSmallInteger('next_run_secs')->default(12);
            $table->unsignedTinyInteger('site_health_blocks')->default(6);
            $table->timestamps();
        });

        Schema::create('lei_backup_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('snapshot_id', 32);
            $table->timestamp('captured_at');
            $table->enum('type', ['delta', 'full'])->default('delta');
            $table->string('size_display', 16);
            $table->string('integrity_status', 32)->default('Verified');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lei_dr_drills', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('meta');
            $table->string('status', 32)->default('SUCCESS');
            $table->date('completed_on');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lei_compliance_reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('file_meta', 32);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lei_compliance_reports');
        Schema::dropIfExists('lei_dr_drills');
        Schema::dropIfExists('lei_backup_snapshots');
        Schema::dropIfExists('lei_backup_metrics');
    }
};

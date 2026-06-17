<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lei_registry_environments', function (Blueprint $table) {
            $table->id();
            $table->string('env_key', 16)->unique();
            $table->string('label', 32);
            $table->string('uptime_display', 32);
            $table->enum('status_tone', ['green', 'orange', 'blue', 'red'])->default('green');
            $table->string('version', 32);
            $table->string('deployed_meta');
            $table->string('footer_label', 64);
            $table->string('footer_value', 64);
            $table->string('footer_tone', 16)->default('muted');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lei_active_pipelines', function (Blueprint $table) {
            $table->id();
            $table->string('build_number', 32);
            $table->string('target_environment', 32);
            $table->json('steps');
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->string('progress_label');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('lei_deployment_records', function (Blueprint $table) {
            $table->id();
            $table->string('environment', 16);
            $table->enum('environment_tone', ['gray', 'orange', 'blue'])->default('gray');
            $table->string('version', 32);
            $table->string('administrator');
            $table->string('auth_id', 32)->nullable();
            $table->timestamp('deployed_at');
            $table->enum('status', ['success', 'failed', 'pending'])->default('success');
            $table->string('status_detail', 32)->nullable();
            $table->timestamps();
        });

        Schema::create('lei_pending_releases', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('badge', ['critical', 'feature', 'patch'])->default('feature');
            $table->text('description')->nullable();
            $table->enum('approval_status', ['pending', 'approved', 'scheduled'])->default('pending');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lei_deployment_artifacts', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('version_label', 32);
            $table->string('size_display', 16);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lei_deployment_artifacts');
        Schema::dropIfExists('lei_pending_releases');
        Schema::dropIfExists('lei_deployment_records');
        Schema::dropIfExists('lei_active_pipelines');
        Schema::dropIfExists('lei_registry_environments');
    }
};

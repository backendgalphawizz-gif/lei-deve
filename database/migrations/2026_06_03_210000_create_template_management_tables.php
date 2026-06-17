<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lei_workflow_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Enterprise Security Clearance');
            $table->string('module', 64)->default('registry_services');
            $table->string('initial_state', 32)->default('Draft');
            $table->unsignedSmallInteger('sla_hours')->default(48);
            $table->string('total_nodes_label', 32)->default('4 States');
            $table->string('escalation_depth', 32)->default('L3 Authority');
            $table->string('automation_tier', 64)->default('Full-Registry Sync');
            $table->timestamp('last_synced_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('lei_workflow_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('lei_workflow_templates')->cascadeOnDelete();
            $table->string('rule_label', 64)->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('accent', ['core', 'auto', 'approval'])->default('core');
            $table->enum('rule_type', ['initial', 'transition', 'final_placeholder'])->default('transition');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lei_workflow_states');
        Schema::dropIfExists('lei_workflow_templates');
    }
};

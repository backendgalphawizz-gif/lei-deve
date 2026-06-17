<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lei_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_code', 32)->unique();
            $table->string('entity_name');
            $table->string('country', 64);
            $table->string('issuance_type', 64)->default('Direct Issuance');
            $table->enum('status', ['new', 'pending', 'under_review', 'clarification', 'approved', 'rejected'])->default('new');
            $table->enum('priority', ['high', 'med', 'low'])->default('med');
            $table->string('assigned_team', 80)->nullable();
            $table->date('submitted_on');
            $table->timestamps();
        });

        Schema::create('lei_application_audit_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lei_application_id')->constrained()->cascadeOnDelete();
            $table->timestamp('occurred_at');
            $table->string('description');
            $table->string('actor', 80)->default('System Admin');
            $table->boolean('is_highlight')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lei_application_audit_events');
        Schema::dropIfExists('lei_applications');
    }
};

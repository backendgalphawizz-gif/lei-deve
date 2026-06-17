<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lei_document_stat_cards', function (Blueprint $table) {
            $table->id();
            $table->string('stat_key', 32)->unique();
            $table->string('value', 32)->default('0');
            $table->string('label', 64);
            $table->string('icon_tone', 16)->default('blue');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lei_document_config', function (Blueprint $table) {
            $table->id();
            $table->string('version_label', 16)->default('v4.0.2');
            $table->string('ledger_node', 32)->default('12.0');
            $table->text('ledger_text')->nullable();
            $table->timestamps();
        });

        Schema::create('lei_documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_code', 16)->unique();
            $table->string('file_name');
            $table->string('file_type', 16)->default('pdf');
            $table->string('security_label', 32);
            $table->string('security_tone', 16);
            $table->string('status', 32);
            $table->string('status_tone', 16);
            $table->string('preview_url')->nullable();
            $table->text('decision_reason')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lei_document_audit_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lei_document_id')->constrained('lei_documents')->cascadeOnDelete();
            $table->string('title', 64);
            $table->text('description')->nullable();
            $table->string('event_label', 64);
            $table->string('indicator_tone', 16)->default('yellow');
            $table->boolean('is_in_progress')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lei_document_audit_events');
        Schema::dropIfExists('lei_documents');
        Schema::dropIfExists('lei_document_config');
        Schema::dropIfExists('lei_document_stat_cards');
    }
};

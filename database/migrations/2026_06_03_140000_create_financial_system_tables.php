<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lei_financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code', 32)->unique();
            $table->string('entity_name');
            $table->decimal('amount', 14, 2);
            $table->string('currency', 3)->default('INR');
            $table->enum('status', ['success', 'failed', 'pending'])->default('success');
            $table->string('gateway', 32)->default('stripe');
            $table->timestamp('transacted_at');
            $table->timestamps();
            $table->index(['status', 'gateway', 'transacted_at']);
        });

        Schema::create('lei_refund_requests', function (Blueprint $table) {
            $table->id();
            $table->string('refund_code', 32)->unique();
            $table->string('entity_name');
            $table->decimal('amount', 14, 2);
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('priority', ['normal', 'high'])->default('high');
            $table->decimal('avg_response_hours', 6, 2)->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lei_payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('gateway_key', 32)->unique();
            $table->unsignedSmallInteger('latency_ms')->default(100);
            $table->unsignedTinyInteger('health_percent')->default(90);
            $table->enum('status', ['healthy', 'warning', 'critical'])->default('healthy');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lei_tax_reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('filename');
            $table->string('file_size_display', 16);
            $table->string('file_type', 8)->default('pdf');
            $table->string('quarter_label', 16)->nullable();
            $table->timestamp('generated_at');
            $table->timestamps();
        });

        Schema::create('lei_financial_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('actor_name');
            $table->text('description');
            $table->timestamp('occurred_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lei_financial_audit_logs');
        Schema::dropIfExists('lei_tax_reports');
        Schema::dropIfExists('lei_payment_gateways');
        Schema::dropIfExists('lei_refund_requests');
        Schema::dropIfExists('lei_financial_transactions');
    }
};

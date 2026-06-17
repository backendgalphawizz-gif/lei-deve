<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('route_name')->nullable();
            $table->string('icon', 64)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('system_alerts', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['sla_breach', 'security', 'info', 'warning'])->default('info');
            $table->string('title')->nullable();
            $table->text('message');
            $table->string('region')->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->boolean('is_active')->default(true);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('registry_applications', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no', 64)->unique();
            $table->string('applicant_name');
            $table->string('entity_type', 64)->nullable();
            $table->enum('status', ['draft', 'submitted', 'pending', 'approved', 'rejected'])->default('submitted');
            $table->string('source', 32)->default('main_registry');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pending_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('registry_applications')->cascadeOnDelete();
            $table->enum('priority', ['normal', 'urgent'])->default('normal');
            $table->text('validation_note')->nullable();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['open', 'in_review', 'completed'])->default('open');
            $table->timestamps();
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_ref', 64)->unique();
            $table->foreignId('application_id')->nullable()->constrained('registry_applications')->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->enum('type', ['payment', 'refund'])->default('payment');
            $table->string('currency', 3)->default('INR');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('completed');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('service_health_checks', function (Blueprint $table) {
            $table->id();
            $table->string('service_name');
            $table->string('service_key', 64)->unique();
            $table->decimal('uptime_percent', 6, 2)->default(100);
            $table->enum('status', ['healthy', 'warning', 'critical'])->default('healthy');
            $table->unsignedTinyInteger('load_percent')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('application_trend_metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedInteger('main_registry_count')->default(0);
            $table->unsignedInteger('partner_api_count')->default(0);
            $table->timestamps();
            $table->unique(['year', 'month']);
        });

        Schema::create('dashboard_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('metric_key', 64)->unique();
            $table->string('label');
            $table->string('value_display');
            $table->decimal('value_numeric', 16, 2)->nullable();
            $table->string('trend_label')->nullable();
            $table->decimal('trend_percent', 8, 2)->nullable();
            $table->string('badge')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 64);
            $table->string('module', 64)->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('admin_notifications');
        Schema::dropIfExists('dashboard_snapshots');
        Schema::dropIfExists('application_trend_metrics');
        Schema::dropIfExists('service_health_checks');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('pending_approvals');
        Schema::dropIfExists('registry_applications');
        Schema::dropIfExists('system_alerts');
        Schema::dropIfExists('admin_menu_items');
    }
};

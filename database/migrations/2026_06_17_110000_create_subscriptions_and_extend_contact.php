<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lei_contact_submissions', function (Blueprint $table) {
            $table->text('admin_notes')->nullable()->after('status');
            $table->timestamp('read_at')->nullable()->after('admin_notes');
        });

        Schema::table('lei_pricing_plans', function (Blueprint $table) {
            $table->unsignedTinyInteger('duration_years')->default(1)->after('price');
        });

        Schema::create('lei_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 32)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pricing_plan_id')->nullable()->constrained('lei_pricing_plans')->nullOnDelete();
            $table->string('plan_name');
            $table->string('plan_section', 32)->default('registration');
            $table->decimal('amount', 10, 2);
            $table->string('currency_code', 8)->default('USD');
            $table->unsignedTinyInteger('duration_years')->default(1);
            $table->string('status', 16)->default('pending');
            $table->string('payment_status', 16)->default('pending');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['status', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lei_subscriptions');

        Schema::table('lei_pricing_plans', function (Blueprint $table) {
            $table->dropColumn('duration_years');
        });

        Schema::table('lei_contact_submissions', function (Blueprint $table) {
            $table->dropColumn(['admin_notes', 'read_at']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lei_nm_stat_cards', function (Blueprint $table) {
            $table->id();
            $table->string('stat_key', 32)->unique();
            $table->string('value', 32);
            $table->string('label', 64);
            $table->string('icon_tone', 16);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lei_nm_config', function (Blueprint $table) {
            $table->id();
            $table->string('broadcast_channel', 64)->default('System-wide In-app');
            $table->string('broadcast_audience', 64)->default('All Users');
            $table->text('broadcast_message')->nullable();
            $table->unsignedTinyInteger('otp_length')->default(6);
            $table->unsignedTinyInteger('otp_expiry_min')->default(5);
            $table->unsignedTinyInteger('otp_retry_limit')->default(3);
            $table->string('template_channel', 16)->default('email');
            $table->timestamps();
        });

        Schema::create('lei_nm_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64);
            $table->string('subtitle')->nullable();
            $table->string('category', 32);
            $table->string('channel', 16)->default('email');
            $table->string('status', 16)->default('active');
            $table->string('last_updated_label', 32)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lei_nm_triggers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64);
            $table->boolean('is_enabled')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lei_nm_placeholders', function (Blueprint $table) {
            $table->id();
            $table->string('placeholder_key', 32);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lei_nm_delivery_logs', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_type', 16);
            $table->string('recipient');
            $table->string('template_label', 64);
            $table->string('status', 16);
            $table->string('time_label', 32);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lei_nm_delivery_logs');
        Schema::dropIfExists('lei_nm_placeholders');
        Schema::dropIfExists('lei_nm_triggers');
        Schema::dropIfExists('lei_nm_templates');
        Schema::dropIfExists('lei_nm_config');
        Schema::dropIfExists('lei_nm_stat_cards');
    }
};

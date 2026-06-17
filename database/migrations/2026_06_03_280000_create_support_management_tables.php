<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lei_support_stat_cards', function (Blueprint $table) {
            $table->id();
            $table->string('stat_key', 32)->unique();
            $table->string('value', 32);
            $table->string('label', 64);
            $table->string('icon_tone', 16)->default('blue');
            $table->string('badge_text', 32)->nullable();
            $table->string('badge_tone', 16)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lei_support_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64);
            $table->string('ticket_count_label', 32);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lei_support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code', 16)->unique();
            $table->string('user_entity');
            $table->string('category', 32);
            $table->string('priority', 16);
            $table->string('priority_tone', 16);
            $table->string('status', 32);
            $table->string('status_tone', 16);
            $table->string('title');
            $table->boolean('is_urgent')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lei_support_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lei_support_ticket_id')->constrained('lei_support_tickets')->cascadeOnDelete();
            $table->string('sender_initials', 8);
            $table->string('sender_tone', 16)->default('client');
            $table->text('body');
            $table->string('time_label', 32)->nullable();
            $table->boolean('is_outgoing')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lei_support_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lei_support_ticket_id')->constrained('lei_support_tickets')->cascadeOnDelete();
            $table->string('author_initials', 8);
            $table->string('author_tone', 16)->default('admin');
            $table->text('body');
            $table->string('time_label', 32)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lei_support_notes');
        Schema::dropIfExists('lei_support_messages');
        Schema::dropIfExists('lei_support_tickets');
        Schema::dropIfExists('lei_support_categories');
        Schema::dropIfExists('lei_support_stat_cards');
    }
};

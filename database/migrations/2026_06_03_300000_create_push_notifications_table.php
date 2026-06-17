<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lei_push_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title', 128);
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->string('user_type', 32)->default('user');
            $table->unsignedInteger('notification_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lei_push_notifications');
    }
};

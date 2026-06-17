<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lei_countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('iso_alpha2', 2)->unique();
            $table->string('region', 64);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('dialing_code', 8);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lei_master_data_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 64)->unique();
            $table->json('value');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lei_master_data_settings');
        Schema::dropIfExists('lei_countries');
    }
};

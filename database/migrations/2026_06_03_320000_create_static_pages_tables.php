<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lei_static_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->string('slug', 120)->unique();
            $table->string('page_type', 32)->default('legal');
            $table->string('status', 16)->default('draft');
            $table->longText('content');
            $table->string('meta_title', 150)->nullable();
            $table->string('meta_description', 255)->nullable();
            $table->boolean('is_in_footer')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lei_static_pages');
    }
};

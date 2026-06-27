<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lei_site_sections', function (Blueprint $table) {
            $table->id();
            $table->string('page', 32);
            $table->string('section_key', 64);
            $table->string('title')->nullable();
            $table->text('subtitle')->nullable();
            $table->json('content')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['page', 'section_key']);
        });

        Schema::create('lei_faq_categories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug', 64)->unique();
            $table->string('icon', 32)->default('grid');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('lei_faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('lei_faq_categories')->nullOnDelete();
            $table->string('question');
            $table->text('answer');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->boolean('show_on_pricing')->default(false);
            $table->timestamps();
        });

        Schema::create('lei_pricing_plans', function (Blueprint $table) {
            $table->id();
            $table->string('section', 32);
            $table->string('label', 64)->nullable();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->string('price_suffix', 32)->default('/ entity');
            $table->string('savings_label')->nullable();
            $table->json('features')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('button_text', 64)->default('Select Plan');
            $table->string('button_style', 16)->default('outline');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('lei_pricing_matrix_rows', function (Blueprint $table) {
            $table->id();
            $table->string('component');
            $table->string('standard_value');
            $table->string('bundle_value');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('lei_contact_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email');
            $table->string('subject');
            $table->text('message');
            $table->string('status', 16)->default('new');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('lei_applicant_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code', 8);
            $table->string('purpose', 32)->default('registration');
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('country_of_incorporation', 64)->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('country_of_incorporation');
        });

        Schema::dropIfExists('lei_applicant_otps');
        Schema::dropIfExists('lei_contact_submissions');
        Schema::dropIfExists('lei_pricing_matrix_rows');
        Schema::dropIfExists('lei_pricing_plans');
        Schema::dropIfExists('lei_faqs');
        Schema::dropIfExists('lei_faq_categories');
        Schema::dropIfExists('lei_site_sections');
    }
};

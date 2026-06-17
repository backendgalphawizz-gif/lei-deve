<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lei_registry_templates', function (Blueprint $table) {
            $table->id();
            $table->string('document_name')->default('Certificate of Incorporation');
            $table->string('primary_category', 64)->default('legal_entity_proof');
            $table->string('sub_category', 64)->default('general_corporate');
            $table->boolean('mandatory_flag')->default(true);
            $table->boolean('ocr_verification')->default(false);
            $table->json('file_formats')->nullable();
            $table->unsignedSmallInteger('max_file_size_mb')->default(25);
            $table->string('versioning_mode', 32)->default('audit_trail');
            $table->string('approval_flow', 64)->default('standard_review_2');
            $table->string('security_tier', 32)->default('standard');
            $table->string('last_modified_by', 64)->default('Super_Admin_01');
            $table->timestamp('last_modified_at')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lei_registry_templates');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lei_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lei_application_id')->constrained()->cascadeOnDelete();
            $table->string('status', 32)->default('pending_unsigned');
            // pending_unsigned | unsigned | pending_ca | signed | rejected
            $table->string('serial_number', 64)->unique();
            $table->string('signature_algorithm', 64)->default('sha256WithRSAEncryption');
            $table->string('issuer_dn', 500)->nullable();
            $table->string('subject_dn', 500)->nullable();
            $table->string('lei_oid', 40)->default('1.3.6.1.4.1.52266.1');
            $table->string('role_oid', 40)->default('1.3.6.1.4.1.52266.2');
            $table->string('certificate_role', 80)->nullable();
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->string('unsigned_pdf_path')->nullable();
            $table->string('signed_pdf_path')->nullable();
            $table->string('x509_pem_path')->nullable();
            $table->string('signature_hash', 128)->nullable();
            $table->foreignId('signed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('signed_at')->nullable();
            $table->text('ca_notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lei_certificates');
    }
};

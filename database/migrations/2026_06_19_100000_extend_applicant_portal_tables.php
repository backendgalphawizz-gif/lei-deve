<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lei_applications', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('workflow_type', 32)->nullable()->after('issuance_type');
            $table->unsignedTinyInteger('workflow_step')->default(1)->after('workflow_type');
            $table->json('draft_data')->nullable()->after('workflow_step');
            $table->string('lei_number', 20)->nullable()->after('draft_data');
            $table->date('expiry_date')->nullable()->after('lei_number');
            $table->string('application_type', 32)->default('new_registration')->after('expiry_date');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE lei_applications MODIFY COLUMN status ENUM('draft','new','pending','under_review','clarification','approved','rejected') NOT NULL DEFAULT 'new'");
            DB::statement('ALTER TABLE lei_applications MODIFY COLUMN submitted_on DATE NULL');
        } else {
            Schema::table('lei_applications', function (Blueprint $table) {
                $table->date('submitted_on')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('lei_applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn([
                'workflow_type',
                'workflow_step',
                'draft_data',
                'lei_number',
                'expiry_date',
                'application_type',
            ]);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE lei_applications MODIFY COLUMN status ENUM('new','pending','under_review','clarification','approved','rejected') NOT NULL DEFAULT 'new'");
            DB::statement('ALTER TABLE lei_applications MODIFY COLUMN submitted_on DATE NOT NULL');
        }
    }
};

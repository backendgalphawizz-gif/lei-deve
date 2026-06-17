<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lei_push_notifications', function (Blueprint $table) {
            $table->string('file_name', 128)->nullable()->after('description');
            $table->string('file_type', 16)->default('pdf')->after('file_name');
        });
    }

    public function down(): void
    {
        Schema::table('lei_push_notifications', function (Blueprint $table) {
            $table->dropColumn(['file_name', 'file_type']);
        });
    }
};

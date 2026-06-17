<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lei_support_tickets', function (Blueprint $table) {
            $table->string('contact_email', 128)->nullable()->after('user_entity');
        });

        Schema::table('lei_support_messages', function (Blueprint $table) {
            $table->string('sender_name', 64)->nullable()->after('sender_initials');
            $table->string('sender_role', 64)->nullable()->after('sender_name');
        });

        Schema::table('lei_support_notes', function (Blueprint $table) {
            $table->string('author_name', 64)->nullable()->after('author_initials');
        });
    }

    public function down(): void
    {
        Schema::table('lei_support_notes', function (Blueprint $table) {
            $table->dropColumn('author_name');
        });
        Schema::table('lei_support_messages', function (Blueprint $table) {
            $table->dropColumn(['sender_name', 'sender_role']);
        });
        Schema::table('lei_support_tickets', function (Blueprint $table) {
            $table->dropColumn('contact_email');
        });
    }
};

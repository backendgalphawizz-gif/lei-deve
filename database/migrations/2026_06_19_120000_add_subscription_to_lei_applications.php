<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lei_applications', function (Blueprint $table) {
            $table->foreignId('lei_subscription_id')
                ->nullable()
                ->after('user_id')
                ->constrained('lei_subscriptions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lei_applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lei_subscription_id');
        });
    }
};

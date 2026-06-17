<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lei_business_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name', 150);
            $table->string('legal_name', 150)->nullable();
            $table->string('tagline', 255)->nullable();
            $table->string('portal_title', 150)->nullable();
            $table->string('registry_authority', 150)->nullable();
            $table->string('logo_path', 255)->nullable();
            $table->string('favicon_path', 255)->nullable();
            $table->string('sidebar_icon_path', 255)->nullable();
            $table->string('primary_color', 7)->default('#0b162c');
            $table->string('accent_color', 7)->default('#c9a227');
            $table->string('sidebar_color', 7)->default('#0b162c');
            $table->string('support_email', 150)->nullable();
            $table->string('support_phone', 40)->nullable();
            $table->string('address_line', 255)->nullable();
            $table->string('city', 80)->nullable();
            $table->string('state', 80)->nullable();
            $table->string('country', 80)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('website_url', 255)->nullable();
            $table->string('linkedin_url', 255)->nullable();
            $table->string('twitter_url', 255)->nullable();
            $table->string('copyright_text', 255)->nullable();
            $table->string('timezone', 64)->default('Asia/Kolkata');
            $table->string('locale', 16)->default('en');
            $table->string('date_format', 32)->default('M j, Y');
            $table->string('currency_code', 8)->default('INR');
            $table->string('currency_symbol', 8)->default('₹');
            $table->text('meta_description')->nullable();
            $table->boolean('show_maintenance_banner')->default(false);
            $table->string('maintenance_message', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lei_business_settings');
    }
};

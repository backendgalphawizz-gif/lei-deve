<?php

use App\Models\LeiPricingPlan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('lei_business_settings')
            ->where(function ($query) {
                $query->where('currency_code', 'USD')
                    ->orWhere('currency_symbol', '$');
            })
            ->update([
                'currency_code' => 'INR',
                'currency_symbol' => '₹',
            ]);

        DB::table('lei_subscriptions')
            ->where('currency_code', 'USD')
            ->update(['currency_code' => 'INR']);

        DB::table('lei_pricing_plans')
            ->where('savings_label', 'like', '%$%')
            ->update([
                'savings_label' => DB::raw("REPLACE(savings_label, '$', '₹')"),
            ]);
    }

    public function down(): void
    {
        // No rollback — currency is environment-specific.
    }
};

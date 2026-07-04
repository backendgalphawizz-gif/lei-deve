<?php

use App\Models\LeiPricingPlan;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $plans = [
            '1 Year Plan' => ['price' => 4350, 'duration_years' => 1, 'label' => null, 'is_featured' => false, 'price_suffix' => '/ year'],
            '3 Year Plan' => ['price' => 11970, 'duration_years' => 3, 'label' => 'Most popular', 'is_featured' => true, 'price_suffix' => '/ year', 'savings_label' => 'SAVE ₹1080 vs 1-year plan'],
            '5 Year Plan' => ['price' => 16900, 'duration_years' => 5, 'label' => null, 'is_featured' => false, 'price_suffix' => '/ year', 'savings_label' => 'SAVE ₹4850 vs 1-year plan'],
        ];

        foreach ($plans as $name => $data) {
            LeiPricingPlan::query()
                ->where('section', 'registration')
                ->where('name', $name)
                ->update($data);
        }
    }

    public function down(): void
    {
        // No rollback — prices are content configuration.
    }
};

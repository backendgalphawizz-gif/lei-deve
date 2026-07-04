<?php

namespace App\Models;

use App\Support\CurrencyFormatter;
use Illuminate\Database\Eloquent\Model;

class LeiPricingPlan extends Model
{
    protected $table = 'lei_pricing_plans';

    protected $fillable = [
        'section',
        'label',
        'name',
        'price',
        'duration_years',
        'price_suffix',
        'savings_label',
        'features',
        'is_featured',
        'button_text',
        'button_style',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(LeiSubscription::class, 'pricing_plan_id');
    }

    /**
     * Total one-time checkout amount stored in admin.
     */
    public function totalPrice(): float
    {
        return (float) $this->price;
    }

    /**
     * Effective per-year rate (total ÷ duration).
     */
    public function yearlyPrice(): float
    {
        $years = max(1, (int) ($this->duration_years ?: 1));

        return round($this->totalPrice() / $years, 2);
    }

    public function formattedPrice(): string
    {
        return $this->formattedTotalPrice();
    }

    public function formattedYearlyPrice(): string
    {
        return CurrencyFormatter::format($this->yearlyPrice(), 0);
    }

    public function formattedTotalPrice(): string
    {
        return CurrencyFormatter::format($this->totalPrice(), 0);
    }

    public function durationLabel(): string
    {
        $years = (int) ($this->duration_years ?? 1);

        return $years === 1 ? '1 Year' : $years . ' Years';
    }

    public function yearLabel(): string
    {
        $years = max(1, (int) ($this->duration_years ?: 1));

        return $years === 1 ? '1 year' : $years . ' years';
    }
}

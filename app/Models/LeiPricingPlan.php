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

    public function formattedPrice(): string
    {
        return CurrencyFormatter::format((float) $this->price, 0);
    }

    public function durationLabel(): string
    {
        $years = (int) ($this->duration_years ?? 1);

        return $years === 1 ? '1 Year' : $years . ' Years';
    }
}

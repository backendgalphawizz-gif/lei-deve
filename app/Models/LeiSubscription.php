<?php

namespace App\Models;

use App\Support\CurrencyFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LeiSubscription extends Model
{
    protected $table = 'lei_subscriptions';

    protected $fillable = [
        'reference',
        'user_id',
        'pricing_plan_id',
        'plan_name',
        'plan_section',
        'amount',
        'currency_code',
        'duration_years',
        'status',
        'payment_status',
        'starts_at',
        'expires_at',
        'admin_notes',
        'ip_address',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public static function statuses(): array
    {
        return [
            'pending' => 'Pending',
            'active' => 'Active',
            'expired' => 'Expired',
            'cancelled' => 'Cancelled',
        ];
    }

    public static function paymentStatuses(): array
    {
        return [
            'pending' => 'Pending',
            'paid' => 'Paid',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
        ];
    }

    public static function generateReference(): string
    {
        do {
            $ref = 'LEI-SUB-' . strtoupper(Str::random(8));
        } while (static::query()->where('reference', $ref)->exists());

        return $ref;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pricingPlan(): BelongsTo
    {
        return $this->belongsTo(LeiPricingPlan::class, 'pricing_plan_id');
    }

    public function applications()
    {
        return $this->hasMany(LeiApplication::class, 'lei_subscription_id');
    }

    public function formattedAmount(): string
    {
        return CurrencyFormatter::format((float) $this->amount, 0);
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? ucfirst($this->status);
    }

    public function paymentStatusLabel(): string
    {
        return self::paymentStatuses()[$this->payment_status] ?? ucfirst($this->payment_status);
    }
}

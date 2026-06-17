<?php

namespace App\Models;

use App\Support\CurrencyFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeiRefundRequest extends Model
{
    protected $fillable = [
        'refund_code',
        'entity_name',
        'amount',
        'reason',
        'status',
        'priority',
        'avg_response_hours',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'avg_response_hours' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getFormattedAmountAttribute(): string
    {
        return CurrencyFormatter::format((float) $this->amount);
    }
}

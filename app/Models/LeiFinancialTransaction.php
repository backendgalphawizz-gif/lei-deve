<?php

namespace App\Models;

use App\Support\CurrencyFormatter;
use Illuminate\Database\Eloquent\Model;

class LeiFinancialTransaction extends Model
{
    protected $fillable = [
        'transaction_code',
        'entity_name',
        'amount',
        'currency',
        'status',
        'gateway',
        'transacted_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transacted_at' => 'datetime',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return strtoupper($this->status);
    }

    public function getFormattedAmountAttribute(): string
    {
        return CurrencyFormatter::format((float) $this->amount);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiFinancialAuditLog extends Model
{
    protected $fillable = [
        'actor_name',
        'description',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
        ];
    }
}

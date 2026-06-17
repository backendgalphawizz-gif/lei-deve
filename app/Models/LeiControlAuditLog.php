<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiControlAuditLog extends Model
{
    protected $fillable = [
        'actor_name',
        'action_type',
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

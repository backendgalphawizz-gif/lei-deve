<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiSecurityPolicy extends Model
{
    protected $fillable = [
        'policy_key',
        'title',
        'description',
        'is_enabled',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }
}

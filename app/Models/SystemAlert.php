<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemAlert extends Model
{
    protected $fillable = [
        'type', 'title', 'message', 'region', 'severity', 'is_active', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->whereNull('resolved_at');
    }
}

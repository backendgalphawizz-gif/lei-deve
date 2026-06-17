<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiGovernanceVariable extends Model
{
    protected $fillable = [
        'variable_name',
        'value_display',
        'risk_level',
        'sort_order',
        'last_changed_at',
    ];

    protected function casts(): array
    {
        return [
            'last_changed_at' => 'datetime',
        ];
    }

    public function getRiskLabelAttribute(): string
    {
        return strtoupper($this->risk_level);
    }
}

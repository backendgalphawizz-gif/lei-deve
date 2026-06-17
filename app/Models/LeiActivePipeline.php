<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiActivePipeline extends Model
{
    protected $fillable = [
        'build_number', 'target_environment', 'steps',
        'progress_percent', 'progress_label', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'steps' => 'array',
            'is_active' => 'boolean',
        ];
    }
}

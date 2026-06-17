<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardSnapshot extends Model
{
    protected $fillable = [
        'metric_key', 'label', 'value_display', 'value_numeric',
        'trend_label', 'trend_percent', 'badge', 'meta',
    ];

    protected function casts(): array
    {
        return ['meta' => 'array'];
    }
}

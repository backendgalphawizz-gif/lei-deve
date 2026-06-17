<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationTrendMetric extends Model
{
    protected $fillable = [
        'year', 'month', 'main_registry_count', 'partner_api_count',
    ];
}

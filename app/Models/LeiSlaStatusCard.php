<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiSlaStatusCard extends Model
{
    protected $fillable = [
        'title', 'status_label', 'status_tone', 'metric_label',
        'metric_value', 'border_tone', 'sort_order',
    ];
}

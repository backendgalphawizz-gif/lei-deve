<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiReportsStatCard extends Model
{
    protected $fillable = [
        'stat_key', 'value', 'label', 'description', 'icon_tone',
        'trend_text', 'trend_tone', 'sort_order',
    ];
}

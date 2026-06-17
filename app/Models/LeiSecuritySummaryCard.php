<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiSecuritySummaryCard extends Model
{
    protected $fillable = [
        'title', 'border_tone', 'icon_tone', 'line_primary', 'line_secondary', 'sort_order',
    ];
}

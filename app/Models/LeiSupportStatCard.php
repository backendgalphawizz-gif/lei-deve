<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiSupportStatCard extends Model
{
    protected $fillable = ['stat_key', 'value', 'label', 'icon_tone', 'badge_text', 'badge_tone', 'sort_order'];
}

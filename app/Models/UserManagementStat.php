<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserManagementStat extends Model
{
    protected $fillable = ['metric_key', 'label', 'value_display', 'badge', 'badge_tone'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiSecurityThreatEvent extends Model
{
    protected $fillable = ['level', 'level_tone', 'title', 'meta', 'time_label', 'sort_order'];
}

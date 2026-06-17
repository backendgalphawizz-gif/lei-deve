<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiSecurityIpRule extends Model
{
    protected $fillable = ['status', 'status_tone', 'ip_range', 'location', 'context', 'sort_order'];
}

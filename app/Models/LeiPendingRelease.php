<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiPendingRelease extends Model
{
    protected $fillable = [
        'title', 'badge', 'description', 'approval_status', 'sort_order',
    ];
}

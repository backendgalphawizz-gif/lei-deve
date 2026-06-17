<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiNmTrigger extends Model
{
    protected $table = 'lei_nm_triggers';

    protected $fillable = ['name', 'is_enabled', 'sort_order'];

    protected $casts = ['is_enabled' => 'boolean'];
}

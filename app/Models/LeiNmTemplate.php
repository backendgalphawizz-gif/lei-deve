<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiNmTemplate extends Model
{
    protected $table = 'lei_nm_templates';

    protected $fillable = [
        'name', 'subtitle', 'category', 'channel', 'status', 'last_updated_label', 'sort_order',
    ];
}

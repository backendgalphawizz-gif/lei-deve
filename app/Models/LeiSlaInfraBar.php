<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiSlaInfraBar extends Model
{
    protected $fillable = ['height_percent', 'is_alert', 'sort_order'];

    protected function casts(): array
    {
        return ['is_alert' => 'boolean'];
    }
}

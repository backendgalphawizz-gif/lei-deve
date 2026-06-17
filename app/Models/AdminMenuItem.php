<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminMenuItem extends Model
{
    protected $fillable = ['label', 'route_name', 'icon', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiCountry extends Model
{
    protected $fillable = [
        'name', 'iso_alpha2', 'region', 'status', 'dialing_code', 'sort_order',
    ];

    public function getStatusLabelAttribute(): string
    {
        return strtoupper($this->status);
    }
}

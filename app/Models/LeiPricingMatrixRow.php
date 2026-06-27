<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiPricingMatrixRow extends Model
{
    protected $table = 'lei_pricing_matrix_rows';

    protected $fillable = [
        'component',
        'standard_value',
        'bundle_value',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

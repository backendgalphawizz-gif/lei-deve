<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiReportsChartPoint extends Model
{
    protected $fillable = ['day_label', 'current_value', 'previous_value', 'sort_order'];
}

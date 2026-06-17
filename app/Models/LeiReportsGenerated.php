<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiReportsGenerated extends Model
{
    protected $table = 'lei_reports_generated';

    protected $fillable = [
        'report_name', 'parameters', 'generated_date', 'status', 'status_tone', 'sort_order',
    ];
}

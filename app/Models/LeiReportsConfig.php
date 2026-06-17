<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiReportsConfig extends Model
{
    protected $table = 'lei_reports_config';

    protected $fillable = [
        'period_label', 'active_tab', 'scheduled_enabled', 'next_scheduled',
        'builder_date_range', 'builder_category', 'builder_entity',
        'sla_percent', 'critical_incidents', 'warning_alerts', 'resolution_time',
    ];

    protected $casts = [
        'scheduled_enabled' => 'boolean',
    ];
}

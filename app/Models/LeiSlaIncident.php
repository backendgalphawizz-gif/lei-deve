<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiSlaIncident extends Model
{
    protected $fillable = [
        'severity', 'severity_tone', 'target_node', 'incident_type',
        'time_active', 'action_label', 'action_key', 'sort_order',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiSecurityIncident extends Model
{
    protected $fillable = [
        'incident_id', 'title', 'subtitle', 'severity', 'severity_tone',
        'last_event', 'current_status', 'status_tone', 'assignee_name',
        'assignee_initials', 'action_label', 'action_style', 'action_key', 'sort_order', 'is_cleared',
    ];

    protected function casts(): array
    {
        return ['is_cleared' => 'boolean'];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiBackupMetric extends Model
{
    protected $fillable = [
        'last_backup_time', 'last_backup_size', 'integrity_label',
        'dr_nodes', 'dr_status', 'rpo_minutes', 'rpo_percent',
        'rto_hours', 'rto_sla', 'rto_badge', 'primary_site', 'is_synced',
        'latency_ms', 'frequency', 'retention', 'next_run_mins', 'next_run_secs',
        'site_health_blocks',
    ];

    protected function casts(): array
    {
        return ['is_synced' => 'boolean'];
    }
}

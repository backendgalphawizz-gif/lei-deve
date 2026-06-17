<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiSlaConfig extends Model
{
    protected $table = 'lei_sla_config';

    protected $fillable = [
        'cpu_threshold', 'ram_threshold', 'disk_threshold',
        'backup_last', 'backup_next', 'api_latency', 'api_err_rate',
        'api_progress', 'db_pools', 'db_peak', 'db_segments',
    ];

    protected function casts(): array
    {
        return ['db_segments' => 'array'];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceHealthCheck extends Model
{
    protected $fillable = [
        'service_name', 'service_key', 'uptime_percent', 'status', 'load_percent', 'sort_order',
    ];
}

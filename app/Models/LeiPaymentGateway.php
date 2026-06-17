<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiPaymentGateway extends Model
{
    protected $fillable = [
        'name',
        'gateway_key',
        'latency_ms',
        'health_percent',
        'status',
        'sort_order',
    ];
}

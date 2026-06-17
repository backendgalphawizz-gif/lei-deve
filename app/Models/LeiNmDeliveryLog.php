<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiNmDeliveryLog extends Model
{
    protected $table = 'lei_nm_delivery_logs';

    protected $fillable = [
        'delivery_type', 'recipient', 'template_label', 'status', 'time_label', 'sort_order',
    ];
}

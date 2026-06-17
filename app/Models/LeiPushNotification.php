<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiPushNotification extends Model
{
    protected $fillable = [
        'title', 'description', 'file_name', 'file_type', 'image_url', 'user_type',
        'notification_count', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'notification_count' => 'integer',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiSecurityAccessPolicy extends Model
{
    protected $fillable = [
        'mfa_enabled', 'mfa_adoption', 'session_timeout', 'max_login_attempts',
        'failed_login_count', 'critical_count', 'warning_count', 'overlay_status', 'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'mfa_enabled' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }
}

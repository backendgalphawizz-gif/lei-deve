<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiAuditConfig extends Model
{
    protected $table = 'lei_audit_config';

    protected $fillable = ['uptime_percent', 'sync_ms', 'date_range_label'];
}

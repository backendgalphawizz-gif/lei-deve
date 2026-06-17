<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiRegistryEnvironment extends Model
{
    protected $fillable = [
        'env_key', 'label', 'uptime_display', 'status_tone', 'version',
        'deployed_meta', 'footer_label', 'footer_value', 'footer_tone', 'sort_order',
    ];
}

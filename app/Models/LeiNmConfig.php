<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiNmConfig extends Model
{
    protected $table = 'lei_nm_config';

    protected $fillable = [
        'broadcast_channel', 'broadcast_audience', 'broadcast_message',
        'otp_length', 'otp_expiry_min', 'otp_retry_limit', 'template_channel',
    ];
}

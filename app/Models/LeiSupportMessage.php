<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeiSupportMessage extends Model
{
    protected $fillable = [
        'lei_support_ticket_id', 'sender_initials', 'sender_name', 'sender_role', 'sender_tone', 'body',
        'time_label', 'is_outgoing', 'sort_order',
    ];

    protected $casts = ['is_outgoing' => 'boolean'];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(LeiSupportTicket::class, 'lei_support_ticket_id');
    }
}

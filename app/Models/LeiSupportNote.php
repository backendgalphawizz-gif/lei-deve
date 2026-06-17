<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeiSupportNote extends Model
{
    protected $fillable = [
        'lei_support_ticket_id', 'author_initials', 'author_name', 'author_tone', 'body', 'time_label', 'sort_order',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(LeiSupportTicket::class, 'lei_support_ticket_id');
    }
}

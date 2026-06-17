<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeiSupportTicket extends Model
{
    protected $fillable = [
        'ticket_code', 'user_entity', 'contact_email', 'category', 'priority', 'priority_tone',
        'status', 'status_tone', 'title', 'is_urgent', 'sort_order',
        'closed_at', 'assigned_at',
    ];

    protected $casts = [
        'is_urgent' => 'boolean',
        'closed_at' => 'datetime',
        'assigned_at' => 'datetime',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(LeiSupportMessage::class)->orderBy('sort_order');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(LeiSupportNote::class)->orderBy('sort_order');
    }
}

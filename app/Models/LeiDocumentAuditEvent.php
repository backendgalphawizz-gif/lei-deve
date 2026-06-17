<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeiDocumentAuditEvent extends Model
{
    protected $fillable = [
        'lei_document_id', 'title', 'description', 'event_label',
        'indicator_tone', 'is_in_progress', 'sort_order',
    ];

    protected $casts = ['is_in_progress' => 'boolean'];

    public function document(): BelongsTo
    {
        return $this->belongsTo(LeiDocument::class, 'lei_document_id');
    }
}

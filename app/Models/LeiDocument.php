<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeiDocument extends Model
{
    protected $fillable = [
        'document_code', 'file_name', 'file_type', 'security_label', 'security_tone',
        'status', 'status_tone', 'preview_url', 'decision_reason', 'sort_order',
        'verified_at', 'rejected_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function auditEvents(): HasMany
    {
        return $this->hasMany(LeiDocumentAuditEvent::class)->orderBy('sort_order');
    }
}

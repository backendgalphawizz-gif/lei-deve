<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiApplication extends Model
{
    protected $fillable = [
        'application_code',
        'entity_name',
        'country',
        'issuance_type',
        'status',
        'priority',
        'assigned_team',
        'submitted_on',
    ];

    protected function casts(): array
    {
        return [
            'submitted_on' => 'date',
        ];
    }

    public function auditEvents()
    {
        return $this->hasMany(LeiApplicationAuditEvent::class)->orderByDesc('occurred_at');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'new' => 'NEW',
            'pending' => 'PENDING',
            'under_review' => 'REVIEW',
            'clarification' => 'CLARIFY',
            'approved' => 'APPROVED',
            'rejected' => 'REJECTED',
            default => strtoupper($this->status),
        };
    }

    public function getStatusToneAttribute(): string
    {
        return match ($this->status) {
            'new' => 'blue',
            'pending' => 'gray',
            'under_review' => 'orange',
            'clarification' => 'red',
            'approved' => 'green',
            'rejected' => 'red',
            default => 'gray',
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiApplication extends Model
{
    protected $fillable = [
        'application_code',
        'user_id',
        'lei_subscription_id',
        'entity_name',
        'country',
        'issuance_type',
        'workflow_type',
        'workflow_step',
        'draft_data',
        'lei_number',
        'expiry_date',
        'application_type',
        'status',
        'priority',
        'assigned_team',
        'submitted_on',
    ];

    protected function casts(): array
    {
        return [
            'submitted_on' => 'date',
            'expiry_date' => 'date',
            'draft_data' => 'array',
            'workflow_step' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(LeiSubscription::class, 'lei_subscription_id');
    }

    public function auditEvents()
    {
        return $this->hasMany(LeiApplicationAuditEvent::class)->orderByDesc('occurred_at');
    }

    public function certificate()
    {
        return $this->hasOne(LeiCertificate::class, 'lei_application_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'DRAFT',
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
            'draft' => 'gray',
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

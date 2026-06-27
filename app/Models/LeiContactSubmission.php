<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiContactSubmission extends Model
{
    protected $table = 'lei_contact_submissions';

    protected $fillable = [
        'full_name',
        'email',
        'subject',
        'message',
        'status',
        'admin_notes',
        'read_at',
        'ip_address',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public static function statuses(): array
    {
        return [
            'new' => 'New',
            'read' => 'Read',
            'replied' => 'Replied',
            'closed' => 'Closed',
        ];
    }

    public static function subjectOptions(): array
    {
        return [
            'Enterprise Bulk Registration' => 'Enterprise Bulk Registration',
            'General Inquiry' => 'General Inquiry',
            'Technical Support' => 'Technical Support',
            'Partnership' => 'Partnership',
        ];
    }
}

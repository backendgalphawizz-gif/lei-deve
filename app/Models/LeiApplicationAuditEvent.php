<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiApplicationAuditEvent extends Model
{
    protected $fillable = [
        'lei_application_id',
        'occurred_at',
        'description',
        'actor',
        'is_highlight',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'is_highlight' => 'boolean',
        ];
    }

    public function application()
    {
        return $this->belongsTo(LeiApplication::class, 'lei_application_id');
    }
}

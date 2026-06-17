<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiAuditLogEntry extends Model
{
    protected $fillable = [
        'logged_at',
        'category_level',
        'category_domain',
        'category_tone',
        'actor_name',
        'actor_ip',
        'action_performed',
        'status_label',
        'status_tone',
        'action_type',
        'changes_detail',
        'sort_order',
    ];

    public function getCategoryPillAttribute(): string
    {
        return "{$this->category_level}: {$this->category_domain}";
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeiWorkflowTemplate extends Model
{
    protected $fillable = [
        'name', 'module', 'initial_state', 'sla_hours',
        'total_nodes_label', 'escalation_depth', 'automation_tier',
        'last_synced_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function states(): HasMany
    {
        return $this->hasMany(LeiWorkflowState::class, 'template_id')->orderBy('sort_order');
    }

    public function getModuleLabelAttribute(): string
    {
        return match ($this->module) {
            'registry_services' => 'Registry Services',
            'payments' => 'Payments',
            'master_data' => 'Master Data',
            default => ucwords(str_replace('_', ' ', $this->module)),
        };
    }
}

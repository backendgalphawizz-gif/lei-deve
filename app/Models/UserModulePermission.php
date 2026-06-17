<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserModulePermission extends Model
{
    protected $fillable = [
        'user_id', 'system_module_id', 'can_read', 'can_write', 'can_delete',
    ];

    protected function casts(): array
    {
        return [
            'can_read' => 'boolean',
            'can_write' => 'boolean',
            'can_delete' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(SystemModule::class, 'system_module_id');
    }
}

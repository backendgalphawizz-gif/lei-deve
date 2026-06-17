<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiBackupSnapshot extends Model
{
    protected $fillable = [
        'snapshot_id', 'captured_at', 'type', 'size_display',
        'integrity_status', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['captured_at' => 'datetime'];
    }
}

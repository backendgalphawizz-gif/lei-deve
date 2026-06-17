<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiTaxReport extends Model
{
    protected $fillable = [
        'title',
        'filename',
        'file_size_display',
        'file_type',
        'quarter_label',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
        ];
    }
}

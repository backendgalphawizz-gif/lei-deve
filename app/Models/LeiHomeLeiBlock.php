<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiHomeLeiBlock extends Model
{
    protected $fillable = [
        'block_type',
        'title',
        'subtitle',
        'body',
        'category_number',
        'items',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'items' => 'array',
        'is_active' => 'boolean',
        'category_number' => 'integer',
    ];

    public static function blockTypes(): array
    {
        return [
            'intro' => 'Introduction',
            'category' => 'Entity Category',
            'reasons' => 'Common Reasons',
            'benefits' => 'Benefits',
            'mandatory' => 'Mandatory Notice',
        ];
    }

    public static function published(): \Illuminate\Database\Eloquent\Builder
    {
        return static::query()->where('is_active', true)->orderBy('sort_order');
    }

    public function typeLabel(): string
    {
        return static::blockTypes()[$this->block_type] ?? ucfirst($this->block_type);
    }
}

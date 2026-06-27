<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiSiteSection extends Model
{
    protected $table = 'lei_site_sections';

    protected $fillable = [
        'page',
        'section_key',
        'title',
        'subtitle',
        'content',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'content' => 'array',
        'is_active' => 'boolean',
    ];

    public static function forPage(string $page): \Illuminate\Support\Collection
    {
        return static::query()
            ->where('page', $page)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('section_key');
    }

    public static function getContent(string $page, string $key, array $default = []): array
    {
        $section = static::query()
            ->where('page', $page)
            ->where('section_key', $key)
            ->where('is_active', true)
            ->first();

        return array_merge($default, $section?->content ?? []);
    }
}

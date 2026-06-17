<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LeiStaticPage extends Model
{
    protected $table = 'lei_static_pages';

    protected $fillable = [
        'title',
        'slug',
        'page_type',
        'status',
        'content',
        'meta_title',
        'meta_description',
        'is_in_footer',
        'sort_order',
        'published_at',
    ];

    protected $casts = [
        'is_in_footer' => 'boolean',
        'published_at' => 'datetime',
    ];

    public static function pageTypes(): array
    {
        return [
            'legal' => 'Legal & Compliance',
            'help' => 'Help & Support',
            'marketing' => 'Marketing',
            'system' => 'System',
        ];
    }

    public static function statuses(): array
    {
        return [
            'draft' => 'Draft',
            'published' => 'Published',
            'archived' => 'Archived',
        ];
    }

    public function getPageTypeLabelAttribute(): string
    {
        return self::pageTypes()[$this->page_type] ?? ucfirst($this->page_type);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statuses()[$this->status] ?? ucfirst($this->status);
    }

    public function getUpdatedLabelAttribute(): string
    {
        return $this->updated_at?->format('M j, Y') ?? '—';
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        if ($base === '') {
            $base = 'page';
        }

        $slug = $base;
        $n = 1;

        while (static::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base . '-' . $n;
            $n++;
        }

        return $slug;
    }
}

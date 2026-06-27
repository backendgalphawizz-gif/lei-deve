<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeiFaqCategory extends Model
{
    protected $table = 'lei_faq_categories';

    protected $fillable = [
        'title',
        'slug',
        'icon',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function faqs(): HasMany
    {
        return $this->hasMany(LeiFaq::class, 'category_id')->orderBy('sort_order');
    }

    public function publishedFaqs(): HasMany
    {
        return $this->faqs()->where('is_published', true);
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = \Illuminate\Support\Str::slug($title) ?: 'category';
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

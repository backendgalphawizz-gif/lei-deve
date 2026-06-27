<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeiFaq extends Model
{
    protected $table = 'lei_faqs';

    protected $fillable = [
        'category_id',
        'question',
        'answer',
        'sort_order',
        'is_published',
        'show_on_pricing',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'show_on_pricing' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(LeiFaqCategory::class, 'category_id');
    }
}

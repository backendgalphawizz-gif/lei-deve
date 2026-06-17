<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeiWorkflowState extends Model
{
    protected $fillable = [
        'template_id', 'rule_label', 'title', 'description',
        'accent', 'rule_type', 'sort_order',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(LeiWorkflowTemplate::class, 'template_id');
    }
}

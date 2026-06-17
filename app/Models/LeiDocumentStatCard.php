<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiDocumentStatCard extends Model
{
    protected $fillable = ['stat_key', 'value', 'label', 'icon_tone', 'sort_order'];
}

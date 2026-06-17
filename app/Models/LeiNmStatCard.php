<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiNmStatCard extends Model
{
    protected $table = 'lei_nm_stat_cards';

    protected $fillable = ['stat_key', 'value', 'label', 'icon_tone', 'sort_order'];
}

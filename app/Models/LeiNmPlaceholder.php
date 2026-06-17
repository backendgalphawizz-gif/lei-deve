<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiNmPlaceholder extends Model
{
    protected $table = 'lei_nm_placeholders';

    protected $fillable = ['placeholder_key', 'sort_order'];
}

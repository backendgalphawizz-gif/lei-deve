<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiSupportCategory extends Model
{
    protected $fillable = ['name', 'ticket_count_label', 'sort_order'];
}

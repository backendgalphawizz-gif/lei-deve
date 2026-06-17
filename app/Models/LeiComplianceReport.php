<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiComplianceReport extends Model
{
    protected $fillable = ['title', 'file_meta', 'sort_order'];
}

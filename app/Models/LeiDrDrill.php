<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiDrDrill extends Model
{
    protected $fillable = ['title', 'meta', 'status', 'completed_on', 'sort_order'];

    protected function casts(): array
    {
        return ['completed_on' => 'date'];
    }
}

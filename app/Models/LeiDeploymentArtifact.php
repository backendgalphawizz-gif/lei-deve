<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiDeploymentArtifact extends Model
{
    protected $fillable = ['filename', 'version_label', 'size_display', 'sort_order'];
}

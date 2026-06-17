<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeiDeploymentRecord extends Model
{
    protected $fillable = [
        'environment', 'environment_tone', 'version', 'administrator',
        'auth_id', 'deployed_at', 'status', 'status_detail',
    ];

    protected function casts(): array
    {
        return ['deployed_at' => 'datetime'];
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->status === 'failed' && $this->status_detail) {
            return 'FAILED ('.$this->status_detail.')';
        }

        return strtoupper($this->status);
    }
}

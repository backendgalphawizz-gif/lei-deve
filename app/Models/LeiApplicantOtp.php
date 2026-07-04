<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeiApplicantOtp extends Model
{
    protected $table = 'lei_applicant_otps';

    protected $fillable = [
        'user_id',
        'code',
        'purpose',
        'expires_at',
        'verified_at',
        'attempts',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(int $minimumValidSeconds = 60): bool
    {
        if ($this->created_at && now()->lt($this->created_at->copy()->addSeconds($minimumValidSeconds))) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    public function secondsUntilMinimumExpiry(int $minimumValidSeconds = 60): int
    {
        if (! $this->created_at) {
            return 0;
        }

        $minimumUntil = $this->created_at->copy()->addSeconds($minimumValidSeconds);

        return max(0, $minimumUntil->getTimestamp() - now()->timestamp);
    }
}

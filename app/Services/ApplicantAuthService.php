<?php

namespace App\Services;

use App\Models\LeiApplicantOtp;
use App\Models\User;

class ApplicantAuthService
{
    public function generateOtp(User $user, string $purpose = 'registration'): LeiApplicantOtp
    {
        LeiApplicantOtp::query()
            ->where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->delete();

        $length = 6;
        try {
            $config = \App\Models\LeiNmConfig::query()->first();
            if ($config?->otp_length) {
                $length = (int) $config->otp_length;
            }
        } catch (\Throwable) {
            // use default
        }

        $code = str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);

        return LeiApplicantOtp::create([
            'user_id' => $user->id,
            'code' => $code,
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(10),
        ]);
    }

    public function verifyOtp(User $user, string $code, string $purpose = 'registration'): bool
    {
        $otp = LeiApplicantOtp::query()
            ->where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (! $otp || $otp->isExpired()) {
            return false;
        }

        $otp->increment('attempts');

        if (! hash_equals($otp->code, $code)) {
            return false;
        }

        $otp->update(['verified_at' => now()]);
        $user->update(['is_active' => true, 'account_status' => 'active', 'email_verified_at' => now()]);

        return true;
    }

    public function createApplicant(array $data): User
    {
        $orgName = $data['organization_name'] ?? $data['name'];

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'country_of_incorporation' => $data['country_of_incorporation'] ?? null,
            'role' => 'applicant',
            'is_active' => false,
            'account_status' => 'pending',
            'mfa_status' => 'pending',
        ]);
    }
}

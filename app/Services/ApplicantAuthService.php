<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Mail\RegistrationWelcomeMail;
use App\Models\LeiApplicantOtp;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class ApplicantAuthService
{
    public function __construct(private LeiCodeGenerator $leiCodes) {}
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

        $otp = LeiApplicantOtp::create([
            'user_id' => $user->id,
            'code' => $code,
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Send OTP email
        try {
            Mail::to($user->email)->send(new OtpMail($user->name, $code));
        } catch (\Throwable) {
            // Non-fatal: OTP is still stored in session for dev display
        }

        return $otp;
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
        $leiNumber = $this->leiCodes->generate();

        $user = User::create([
            'name' => $data['name'],
            'organization_name' => $data['organization_name'] ?? $data['name'],
            'lei_number' => $leiNumber,
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'country_of_incorporation' => $data['country_of_incorporation'] ?? null,
            'role' => 'applicant',
            'is_active' => false,
            'account_status' => 'pending',
            'mfa_status' => 'pending',
        ]);

        // Send welcome email with LEI code
        try {
            Mail::to($user->email)->send(new RegistrationWelcomeMail($user));
        } catch (\Throwable) {
            // Non-fatal
        }

        return $user;
    }

    public function assignLeiIfMissing(User $user, ?string $organizationName = null): User
    {
        $updates = [];

        if ($organizationName) {
            $updates['organization_name'] = $organizationName;
        }

        if (! $user->lei_number) {
            $updates['lei_number'] = $this->leiCodes->generate();
        }

        if ($updates !== []) {
            $user->update($updates);
        }

        return $user->fresh();
    }
}

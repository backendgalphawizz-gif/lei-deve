<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Mail\RegistrationWelcomeMail;
use App\Models\LeiApplicantOtp;
use App\Models\LeiNmConfig;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class ApplicantAuthService
{
    public function __construct(private LeiCodeGenerator $leiCodes) {}

    public function otpConfig(): array
    {
        try {
            $config = LeiNmConfig::query()->first();
        } catch (\Throwable) {
            $config = null;
        }

        return [
            'length' => max(4, min(8, (int) ($config?->otp_length ?: 6))),
            'expiry_minutes' => max(1, min(30, (int) ($config?->otp_expiry_min ?: 10))),
            'retry_limit' => max(1, min(10, (int) ($config?->otp_retry_limit ?: 5))),
        ];
    }

    public function normalizeOtpCode(?string $code): string
    {
        return preg_replace('/\D/', '', (string) $code) ?? '';
    }

    public function activeOtp(User $user, string $purpose = 'registration'): ?LeiApplicantOtp
    {
        return LeiApplicantOtp::query()
            ->where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->latest('id')
            ->first();
    }

    public function generateOtp(User $user, string $purpose = 'registration'): LeiApplicantOtp
    {
        LeiApplicantOtp::query()
            ->where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->delete();

        $config = $this->otpConfig();
        $length = $config['length'];

        $code = str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);

        $otp = LeiApplicantOtp::create([
            'user_id' => $user->id,
            'code' => $code,
            'purpose' => $purpose,
            'expires_at' => now()->addSeconds(max(60, $config['expiry_minutes'] * 60)),
        ]);

        // Send OTP email
        try {
            Mail::to($user->email)->send(new OtpMail($user->name, $code));
        } catch (\Throwable) {
            // Non-fatal: OTP is still stored in session for dev display
        }

        return $otp;
    }

    /**
     * @return array{ok: bool, reason?: string, remaining_attempts?: int}
     */
    public function verifyOtp(User $user, string $code, string $purpose = 'registration'): array
    {
        $code = $this->normalizeOtpCode($code);
        $config = $this->otpConfig();

        if (strlen($code) !== $config['length']) {
            return ['ok' => false, 'reason' => 'invalid'];
        }

        $otp = $this->activeOtp($user, $purpose);

        if (! $otp) {
            return ['ok' => false, 'reason' => 'missing'];
        }

        if ($otp->isExpired()) {
            return ['ok' => false, 'reason' => 'expired'];
        }

        if (hash_equals((string) $otp->code, $code)) {
            $otp->update(['verified_at' => now()]);
            $user->update(['is_active' => true, 'account_status' => 'active', 'email_verified_at' => now()]);

            return ['ok' => true];
        }

        $otp->increment('attempts');
        $attempts = (int) $otp->fresh()->attempts;
        $remaining = max(0, $config['retry_limit'] - $attempts);

        if ($attempts >= $config['retry_limit']) {
            return ['ok' => false, 'reason' => 'max_attempts', 'remaining_attempts' => 0];
        }

        return ['ok' => false, 'reason' => 'invalid', 'remaining_attempts' => $remaining];
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

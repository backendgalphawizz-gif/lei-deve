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
    public const MIN_VALID_SECONDS = 60;

    public const RESEND_COOLDOWN_SECONDS = 60;

    public function __construct(private LeiCodeGenerator $leiCodes) {}

    public function otpConfig(): array
    {
        try {
            $config = LeiNmConfig::query()->first();
        } catch (\Throwable) {
            $config = null;
        }

        $expiryMinutes = max(1, min(30, (int) ($config?->otp_expiry_min ?: 10)));

        return [
            'length' => max(4, min(8, (int) ($config?->otp_length ?: 6))),
            'expiry_seconds' => max(self::MIN_VALID_SECONDS, $expiryMinutes * 60),
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

    public function resendCooldownRemaining(?int $lastSentAt): int
    {
        if (! $lastSentAt) {
            return 0;
        }

        $elapsed = max(0, now()->timestamp - $lastSentAt);

        return max(0, self::RESEND_COOLDOWN_SECONDS - $elapsed);
    }

    public function parseLastSentAt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->timestamp;
        } catch (\Throwable) {
            return null;
        }
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
            'expires_at' => now()->addSeconds($config['expiry_seconds']),
            'attempts' => 0,
        ]);

        try {
            Mail::to($user->email)->send(new OtpMail($user->name, $code));
        } catch (\Throwable) {
            // Non-fatal: OTP is still stored for verification
        }

        return $otp;
    }

    /**
     * @return array{ok: bool, reason?: string}
     */
    public function verifyOtp(User $user, string $code, string $purpose = 'registration'): array
    {
        $code = $this->normalizeOtpCode($code);
        $length = $this->otpConfig()['length'];

        if (strlen($code) !== $length) {
            return ['ok' => false, 'reason' => 'invalid'];
        }

        $otp = $this->activeOtp($user, $purpose);

        if (! $otp) {
            return ['ok' => false, 'reason' => 'missing'];
        }

        if ($otp->isExpired(self::MIN_VALID_SECONDS)) {
            return ['ok' => false, 'reason' => 'expired'];
        }

        if (hash_equals((string) $otp->code, $code)) {
            $otp->update(['verified_at' => now()]);
            $user->update(['is_active' => true, 'account_status' => 'active', 'email_verified_at' => now()]);

            return ['ok' => true];
        }

        $otp->increment('attempts');

        return ['ok' => false, 'reason' => 'invalid'];
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

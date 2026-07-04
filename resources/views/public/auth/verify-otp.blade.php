@extends('public.layouts.app')

@section('title', 'Verify Your Identity')

@section('content')
@php
    $otpLength = $otpLength ?? 6;
    $otpLastSentAt = $otpLastSentAt ?? null;
    $resendCooldownRemaining = (int) ($resendCooldownRemaining ?? 0);
    $codeValidSeconds = (int) ($codeValidSeconds ?? 0);
@endphp
<section class="lei-pub-auth-section">
    <div class="lei-pub-auth-card">
        <div class="lei-pub-auth-icon"><i class="fa-solid fa-lock"></i></div>
        <h1>Verify Your Identity</h1>
        <p>A {{ $otpLength }}-digit verification code has been sent to your registered email.</p>

        @if (session('otp_code_dev') && config('app.debug'))
            <p class="lei-pub-dev-otp">Dev OTP: <strong>{{ session('otp_code_dev') }}</strong></p>
        @endif

        <p class="lei-pub-otp-valid-note" id="leiOtpValidNote" @if ($codeValidSeconds <= 0) hidden @endif>
            Your code stays active for at least <strong id="leiOtpValidSeconds">{{ $codeValidSeconds }}</strong>s — wrong entries do not cancel it.
        </p>

        <form method="POST" action="{{ route('applicant.verify-otp.submit') }}" data-no-validate id="leiOtpVerifyForm">
            @csrf
            <label>Verification Code
                <input type="text"
                       name="code"
                       value="{{ old('code') }}"
                       maxlength="{{ $otpLength }}"
                       inputmode="numeric"
                       autocomplete="one-time-code"
                       placeholder="{{ str_repeat('0', $otpLength) }}"
                       required
                       autofocus
                       class="lei-pub-otp-input @error('code') is-invalid @enderror"
                       data-type="otp"
                       data-rules="required|digits:{{ $otpLength }}">
            </label>
            @error('code')
                <p class="lei-pub-field-error">{{ $message }}</p>
            @enderror
            <button type="submit" class="lei-pub-btn full">Verify & Continue →</button>
        </form>

        <form method="POST"
              action="{{ route('applicant.verify-otp.resend') }}"
              class="lei-pub-otp-resend-form"
              id="leiOtpResendForm">
            @csrf
            <button type="submit" class="lei-pub-otp-resend-btn" id="leiOtpResendBtn">
                <i class="fa-solid fa-rotate-right" aria-hidden="true"></i>
                <span>Send code again</span>
            </button>
            <p class="lei-pub-otp-resend-note" id="leiOtpResendNote" @if ($resendCooldownRemaining <= 0) hidden @endif>
                @if ($resendCooldownRemaining > 0)
                    You can request another code in {{ $resendCooldownRemaining }}s. Your current code remains valid.
                @endif
            </p>
        </form>

        @if (session('intended_plan_id'))
            <p class="lei-pub-auth-footer" style="margin-top:8px;">After verification you will continue to your selected plan checkout.</p>
        @endif
        <p class="lei-pub-auth-note"><i class="fa-solid fa-shield"></i> Protected by bank-grade encryption. This verification is mandatory under the Regulatory Oversight Committee (ROC) framework.</p>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    var otpLength = {{ (int) $otpLength }};
    var cooldownSeconds = {{ (int) \App\Services\ApplicantAuthService::RESEND_COOLDOWN_SECONDS }};
    var lastSentAt = @json($otpLastSentAt);
    var codeValidUntil = @json($codeValidSeconds > 0 ? now()->timestamp + $codeValidSeconds : null);

    var input = document.querySelector('.lei-pub-otp-input');
    if (input) {
        input.addEventListener('input', function () {
            input.value = input.value.replace(/\D/g, '').slice(0, otpLength);
        });
    }

    var btn = document.getElementById('leiOtpResendBtn');
    var note = document.getElementById('leiOtpResendNote');
    var validNote = document.getElementById('leiOtpValidNote');
    var validSecondsEl = document.getElementById('leiOtpValidSeconds');
    var resendForm = document.getElementById('leiOtpResendForm');

    function resendRemaining() {
        if (!lastSentAt) return 0;
        var elapsed = Math.floor(Date.now() / 1000) - lastSentAt;
        return Math.max(0, cooldownSeconds - elapsed);
    }

    function codeValidRemaining() {
        if (!codeValidUntil) return 0;
        return Math.max(0, codeValidUntil - Math.floor(Date.now() / 1000));
    }

    function updateTimers() {
        var resendWait = resendRemaining();
        var validWait = codeValidRemaining();

        if (btn) {
            btn.disabled = resendWait > 0;
        }

        if (note) {
            if (resendWait > 0) {
                note.hidden = false;
                note.textContent = 'You can request another code in ' + resendWait + 's. Your current code remains valid.';
            } else {
                note.hidden = true;
            }
        }

        if (validNote && validSecondsEl) {
            if (validWait > 0) {
                validNote.hidden = false;
                validSecondsEl.textContent = String(validWait);
            } else {
                validNote.hidden = true;
            }
        }
    }

    if (resendForm) {
        resendForm.addEventListener('submit', function (e) {
            if (resendRemaining() > 0) {
                e.preventDefault();
                updateTimers();
            }
        });
    }

    updateTimers();
    setInterval(updateTimers, 1000);
})();
</script>
@endpush

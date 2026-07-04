@extends('public.layouts.app')

@section('title', 'Verify Your Identity')

@section('content')
@php
    $otpLength = $otpLength ?? 6;
@endphp
<section class="lei-pub-auth-section">
    <div class="lei-pub-auth-card">
        <div class="lei-pub-auth-icon"><i class="fa-solid fa-lock"></i></div>
        <h1>Verify Your Identity</h1>
        <p>A {{ $otpLength }}-digit verification code has been sent to your registered email.</p>

        @if (session('otp_code_dev') && config('app.debug'))
            <p class="lei-pub-dev-otp">Dev OTP: <strong>{{ session('otp_code_dev') }}</strong></p>
        @endif

        <form method="POST" action="{{ route('applicant.verify-otp.submit') }}" data-no-validate>
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
            <p class="lei-pub-otp-resend-note" id="leiOtpResendNote" hidden></p>
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
    var input = document.querySelector('.lei-pub-otp-input');
    if (input) {
        input.addEventListener('input', function () {
            input.value = input.value.replace(/\D/g, '').slice(0, otpLength);
        });
    }

    var cooldownSeconds = 60;
    var lastSent = @json(session('otp_last_sent_at'));
    var btn = document.getElementById('leiOtpResendBtn');
    var note = document.getElementById('leiOtpResendNote');
    var timerId;

    function remainingSeconds() {
        if (!lastSent) return 0;
        var elapsed = Math.floor((Date.now() - new Date(lastSent).getTime()) / 1000);
        return Math.max(0, cooldownSeconds - elapsed);
    }

    function updateResendState() {
        var wait = remainingSeconds();
        if (!btn) return;
        if (wait > 0) {
            btn.disabled = true;
            if (note) {
                note.hidden = false;
                note.textContent = 'You can request another code in ' + wait + 's.';
            }
            timerId = setTimeout(updateResendState, 1000);
        } else {
            btn.disabled = false;
            if (note) note.hidden = true;
            clearTimeout(timerId);
        }
    }

    updateResendState();
})();
</script>
@endpush

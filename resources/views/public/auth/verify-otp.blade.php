@extends('public.layouts.app')

@section('title', 'Verify Your Identity')

@section('content')
<section class="lei-pub-auth-section">
    <div class="lei-pub-auth-card">
        <div class="lei-pub-auth-icon"><i class="fa-solid fa-lock"></i></div>
        <h1>Verify Your Identity</h1>
        <p>A 6-digit verification code has been sent to your registered email/phone.</p>
        @if (session('assigned_lei_number'))
            <div class="lei-pub-lei-assigned">
                <span>Your LEI Code</span>
                <strong>{{ session('assigned_lei_number') }}</strong>
                <small>Assigned when you created your account. Save this code for your records.</small>
            </div>
        @endif
        @if (session('otp_code_dev') && config('app.debug'))
            <p class="lei-pub-dev-otp">Dev OTP: <strong>{{ session('otp_code_dev') }}</strong></p>
        @endif
        <form method="POST" action="{{ route('applicant.verify-otp.submit') }}">
            @csrf
            <label>Verification Code
                <input type="text" name="code" maxlength="6" pattern="[0-9]{6}" placeholder="000000" required autofocus class="lei-pub-otp-input">
            </label>
            <button type="submit" class="lei-pub-btn full">Verify & Continue →</button>
        </form>
        @if (session('intended_plan_id'))
            <p class="lei-pub-auth-footer" style="margin-top:8px;">After verification you will continue to your selected plan checkout.</p>
        @endif
        <p class="lei-pub-auth-note"><i class="fa-solid fa-shield"></i> Protected by bank-grade encryption. This verification is mandatory under the Regulatory Oversight Committee (ROC) framework.</p>
    </div>
</section>
@endsection

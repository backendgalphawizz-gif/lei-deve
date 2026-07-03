@extends('emails.layouts.base')

@section('subject', 'Your Verification Code — ' . (config('app.name')))

@section('body')
<p class="em-eyebrow">Account Verification</p>
<h1 class="em-h1">Your One-Time Password</h1>
<p class="em-p">Hello {{ $userName }},</p>
<p class="em-p">Use the verification code below to complete your account setup. This code is valid for <strong>5 minutes</strong>.</p>

<div class="em-otp-box">
  <div class="em-otp-code">{{ $otpCode }}</div>
  <div class="em-otp-expiry">This code expires in 5 minutes · Do not share it with anyone</div>
</div>

<p class="em-p">If you didn't request this code, please ignore this email. Your account will remain secure.</p>
<hr class="em-divider">
<p class="em-p" style="font-size:13px;color:#94a3b8;">For security, our team will never ask for your OTP via phone or chat.</p>
@endsection

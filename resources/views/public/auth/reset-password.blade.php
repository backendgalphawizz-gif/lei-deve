@extends('public.layouts.app')

@section('title', 'Reset Password')

@section('content')
<section class="lei-pub-auth-section">
    <div class="lei-pub-auth-card">
        <div class="lei-pub-auth-icon"><i class="fa-solid fa-key"></i></div>
        <h1>Reset Your Password</h1>
        <p>Please enter a new, high-security password for your account.</p>
        <form method="POST" action="{{ route('applicant.reset-password.submit') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">
            <label>New Password<input type="password" name="password" required data-rules="required|password:strong"></label>
            <label>Confirm New Password<input type="password" name="password_confirmation" required></label>
            <button type="submit" class="lei-pub-btn full">Update Password →</button>
        </form>
    </div>
</section>
@endsection

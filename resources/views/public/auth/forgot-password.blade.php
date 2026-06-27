@extends('public.layouts.app')

@section('title', 'Password Recovery')

@section('content')
<section class="lei-pub-auth-section">
    <div class="lei-pub-auth-card">
        <div class="lei-pub-auth-icon"><i class="fa-solid fa-shield"></i></div>
        <h1>Password Recovery</h1>
        <p>Enter your registered work email to receive a secure recovery link.</p>
        <form method="POST" action="{{ route('applicant.forgot-password.submit') }}">
            @csrf
            <label>Registered Work Email
                <div class="lei-pub-input-icon"><i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="name@organization.com" required>
                </div>
            </label>
            <button type="submit" class="lei-pub-btn full">Send Recovery Link →</button>
        </form>
        <p class="lei-pub-auth-footer"><a href="{{ route('applicant.login') }}">← Back to Login</a></p>
    </div>
</section>
@endsection

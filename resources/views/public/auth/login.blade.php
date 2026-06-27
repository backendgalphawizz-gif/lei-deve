@extends('public.layouts.app')

@section('title', 'Applicant Portal')

@section('content')
<section class="lei-pub-auth-section">
    <div class="lei-pub-auth-card">
        <h1>Applicant Portal</h1>
        <p>Log in to manage your Legal Entity Identifier applications and compliance data.</p>
        <form method="POST" action="{{ route('applicant.login.submit') }}">
            @csrf
            <label>Work Email
                <div class="lei-pub-input-icon"><i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="name@organization.com" required autofocus>
                </div>
            </label>
            <label>
                <span class="lei-pub-label-row">
                    <span>Password</span>
                    <a href="{{ route('applicant.forgot-password') }}">Forgot Password?</a>
                </span>
                <div class="lei-pub-input-icon"><i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
            </label>
            <label class="lei-pub-check"><input type="checkbox" name="remember"> Keep me signed in for 30 days</label>
            <button type="submit" class="lei-pub-btn full">Sign In →</button>
        </form>
        <p class="lei-pub-auth-footer">New to the Registry? <a href="{{ route('register') }}">Register for an account</a></p>
        @if (session('intended_plan_id'))
            <p class="lei-pub-auth-footer" style="margin-top:8px;">You selected a plan — sign in with your account to continue to checkout.</p>
        @endif
    </div>
</section>
@endsection

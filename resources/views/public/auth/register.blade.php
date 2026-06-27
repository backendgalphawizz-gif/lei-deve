@extends('public.layouts.app')

@section('title', 'Applicant Registration')

@section('content')
<section class="lei-pub-register-section">
    <div class="lei-pub-container lei-pub-register-grid">
        <div class="lei-pub-register-info">
            <h1>Secure Your Institutional Identity</h1>
            <p>Join the global network of verified legal entities. Our registry portal ensures data integrity, regulatory compliance, and seamless LEI lifecycle management.</p>
            <ul class="lei-pub-register-features">
                <li><strong>Global Entity Recognition</strong><span>Instant visibility across international financial markets.</span></li>
                <li><strong>24/7 Compliance Monitoring</strong><span>Automated alerts for renewal cycles and regulatory changes.</span></li>
                <li><strong>Secure Data Management</strong><span>Enterprise-grade encryption and strict access controls.</span></li>
            </ul>
        </div>
        <div class="lei-pub-auth-card wide">
            <h2>Applicant Registration</h2>
            <p>Initialize your legal entity identifier request.</p>
            @if ($selectedPlan ?? null)
                <p class="lei-pub-auth-plan-note">Selected plan: <strong>{{ $selectedPlan->name }}</strong> — after verification you will continue to checkout.</p>
            @endif
            <form method="POST" action="{{ route('register.submit') }}">
                @csrf
                <div class="lei-pub-form-row">
                    <label>Full Name<input type="text" name="name" value="{{ old('name') }}" placeholder="Alexander Hamilton" required></label>
                    <label>Organization Name<input type="text" name="organization_name" value="{{ old('organization_name') }}" placeholder="Global Finance Corp" required></label>
                </div>
                <div class="lei-pub-form-row">
                    <label>Work Email<input type="email" name="email" value="{{ old('email') }}" placeholder="a.hamilton@corp.com" required></label>
                    <label>Phone Number<input type="text" name="phone" value="{{ old('phone') }}" placeholder="+1 (555) 000-0000"></label>
                </div>
                <label>Country of Incorporation
                    <select name="country_of_incorporation">
                        <option value="">Select a country</option>
                        <option value="United States">United States</option>
                        <option value="United Kingdom">United Kingdom</option>
                        <option value="India">India</option>
                        <option value="Germany">Germany</option>
                        <option value="Singapore">Singapore</option>
                    </select>
                </label>
                <label>Password<input type="password" name="password" required data-rules="required|password:strong"></label>
                <label>Confirm Password<input type="password" name="password_confirmation" required></label>
                <label class="lei-pub-check"><input type="checkbox" name="terms" value="1" required> I agree to the <a href="{{ route('pages.show', 'terms-of-service') }}">Terms of Service</a> and regulatory operating standards.</label>
                <label class="lei-pub-check"><input type="checkbox" name="privacy" value="1" required> I consent to data processing according to the <a href="{{ route('pages.show', 'privacy-policy') }}">Data Privacy Policy</a>.</label>
                <button type="submit" class="lei-pub-btn full">Create Account →</button>
            </form>
            <p class="lei-pub-auth-footer">Already have an account? <a href="{{ route('applicant.login') }}">Log in</a></p>
        </div>
    </div>
</section>
@endsection

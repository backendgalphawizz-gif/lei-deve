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
            <p>Initialize your legal entity identifier request. Your unique 20-character LEI code will be assigned as soon as you create your account.</p>
            @if ($errors->any())
                <div style="background:#fff5f5;border:1px solid #fca5a5;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#b91c1c;">
                    <ul style="margin:0;padding-left:18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('info'))
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#1e40af;">
                    {{ session('info') }}
                </div>
            @endif

            @if ($registrationPrefill ?? null)
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#166534;">
                    <strong>Entity details loaded.</strong>
                    @if (! empty($registrationPrefill['source_lei']))
                        We pre-filled information from GLEIF record <span style="font-family:monospace;">{{ $registrationPrefill['source_lei'] }}</span>.
                        You may edit the organization name below — a <strong>new LEI code</strong> will be assigned to your account.
                    @else
                        We pre-filled your search details. You may edit them before continuing.
                    @endif
                </div>
            @endif

            @if ($selectedPlan ?? null)
                <p class="lei-pub-auth-plan-note">Selected plan: <strong>{{ $selectedPlan->name }}</strong> — after verification you will continue to checkout.</p>
            @endif
            <form method="POST" action="{{ route('register.submit') }}">
                @csrf
                <div class="lei-pub-form-row">
                        <label>Full Name<input type="text" name="name" value="{{ old('name') }}" placeholder="Alexander Hamilton" required class="{{ $errors->has('name') ? 'is-invalid' : '' }}"></label>
                    <label>Organization Name<input type="text" name="organization_name" value="{{ old('organization_name', $registrationPrefill['entity_name'] ?? '') }}" placeholder="Global Finance Corp" required class="{{ $errors->has('organization_name') ? 'is-invalid' : '' }}"></label>
                </div>
                <div class="lei-pub-form-row">
                    <label>Work Email<input type="email" name="email" value="{{ old('email') }}" placeholder="a.hamilton@corp.com" required></label>
                    <label>Phone Number<input type="text" name="phone" value="{{ old('phone') }}" placeholder="+1 (555) 000-0000"></label>
                </div>
                <label>Country of Incorporation
                    <select name="country_of_incorporation">
                        @php $prefillCountry = old('country_of_incorporation', $registrationPrefill['country'] ?? ''); @endphp
                        <option value="">Select a country</option>
                        <option value="United States" @selected($prefillCountry === 'United States')>United States</option>
                        <option value="United Kingdom" @selected($prefillCountry === 'United Kingdom')>United Kingdom</option>
                        <option value="India" @selected($prefillCountry === 'India')>India</option>
                        <option value="Germany" @selected($prefillCountry === 'Germany')>Germany</option>
                        <option value="Singapore" @selected($prefillCountry === 'Singapore')>Singapore</option>
                    </select>
                </label>
                <label>Password<input type="password" name="password" required data-rules="required|password:strong"></label>
                <label>Confirm Password<input type="password" name="password_confirmation" required></label>
                <label class="lei-pub-check"><input type="checkbox" name="terms" value="1" required> I agree to the <a href="{{ route('pages.show', 'terms-of-service') }}">Terms of Service</a> and regulatory operating standards.</label>
                <label class="lei-pub-check"><input type="checkbox" name="privacy" value="1" required> I consent to data processing according to the <a href="{{ route('pages.show', 'privacy-policy') }}">Data Privacy Policy</a>.</label>
                <button type="submit" class="lei-pub-btn full">Create Account & Get LEI →</button>
            </form>
            <div class="lei-pub-secure-bar" style="margin-top:14px;">
                <span><i class="fa-solid fa-lock"></i> SSL Secured</span>
                <span><i class="fa-solid fa-shield-halved"></i> ISO 27001</span>
                <span><i class="fa-solid fa-user-shield"></i> GDPR Compliant</span>
                <span><i class="fa-solid fa-headset"></i> 24×7 Support</span>
            </div>
            <p class="lei-pub-auth-footer">Already have an account? <a href="{{ route('applicant.login') }}">Log in</a></p>
        </div>
    </div>
</section>
@endsection

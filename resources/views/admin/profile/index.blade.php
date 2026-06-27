@extends('admin.layouts.app')

@section('title', 'My Profile')
@section('body_class', 'lei-page-profile')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-profile.css') }}?v=1">
@endpush

@section('breadcrumbs')
    @include('admin.partials.breadcrumbs', ['current' => 'My Profile'])
@endsection

@section('content')
<div class="lei-profile-page">
    <div class="lei-profile-hero">
        <div class="lei-profile-hero-photo">
            <img src="{{ $user->profileImageUrl() }}" alt="{{ $user->name }}" id="leiProfilePhotoPreview">
        </div>
        <div class="lei-profile-hero-info">
            <h2>{{ $user->name }}</h2>
            <p>{{ $user->email }}</p>
            <div class="lei-profile-badges">
                <span class="lei-profile-badge">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span>
                @if ($user->adminRole)
                    <span class="lei-profile-badge lei-profile-badge--muted">{{ $user->adminRole->name }}</span>
                @endif
                @if ($user->organization)
                    <span class="lei-profile-badge lei-profile-badge--muted">{{ $user->organization->name }}</span>
                @endif
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="lei-profile-errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="lei-profile-grid">
        <section class="lei-profile-card">
            <h3>Profile Details</h3>
            <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="lei-profile-form">
                @csrf
                @method('PUT')

                <label class="lei-profile-field">
                    <span>Profile Photo</span>
                    <input type="file" name="photo" accept="image/png,image/jpeg,image/webp" id="leiProfilePhotoInput">
                    @if ($user->avatar)
                        <label class="lei-profile-check"><input type="checkbox" name="remove_photo" value="1"> Remove photo</label>
                    @endif
                </label>

                <label class="lei-profile-field">
                    <span>Full Name</span>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required data-rules="required|maxLen:120">
                </label>

                <label class="lei-profile-field">
                    <span>Email</span>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required data-rules="required|email|maxLen:150">
                </label>

                <label class="lei-profile-field">
                    <span>Job Title</span>
                    <input type="text" name="job_title" value="{{ old('job_title', $user->job_title) }}" placeholder="e.g. Registry Administrator" data-rules="maxLen:120">
                </label>

                <label class="lei-profile-field">
                    <span>Phone</span>
                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="10-digit mobile number" data-type="phone" data-rules="phone">
                </label>

                <button type="submit" class="lei-profile-btn-primary">Save Profile</button>
            </form>
        </section>

        <section class="lei-profile-card">
            <h3>Security</h3>
            <form method="POST" action="{{ route('admin.profile.password') }}" class="lei-profile-form">
                @csrf
                @method('PUT')

                <label class="lei-profile-field">
                    <span>Current Password</span>
                    <input type="password" name="current_password" required autocomplete="current-password" data-rules="required">
                </label>
                <label class="lei-profile-field">
                    <span>New Password</span>
                    <input type="password" name="password" required autocomplete="new-password" data-rules="required|password:8">
                </label>
                <label class="lei-profile-field">
                    <span>Confirm Password</span>
                    <input type="password" name="password_confirmation" required autocomplete="new-password" data-rules="required|confirmed:password">
                </label>
                <button type="submit" class="lei-profile-btn-outline">Update Password</button>
            </form>
        </section>

        <aside class="lei-profile-card lei-profile-card--side">
            <h3>Account Info</h3>
            <dl class="lei-profile-meta">
                <dt>System ID</dt>
                <dd>{{ $user->system_id }}</dd>
                <dt>Account Status</dt>
                <dd><span class="lei-profile-status lei-profile-status--{{ $user->account_status }}">{{ ucfirst($user->account_status) }}</span></dd>
                <dt>MFA</dt>
                <dd>{{ ucfirst($user->mfa_status) }}</dd>
                <dt>Last Login</dt>
                <dd>{{ $user->last_login_at?->format($settings->date_format) ?? '—' }}</dd>
                <dt>Portal</dt>
                <dd>{{ $settings->portal_title }}</dd>
                <dt>Company</dt>
                <dd>{{ $settings->company_name }}</dd>
            </dl>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var input = document.getElementById('leiProfilePhotoInput');
    var preview = document.getElementById('leiProfilePhotoPreview');
    if (input && preview) {
        input.addEventListener('change', function () {
            if (!input.files || !input.files[0]) return;
            var r = new FileReader();
            r.onload = function (e) { preview.src = e.target.result; };
            r.readAsDataURL(input.files[0]);
        });
    }
})();
</script>
@endpush

@extends('applicant.layouts.app')

@section('title', 'User Profile')

@section('content')
<div class="lei-portal-page-head">
    <div>
        <h1>User Profile</h1>
        <p>Manage your account and organization contact details.</p>
    </div>
</div>

<form method="POST" action="{{ route('applicant.profile.update') }}" class="lei-portal-split">
    @csrf
    <div class="lei-portal-card">
        <h2>Contact Information</h2>
        <div class="lei-portal-form-grid">
            <div class="lei-portal-field">
                <label for="name">Full Name</label>
                <input id="name" name="name" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="lei-portal-field">
                <label for="email">Email Address</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}">
            </div>
            <div class="lei-portal-field">
                <label for="phone">Phone</label>
                <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
            </div>
            <div class="lei-portal-field">
                <label for="country_of_incorporation">Country</label>
                <input id="country_of_incorporation" name="country_of_incorporation" value="{{ old('country_of_incorporation', $user->country_of_incorporation) }}">
            </div>
        </div>
        <div class="lei-portal-actions">
            <button type="submit" class="lei-btn-primary">Save Changes</button>
        </div>
    </div>
    <aside class="lei-portal-summary lei-portal-summary--sticky">
        <img src="{{ $user->profileImageUrl() }}" alt="{{ $user->name }}" class="lei-portal-profile-avatar">
        <h3>{{ $user->name }}</h3>
        <p>{{ $user->email }}</p>
        <p class="muted">Applicant account</p>
    </aside>
</form>
@endsection

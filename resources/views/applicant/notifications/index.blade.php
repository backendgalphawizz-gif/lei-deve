@extends('applicant.layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="lei-portal-page-head">
    <div>
        <h1>Notifications</h1>
        <p>Stay updated on application status, renewals, and support responses.</p>
    </div>
</div>

<div class="lei-portal-card lei-portal-card--padded">
    @foreach ($notifications as $notification)
        <div style="padding:14px 0;border-bottom:1px solid #e5e7eb;">
            <strong>{{ $notification['title'] }}</strong>
            <div class="muted">{{ $notification['time'] }}</div>
        </div>
    @endforeach
</div>
@endsection

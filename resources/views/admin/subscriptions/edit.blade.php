@extends('admin.layouts.app')

@section('title', 'Edit Subscription')
@section('body_class', 'lei-page-website-admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-website-admin.css') }}?v=3">
@endpush

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Registry</a>
    <span> / </span>
    <a href="{{ route('admin.subscriptions.index', ['tab' => 'subscriptions']) }}">Subscription Management</a>
    <span> / </span>
    <span>{{ $subscription->reference }}</span>
@endsection

@section('content')
<div class="lei-wm-form-page">
    <header class="lei-wm-form-head">
        <div>
            <h2>Edit Subscription</h2>
            <p>{{ $subscription->reference }} — {{ $subscription->user?->name }}</p>
        </div>
        <a href="{{ route('admin.subscriptions.index', ['tab' => 'subscriptions']) }}" class="lei-wm-form-close" aria-label="Close">&times;</a>
    </header>

    <div class="lei-wm-form-card">
        @include('admin.subscriptions.partials.subscription-form', [
            'subscription' => $subscription,
            'applicants' => collect(),
            'pricingPlans' => collect(),
            'statuses' => $statuses,
            'paymentStatuses' => $paymentStatuses,
        ])
    </div>
</div>
@endsection

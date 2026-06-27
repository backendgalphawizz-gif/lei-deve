@extends('admin.layouts.app')

@section('title', 'Add Subscription')
@section('body_class', 'lei-page-website-admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-website-admin.css') }}?v=3">
@endpush

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Registry</a>
    <span> / </span>
    <a href="{{ route('admin.subscriptions.index', ['tab' => 'subscriptions']) }}">Subscription Management</a>
    <span> / </span>
    <span>Add Subscription</span>
@endsection

@section('content')
<div class="lei-wm-form-page">
    <header class="lei-wm-form-head">
        <div>
            <h2>Add Subscription</h2>
            <p>Assign a plan to an applicant. It will appear on their portal at /portal.</p>
        </div>
        <a href="{{ route('admin.subscriptions.index', ['tab' => 'subscriptions']) }}" class="lei-wm-form-close" aria-label="Close">&times;</a>
    </header>

    <div class="lei-wm-form-card">
        @include('admin.subscriptions.partials.subscription-form', [
            'subscription' => $subscription,
            'applicants' => $applicants,
            'pricingPlans' => $pricingPlans,
            'statuses' => $statuses,
            'paymentStatuses' => $paymentStatuses,
        ])
    </div>
</div>
@endsection

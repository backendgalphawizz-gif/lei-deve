@extends('admin.layouts.app')

@section('title', 'Edit Pricing Plan')
@section('body_class', 'lei-page-website-admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-website-admin.css') }}?v=3">
@endpush

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Registry</a>
    <span> / </span>
    <a href="{{ route('admin.subscriptions.index', ['tab' => 'plans']) }}">Subscription Management</a>
    <span> / </span>
    <span>Edit Plan</span>
@endsection

@section('content')
<div class="lei-wm-form-page">
    <header class="lei-wm-form-head">
        <div>
            <h2>Edit Pricing Plan</h2>
            <p>{{ $plan->name }}</p>
        </div>
        <a href="{{ route('admin.subscriptions.index', ['tab' => 'plans']) }}" class="lei-wm-form-close" aria-label="Close">&times;</a>
    </header>

    <div class="lei-wm-form-card">
        @include('admin.subscriptions.plans.partials.form', ['plan' => $plan])
    </div>

    <div class="lei-wm-form-card" style="margin-top:16px;">
        @include('admin.partials.icon-actions', [
            'deleteUrl' => route('admin.pricing-plans.destroy', $plan),
            'deleteConfirm' => 'Delete this pricing plan?',
            'deleteTitle' => 'Delete Plan',
        ])
    </div>
</div>
@endsection

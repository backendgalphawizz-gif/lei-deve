@extends('admin.layouts.app')

@section('title', 'Add Pricing Plan')
@section('body_class', 'lei-page-website-admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-website-admin.css') }}?v=3">
@endpush

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Registry</a>
    <span> / </span>
    <a href="{{ route('admin.subscriptions.index', ['tab' => 'plans']) }}">Subscription Management</a>
    <span> / </span>
    <span>Add Plan</span>
@endsection

@section('content')
<div class="lei-wm-form-page">
    <header class="lei-wm-form-head">
        <div>
            <h2>Add Pricing Plan</h2>
            <p>Create a plan that appears on the public pricing page when active.</p>
        </div>
        <a href="{{ route('admin.subscriptions.index', ['tab' => 'plans']) }}" class="lei-wm-form-close" aria-label="Close">&times;</a>
    </header>

    <div class="lei-wm-form-card">
        @include('admin.subscriptions.plans.partials.form', ['plan' => $plan])
    </div>
</div>
@endsection

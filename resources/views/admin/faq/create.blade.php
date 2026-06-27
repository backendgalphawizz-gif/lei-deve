@extends('admin.layouts.app')

@section('title', 'Add FAQ')
@section('body_class', 'lei-page-website-admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-website-admin.css') }}?v=3">
@endpush

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Registry</a>
    <span> / </span>
    <a href="{{ route('admin.faq.index') }}">FAQ Management</a>
    <span> / </span>
    <span>Add FAQ</span>
@endsection

@section('content')
<div class="lei-wm-form-page">
    <header class="lei-wm-form-head">
        <div>
            <h2>Add FAQ</h2>
            <p>Create a new question for the public FAQ or pricing page.</p>
        </div>
        <a href="{{ route('admin.faq.index') }}" class="lei-wm-form-close" aria-label="Close">&times;</a>
    </header>

    <div class="lei-wm-form-card">
        @include('admin.faq.partials.form', ['faq' => $faq, 'categories' => $categories])
    </div>
</div>
@endsection

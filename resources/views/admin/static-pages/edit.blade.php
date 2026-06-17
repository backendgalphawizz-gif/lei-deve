@extends('admin.layouts.app')

@section('title', 'Edit — ' . $page->title)
@section('body_class', 'lei-page-static-pages lei-page-static-pages-form')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-static-pages.css') }}?v=1">
@endpush

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Registry</a>
    <span> / </span>
    <a href="{{ route('admin.static-pages.index') }}">Static Pages</a>
    <span> / </span>
    <span>Edit</span>
@endsection

@section('content')
<div class="lei-sp-form-page">
    @if ($errors->any())
        <div class="lei-sp-errors">
            <strong>Please fix the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <header class="lei-sp-form-head">
        <div>
            <h2>Edit Static Page</h2>
            <p>Updating <strong>{{ $page->title }}</strong> · <code>/{{ $page->slug }}</code></p>
        </div>
        <a href="{{ route('admin.static-pages.index') }}" class="lei-sp-form-close" aria-label="Close">&times;</a>
    </header>

    @include('admin.static-pages.partials.form', ['page' => $page, 'pageTypes' => $pageTypes, 'statuses' => $statuses])
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/lei-static-pages.js') }}?v=1"></script>
@endpush

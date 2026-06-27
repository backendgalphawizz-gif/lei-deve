<!DOCTYPE html>
<html lang="{{ $businessSettings->locale ?? 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', $businessSettings->meta_description ?? '')">
    <title>@yield('title', 'Home') | {{ $businessSettings->company_name ?? 'LEI' }}</title>
    @if ($businessSettings->faviconUrl())
        <link rel="icon" href="{{ $businessSettings->faviconUrl() }}">
    @endif
    <style>:root { {{ $businessSettings->cssVars() }} }</style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/lei-public.css') }}?v=7">
    <link rel="stylesheet" href="{{ asset('css/lei-admin-validation.css') }}?v=4">
    @stack('styles')
</head>
<body class="lei-public @yield('body_class')">
@include('public.partials.header')

@php
    $publicFlashMessages = [];
    if (session('success')) {
        $publicFlashMessages[] = ['type' => 'success', 'message' => session('success')];
    }
    if (session('info')) {
        $publicFlashMessages[] = ['type' => 'info', 'message' => session('info')];
    }
    if (session('error')) {
        $publicFlashMessages[] = ['type' => 'error', 'message' => session('error')];
    }
    if ($errors->any()) {
        $publicFlashMessages[] = ['type' => 'error', 'message' => $errors->first()];
    }
@endphp

<main class="lei-public-main">
    @yield('content')
</main>

@include('public.partials.footer')
@include('public.partials.notify-modal')
@if (! empty($publicFlashMessages))
    <script type="application/json" id="lei-pub-flash-data">@json($publicFlashMessages)</script>
@endif
<script src="{{ asset('js/lei-public.js') }}?v=1"></script>
<script src="{{ asset('js/lei-public-notify.js') }}?v=1"></script>
<script src="{{ asset('js/lei-admin-validation.js') }}?v=4"></script>
@stack('scripts')
</body>
</html>

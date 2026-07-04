<!DOCTYPE html>
<html lang="{{ $businessSettings->locale ?? 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ $businessSettings->company_name ?? 'LEI' }} Portal</title>
    @if ($businessSettings->faviconUrl())
        <link rel="icon" href="{{ $businessSettings->faviconUrl() }}">
    @endif
    <style>:root { {{ $businessSettings->cssVars() }} }</style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/lei-admin.css') }}?v=8">
    <link rel="stylesheet" href="{{ asset('css/lei-global.css') }}?v=3">
    <link rel="stylesheet" href="{{ asset('css/lei-applicant-portal.css') }}?v=14">
    <link rel="stylesheet" href="{{ asset('css/lei-admin-validation.css') }}?v=4">
    @stack('styles')
</head>
<body class="lei-body lei-applicant-portal">
<a href="#lei-main-content" class="lei-skip-link">Skip to main content</a>
<div class="lei-admin-wrap">
    @include('applicant.partials.sidebar')

    <div class="lei-main">
        @include('applicant.partials.topbar')

        <main class="lei-content" id="lei-main-content" tabindex="-1">
            @if ($portalDraft ?? null)
                <div class="lei-portal-resume-banner">
                    <div>
                        <strong>In-progress {{ ucfirst($portalDraft->workflow_type) }}</strong>
                        <span>Continue from step {{ $portalDraft->workflow_step }} of 4</span>
                    </div>
                    <a href="{{ $portalDraft->workflow_type === 'renewal' ? route('applicant.renewal.step', ['step' => $portalDraft->workflow_step]) : route('applicant.registration.step', ['step' => $portalDraft->workflow_step]) }}" class="lei-btn-primary lei-portal-btn-sm">Resume</a>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

{{-- Session timeout warning banner --}}
<div id="lei-session-warn" class="lei-session-warn" hidden role="alert" aria-live="polite">
    <div class="lei-session-warn-inner">
        <i class="fa-solid fa-clock"></i>
        <div>
            <strong>Your session is about to expire</strong>
            <p>You will be logged out in <span data-countdown>2m</span>. Any unsaved changes may be lost.</p>
        </div>
        <button type="button" class="lei-btn-primary lei-portal-btn-sm" data-extend>Stay Logged In</button>
    </div>
</div>

@php
    $portalFlashMessages = [];
    if (session('success')) {
        $portalFlashMessages[] = ['type' => 'success', 'message' => session('success')];
    }
    if (session('info')) {
        $portalFlashMessages[] = ['type' => 'info', 'message' => session('info')];
    }
    if (session('error')) {
        $portalFlashMessages[] = ['type' => 'error', 'message' => session('error')];
    }
@endphp

@include('public.partials.notify-modal')
@if (count($portalFlashMessages))
    <script type="application/json" id="lei-pub-flash-data">@json($portalFlashMessages)</script>
@endif

<script src="{{ asset('js/lei-global.js') }}?v=3"></script>
<script src="{{ asset('js/lei-applicant-portal.js') }}?v=6"></script>
<script src="{{ asset('js/lei-public-notify.js') }}?v=2"></script>
<script src="{{ asset('js/lei-admin-validation.js') }}?v=4"></script>
@stack('scripts')
</body>
</html>

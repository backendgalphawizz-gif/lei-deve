<!DOCTYPE html>
<html lang="{{ $businessSettings->locale ?? 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $businessSettings->meta_description ?? '' }}">
    <title>@yield('title', 'Dashboard') | {{ $businessSettings->portal_title ?? 'LEI Super Admin' }}</title>
    @if ($businessSettings->faviconUrl())
        <link rel="icon" href="{{ $businessSettings->faviconUrl() }}">
    @endif
    <style>:root { {{ $businessSettings->cssVars() }} }</style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/lei-admin.css') }}?v=8">
    <link rel="stylesheet" href="{{ asset('css/lei-global.css') }}?v=3">
    <link rel="stylesheet" href="{{ asset('css/lei-users.css') }}?v=3">
    <link rel="stylesheet" href="{{ asset('css/lei-applications.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/lei-admin-validation.css') }}?v=4">
    <link rel="stylesheet" href="{{ asset('css/lei-admin-actions.css') }}?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    @stack('styles')
</head>
<body class="lei-body @yield('body_class')">
<div class="lei-admin-wrap">
    <aside class="lei-sidebar">
        <div class="lei-sidebar-brand">
            <div class="lei-brand-logo-box">
                <img src="{{ $businessSettings->sidebarIconUrl() }}" alt="{{ $businessSettings->company_name }}">
            </div>
            <div class="lei-brand-info">
                <span class="lei-brand-role">{{ $businessSettings->sidebarBrandTitle() }}</span>
                <span class="lei-brand-tagline">{{ $businessSettings->tagline }}</span>
            </div>
        </div>

        <ul class="lei-sidebar-nav">
            @foreach ($menuItems as $item)
                <li>
                    <a href="{{ $item->route_name ? route($item->route_name) : '#' }}"
                       class="{{ \App\Support\AdminNav::isActive($item->route_name) ? 'active' : '' }}{{ $item->route_name ? '' : ' lei-nav-disabled' }}">
                        @include('admin.partials.nav-icon', ['icon' => $item->icon])
                        <span>{{ $item->label }}</span>
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="lei-sidebar-footer">
            @if ($businessSettings->copyright_text)
                <!-- <p class="lei-sidebar-copy">{{ $businessSettings->copyright_text }}</p> -->
            @endif
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="lei-btn-logout">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <div class="lei-main">
        @include('admin.partials.topbar')

        <main class="lei-content">
            @hasSection('breadcrumbs')
                <nav class="lei-breadcrumbs">@yield('breadcrumbs')</nav>
            @endif
            @yield('content')
        </main>
    </div>
</div>
@include('admin.partials.confirm-modal')
@include('admin.partials.flash-toasts')
<div id="leiToastStack" class="lei-toast-stack" aria-live="polite"></div>
<script src="{{ asset('js/lei-global.js') }}?v=3"></script>
<script src="{{ asset('js/lei-admin-validation.js') }}?v=4"></script>
<script src="{{ asset('js/lei-admin-toast.js') }}?v=1"></script>
<script src="{{ asset('js/lei-admin-confirm.js') }}?v=2"></script>
@stack('scripts')
</body>
</html>

<header class="lei-topbar" id="leiTopbar">
    <div class="lei-topbar-welcome-only">
        <p class="lei-topbar-welcome">
            {{ $businessSettings->welcome_prefix }}
            <span>{{ $businessSettings->welcomeName() }}</span>
        </p>
    </div>

    <div class="lei-search-wrap" id="leiSearchWrap">
        <form class="lei-search" method="GET" action="{{ route('admin.search') }}" role="search" id="leiSearchForm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
            </svg>
            <input type="search"
                   name="q"
                   id="leiGlobalSearchInput"
                   placeholder="{{ $businessSettings->search_placeholder }}"
                   value="{{ request('q') }}"
                   autocomplete="off"
                   data-lei-global-search
                   data-search-url="{{ route('admin.search') }}"
                   data-suggest-url="{{ route('admin.search.suggest') }}">
        </form>
        <div class="lei-search-dropdown" id="leiSearchDropdown" hidden></div>
    </div>

    <div class="lei-topbar-actions">
        @if ($businessSettings->header_show_notifications)
            <a href="{{ route('admin.notifications.index') }}" class="lei-icon-btn lei-icon-btn--link" aria-label="Notifications" title="Notifications">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                @if ($businessSettings->header_notification_count > 0)
                    <span class="lei-icon-badge">{{ $businessSettings->header_notification_count > 9 ? '9+' : $businessSettings->header_notification_count }}</span>
                @endif
            </a>
        @endif

        <a href="{{ route('admin.business-settings.index') }}"
           class="lei-icon-btn lei-icon-btn--link {{ request()->routeIs('admin.business-settings.*') ? 'active' : '' }}"
           aria-label="Business Settings"
           title="Business Settings">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
        </a>

        <div class="lei-profile-menu" id="leiProfileMenu">
            <button type="button" class="lei-profile-trigger" id="leiProfileTrigger" aria-expanded="false" aria-haspopup="true">
                <img src="{{ auth()->user()->profileImageUrl() }}" alt="{{ auth()->user()->name }}" class="lei-profile-trigger-img">
                <svg class="lei-profile-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="lei-profile-dropdown" id="leiProfileDropdown" hidden>
                <div class="lei-profile-dropdown-head">
                    <img src="{{ auth()->user()->profileImageUrl() }}" alt="">
                    <div>
                        <strong>{{ auth()->user()->name }}</strong>
                        <span>{{ auth()->user()->email }}</span>
                        @if (auth()->user()->job_title)
                            <em>{{ auth()->user()->job_title }}</em>
                        @endif
                    </div>
                </div>
                <a href="{{ route('admin.profile.show') }}" class="lei-profile-dropdown-item {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    My Profile
                </a>
                <a href="{{ route('admin.business-settings.index') }}" class="lei-profile-dropdown-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    Business Settings
                </a>
                <form method="POST" action="{{ route('admin.logout') }}" class="lei-profile-logout-form">
                    @csrf
                    <button type="submit" class="lei-profile-dropdown-item lei-profile-dropdown-item--danger">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

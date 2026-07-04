<header class="lei-topbar lei-portal-topbar">
    <button type="button" class="lei-portal-menu-btn" id="leiPortalMenuBtn" aria-label="Open navigation menu" aria-expanded="false">
        <i class="fa-solid fa-bars" aria-hidden="true"></i>
    </button>

    <div class="lei-portal-search-wrap">
        <form class="lei-portal-search-form" method="GET" action="{{ route('applicant.applications.index') }}" role="search">
            <i class="fa-solid fa-magnifying-glass lei-portal-search-icon" aria-hidden="true"></i>
            <input type="search" name="q" placeholder="Search applications or LEIs..." value="{{ request('q') }}" autocomplete="off">
        </form>
    </div>

    <div class="lei-topbar-actions lei-portal-topbar-actions">
        <a href="{{ route('applicant.notifications.index') }}" class="lei-icon-btn lei-icon-btn--link" aria-label="Notifications" title="Notifications">
            <i class="fa-regular fa-bell"></i>
        </a>

        <div class="lei-profile-menu lei-portal-profile-menu" id="leiProfileMenu">
            <button type="button" class="lei-profile-trigger lei-portal-profile-trigger" id="leiProfileTrigger" aria-expanded="false" aria-haspopup="true">
                <img src="{{ auth()->user()->profileImageUrl() }}" alt="{{ auth()->user()->name }}" class="lei-profile-trigger-img">
                <div class="lei-portal-profile-text">
                    <strong>{{ auth()->user()->name }}</strong>
                    <span>{{ auth()->user()->organization_name ?: (auth()->user()->organization?->name ?? 'Applicant Account') }}</span>
                </div>
                <i class="fa-solid fa-chevron-down lei-portal-chevron" aria-hidden="true"></i>
            </button>
            <div class="lei-profile-dropdown" id="leiProfileDropdown" hidden>
                <div class="lei-profile-dropdown-head">
                    <img src="{{ auth()->user()->profileImageUrl() }}" alt="">
                    <div>
                        <strong>{{ auth()->user()->name }}</strong>
                        <span>{{ auth()->user()->email }}</span>
                    </div>
                </div>
                <a href="{{ route('applicant.profile.show') }}" class="lei-profile-dropdown-item">User Profile</a>
                <a href="{{ route('applicant.dashboard') }}" class="lei-profile-dropdown-item">My Entities</a>
                <form method="POST" action="{{ route('applicant.logout') }}" class="lei-profile-logout-form">
                    @csrf
                    <button type="submit" class="lei-profile-dropdown-item lei-profile-dropdown-item--danger">Log Out</button>
                </form>
            </div>
        </div>
    </div>
</header>
<div class="lei-portal-sidebar-backdrop" id="leiPortalSidebarBackdrop" hidden aria-hidden="true"></div>

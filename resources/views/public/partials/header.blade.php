@php
    $navItems = [
        ['label' => 'Home', 'route' => 'home'],
        ['label' => 'Bulk Management', 'route' => 'pricing'],
        ['label' => 'About Us', 'route' => 'about'],
        ['label' => 'FAQ', 'route' => 'faq'],
        ['label' => 'Contact Us', 'route' => 'contact'],
    ];
@endphp
<header class="lei-pub-header">
    <div class="lei-pub-container lei-pub-header-inner">
        <a href="{{ route('home') }}" class="lei-pub-logo">
            <img src="{{ asset('images/lei-logo-icon.svg') }}" alt="" class="lei-pub-logo-icon" width="40" height="40">
            <span class="lei-pub-logo-text">
                <strong>{{ $businessSettings->company_name }}</strong>
                <small>{{ $businessSettings->tagline }}</small>
            </span>
        </a>
        <nav class="lei-pub-nav">
            @foreach ($navItems as $item)
                <a href="{{ route($item['route']) }}"
                   class="{{ request()->routeIs($item['route']) ? 'active' : '' }}">{{ $item['label'] }}</a>
            @endforeach
        </nav>
        <div class="lei-pub-header-actions">
            <a href="{{ route('registry.search') }}" class="lei-pub-search" aria-label="Search LEI Registry"><i class="fa-solid fa-magnifying-glass"></i></a>
            @auth
                @if (auth()->user()->isApplicant())
                    <a href="{{ route('applicant.dashboard') }}" class="lei-pub-btn">Portal</a>
                @elseif (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="lei-pub-btn">Admin</a>
                @endif
            @else
                <a href="{{ route('register') }}" class="lei-pub-btn">Apply Now</a>
            @endauth
        </div>
    </div>
</header>

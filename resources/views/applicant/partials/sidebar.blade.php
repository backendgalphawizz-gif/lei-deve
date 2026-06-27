<aside class="lei-sidebar lei-portal-sidebar">
    <div class="lei-sidebar-brand">
        <div class="lei-brand-logo-box">
            <img src="{{ asset('images/lei-logo-icon.svg') }}" alt="LEI">
        </div>
        <div class="lei-brand-info">
            <span class="lei-brand-role">LEI</span>
            <span class="lei-brand-tagline">Applicant Portal</span>
        </div>
    </div>

    <ul class="lei-sidebar-nav">
        @php $section = null; @endphp
        @foreach (\App\Support\ApplicantNav::items() as $item)
            @if (($item['section'] ?? null) !== $section)
                @php $section = $item['section'] ?? null; @endphp
                @if ($section === 'support')
                    <li class="lei-portal-nav-label">Support</li>
                @endif
            @endif
            <li>
                <a href="{{ \App\Support\ApplicantNav::url($item) }}"
                   class="{{ \App\Support\ApplicantNav::isActive($item) ? 'active' : '' }}">
                    <i class="fa-solid {{ $item['icon'] }}"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    <div class="lei-sidebar-footer">
        <form method="POST" action="{{ route('applicant.logout') }}">
            @csrf
            <button type="submit" class="lei-btn-logout">
                <i class="fa-solid fa-right-from-bracket"></i>
                Log Out
            </button>
        </form>
    </div>
</aside>

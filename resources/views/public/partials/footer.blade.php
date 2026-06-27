<footer class="lei-pub-footer">
    <div class="lei-pub-container lei-pub-footer-grid">
        <div class="lei-pub-footer-brand">
            <a href="{{ route('home') }}" class="lei-pub-footer-brand-link">
                <img src="{{ asset('images/lei-logo-icon.svg') }}" alt="" class="lei-pub-footer-logo-icon" width="36" height="36">
                <span class="lei-pub-footer-logo-text">
                    <strong>{{ $businessSettings->company_name }}</strong>
                    <small>{{ $businessSettings->tagline }}</small>
                </span>
            </a>
            <p>{{ $businessSettings->legal_name }} is a global Local Operating Unit partner dedicated to enhancing transparency in international financial markets through superior data management.</p>
        </div>
        <div>
            <h4>Services</h4>
            <ul>
                <li><a href="{{ route('pricing') }}">Registration</a></li>
                <li><a href="{{ route('pricing') }}#renewal">Renewal</a></li>
                <li><a href="{{ route('pricing') }}#renewal">Transfer</a></li>
            </ul>
        </div>
        <div>
            <h4>Company</h4>
            <ul>
                <li><a href="{{ route('about') }}">About Us</a></li>
                @foreach ($footerPages ?? [] as $page)
                    @if ($page->slug !== 'privacy-policy' && $page->slug !== 'terms-of-service')
                        @continue
                    @endif
                @endforeach
                <li><a href="{{ route('about') }}">Careers</a></li>
            </ul>
        </div>
        <div>
            <h4>Support</h4>
            <ul>
                <li><a href="{{ route('faq') }}">FAQ</a></li>
                <li><a href="{{ route('contact') }}">Contact</a></li>
                <li><a href="{{ route('applicant.login') }}">Portal Login</a></li>
            </ul>
        </div>
    </div>
    <div class="lei-pub-container lei-pub-footer-bottom">
        <div>
            @foreach ($footerPages ?? [] as $page)
                <a href="{{ route('pages.show', $page->slug) }}">{{ $page->title }}</a>
            @endforeach
        </div>
        <p>{{ $businessSettings->copyright_text }}</p>
    </div>
</footer>

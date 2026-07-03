@php
    $searchQuery = $query ?? '';
    $searchType = $type ?? request('type', 'all');
    $compact = $compact ?? false;
    $suggestListId = 'lei-reg-suggest-' . \Illuminate\Support\Str::random(8);
@endphp
<form action="{{ route('registry.search') }}" method="GET"
      class="lei-reg-search-form {{ $compact ? 'lei-reg-search-form--compact' : '' }}"
      role="search"
      data-suggest-url="{{ route('registry.suggest') }}"
      data-register-url="{{ route('pricing') }}"
      data-pricing-url="{{ route('pricing') }}">
    <div class="lei-reg-search-types" role="radiogroup" aria-label="Search by">
        <label class="lei-reg-search-type {{ $searchType === 'all' ? 'active' : '' }}">
            <input type="radio" name="type" value="all" {{ $searchType === 'all' ? 'checked' : '' }}>
            <span>All</span>
        </label>
        <label class="lei-reg-search-type {{ $searchType === 'lei' ? 'active' : '' }}">
            <input type="radio" name="type" value="lei" {{ $searchType === 'lei' ? 'checked' : '' }}>
            <span>LEI Code</span>
        </label>
        <label class="lei-reg-search-type {{ $searchType === 'company' ? 'active' : '' }}">
            <input type="radio" name="type" value="company" {{ $searchType === 'company' ? 'checked' : '' }}>
            <span>Company Name</span>
        </label>
        <label class="lei-reg-search-type {{ $searchType === 'registration' ? 'active' : '' }}">
            <input type="radio" name="type" value="registration" {{ $searchType === 'registration' ? 'checked' : '' }}>
            <span>Registration No.</span>
        </label>
    </div>
    <div class="lei-reg-search-row">
        <div class="lei-reg-search-combobox">
            <div class="lei-reg-search-input-wrap">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input type="search"
                       name="q"
                       class="lei-reg-search-input"
                       value="{{ $searchQuery }}"
                       placeholder="Enter LEI code, company name, or registration / CIN number…"
                       autocomplete="off"
                       required
                       minlength="2"
                       aria-label="Registry search"
                       aria-expanded="false"
                       aria-controls="{{ $suggestListId }}"
                       aria-autocomplete="list">
                <button type="button" class="lei-reg-search-clear" aria-label="Clear search" hidden>&times;</button>
            </div>
            <div id="{{ $suggestListId }}" class="lei-reg-suggest" role="listbox" hidden></div>
        </div>
        <button type="submit" class="lei-pub-btn gold">Search Registry</button>
    </div>
    @unless ($compact)
        <p class="lei-reg-search-hint">Type at least 2 characters for suggestions from our registry and GLEIF global index.</p>
    @endunless
</form>

@once
    @push('scripts')
        <script src="{{ asset('js/lei-registry-search.js') }}?v=3" defer></script>
    @endpush
@endonce

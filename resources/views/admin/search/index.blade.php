@extends('admin.layouts.app')

@section('title', 'Search Results')
@section('body_class', 'lei-page-search')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-global.css') }}?v=1">
@endpush

@section('breadcrumbs')
    @include('admin.partials.breadcrumbs', ['current' => 'Search'])
@endsection

@section('content')
<div class="lei-search-page">
    <div class="lei-search-page-head">
        <h2>Search Results</h2>
        @if ($query)
            <p>{{ $total }} result{{ $total === 1 ? '' : 's' }} for <strong>“{{ $query }}”</strong></p>
        @else
            <p>Type a keyword in the global search bar above (minimum 2 characters).</p>
        @endif
    </div>

    @if ($query && $total === 0)
        <div class="lei-search-empty">
            <p>No matches found. Try application codes, user emails, ticket IDs, or page titles.</p>
        </div>
    @endif

    @foreach ($groups as $group)
        <section class="lei-search-group">
            <h3>{{ $group['label'] }} <span>({{ $group['count'] }})</span></h3>
            <div class="lei-search-results">
                @foreach ($group['items'] as $item)
                    <a href="{{ $item['url'] }}" class="lei-search-result">
                        <span class="lei-search-result-type">{{ strtoupper($item['type']) }}</span>
                        <div>
                            <strong>{{ $item['title'] }}</strong>
                            <span>{{ $item['meta'] }}</span>
                        </div>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach
</div>
@endsection

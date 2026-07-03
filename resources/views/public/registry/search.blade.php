@extends('public.layouts.app')

@section('title', 'LEI Registry Search')
@section('body_class', 'lei-page-registry-search')

@section('content')
<section class="lei-reg-hero">
    <div class="lei-pub-container">
        <span class="lei-pub-eyebrow">PUBLIC REGISTRY</span>
        <h1>Search LEI Records</h1>
        <p class="lei-pub-lead">Search our LOU registry and the GLEIF global LEI index — by company name, registration number, or 20-character LEI code.</p>
        @include('public.registry.partials.search-form', ['query' => $query, 'type' => $type])
    </div>
</section>

<section class="lei-pub-section lei-pub-muted">
    <div class="lei-pub-container">
        @if ($query === '')
            <div class="lei-reg-empty">
                <i class="fa-solid fa-database" aria-hidden="true"></i>
                <h2>Start your search</h2>
                <p>Enter at least 2 characters to find matching legal entities in our registry and the GLEIF global index.</p>
            </div>
        @elseif ($results->isEmpty())
            <div class="lei-reg-empty">
                <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>
                <h2>No records found</h2>
                <p>No LEI records match <strong>{{ $query }}</strong> in our registry or GLEIF. Try a different spelling, search type, or partial LEI code.</p>
            </div>
            <div style="margin-top:24px;">
                @include('public.registry.partials.register-cta')
            </div>
        @else
            <div class="lei-reg-results-head">
                <h2>{{ $results->total() }} {{ Str::plural('result', $results->total()) }} for “{{ $query }}”</h2>
                <span class="lei-reg-results-meta">Our registry + GLEIF global index</span>
            </div>
            <div class="lei-reg-table-wrap">
                <table class="lei-reg-table">
                    <thead>
                        <tr>
                            <th>Source</th>
                            <th>LEI Code</th>
                            <th>Legal Entity Name</th>
                            <th>Registration No.</th>
                            <th>Country</th>
                            <th>Status</th>
                            <th>Valid Until</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($results as $record)
                            <tr>
                                <td><span class="lei-reg-source lei-reg-source--{{ $record['source'] }}">{{ $record['source_label'] }}</span></td>
                                <td class="lei-reg-mono">{{ $record['lei_number'] }}</td>
                                <td><strong>{{ $record['entity_name'] }}</strong></td>
                                <td class="lei-reg-mono">{{ $record['registration_number'] ?? '—' }}</td>
                                <td>{{ $record['country'] }}</td>
                                <td><span class="lei-reg-status lei-reg-status--{{ $record['status_tone'] }}">{{ $record['status_label'] }}</span></td>
                                <td>{{ $record['expiry_label'] ?? '—' }}</td>
                                <td class="lei-reg-actions">
                                    <a href="{{ $record['url'] }}" class="lei-reg-view-link">
                                        View <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                    </a>
                                    @if ($record['can_renew'] ?? false)
                                        <a href="{{ $record['renew_url'] }}" class="lei-reg-renew-link">Renew</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($results->hasPages())
                <div class="lei-reg-pager">
                    @if ($results->onFirstPage())
                        <span class="disabled">Previous</span>
                    @else
                        <a href="{{ $results->previousPageUrl() }}">Previous</a>
                    @endif
                    <span>Page {{ $results->currentPage() }} of {{ $results->lastPage() }}</span>
                    @if ($results->hasMorePages())
                        <a href="{{ $results->nextPageUrl() }}">Next</a>
                    @else
                        <span class="disabled">Next</span>
                    @endif
                </div>
            @endif
        @endif
    </div>
</section>
@endsection

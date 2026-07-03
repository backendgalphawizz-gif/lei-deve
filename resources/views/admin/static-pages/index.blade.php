@extends('admin.layouts.app')

@section('title', 'Static Pages')
@section('body_class', 'lei-page-static-pages')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-static-pages.css') }}?v=1">
@endpush

@section('breadcrumbs')
    @include('admin.partials.breadcrumbs', ['current' => 'Static Pages'])
@endsection

@section('content')
<div class="lei-sp-page">
    <div class="lei-sp-head">
        <div>
            <h2>Static Pages</h2>
            <p>Manage legal, help, and marketing content shown on the public registry site.</p>
        </div>
        <a href="{{ route('admin.static-pages.create') }}" class="lei-sp-btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Create Page
        </a>
    </div>

    <div class="lei-sp-stats">
        <div class="lei-sp-stat">
            <span class="lei-sp-stat-label">Total Pages</span>
            <strong>{{ $stats['total'] }}</strong>
        </div>
        <div class="lei-sp-stat lei-sp-stat--green">
            <span class="lei-sp-stat-label">Published</span>
            <strong>{{ $stats['published'] }}</strong>
        </div>
        <div class="lei-sp-stat lei-sp-stat--amber">
            <span class="lei-sp-stat-label">Drafts</span>
            <strong>{{ $stats['draft'] }}</strong>
        </div>
    </div>

    <div class="lei-sp-toolbar-card">
        <form method="GET" action="{{ route('admin.static-pages.index') }}" class="lei-sp-filters">
            <div class="lei-sp-search">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                </svg>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search title or slug...">
            </div>
            <label>
                <span>Status</span>
                <select name="status" onchange="this.form.submit()">
                    <option value="">All</option>
                    @foreach ($statuses as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>Type</span>
                <select name="type" onchange="this.form.submit()">
                    <option value="">All</option>
                    @foreach ($pageTypes as $key => $label)
                        <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            @if (request()->hasAny(['q', 'status', 'type']))
                <a href="{{ route('admin.static-pages.index') }}" class="lei-sp-clear">Clear</a>
            @endif
        </form>
    </div>

    <div class="lei-sp-table-card">
        <div class="lei-sp-table-head">
            <div class="lei-sp-th lei-sp-th--title">TITLE</div>
            <div class="lei-sp-th">SLUG</div>
            <div class="lei-sp-th">TYPE</div>
            <div class="lei-sp-th">STATUS</div>
            <div class="lei-sp-th">UPDATED</div>
            <div class="lei-sp-th lei-sp-th--actions">ACTIONS</div>
        </div>
        <div class="lei-sp-table-body">
            @forelse ($pages as $item)
                <div class="lei-sp-row">
                    <div class="lei-sp-td lei-sp-td--title">
                        <strong>{{ $item->title }}</strong>
                        @if ($item->is_in_footer)
                            <span class="lei-sp-footer-badge">Footer</span>
                        @endif
                    </div>
                    <div class="lei-sp-td"><code>/{{ $item->slug }}</code></div>
                    <div class="lei-sp-td">{{ $item->page_type_label }}</div>
                    <div class="lei-sp-td">
                        <span class="lei-sp-status lei-sp-status--{{ $item->status }}">{{ $item->status_label }}</span>
                    </div>
                    <div class="lei-sp-td">{{ $item->updated_label }}</div>
                    <div class="lei-sp-td lei-sp-td--actions">
                        @include('admin.partials.icon-actions', [
                            'externalUrl' => route('pages.show', $item->slug),
                            'editUrl' => route('admin.static-pages.edit', $item),
                            'deleteUrl' => route('admin.static-pages.destroy', $item),
                            'deleteConfirm' => 'Delete “'.$item->title.'”? This cannot be undone.',
                            'deleteTitle' => 'Delete Page',
                        ])
                    </div>
                </div>
            @empty
                <div class="lei-sp-empty">
                    No static pages found.
                    <a href="{{ route('admin.static-pages.create') }}">Create your first page</a>
                    or run <code>php artisan db:seed --class=StaticPageSeeder</code>.
                </div>
            @endforelse
        </div>

        @if ($pages->hasPages())
            <div class="lei-sp-table-footer">
                <span>Showing {{ $pages->firstItem() }}–{{ $pages->lastItem() }} of {{ $pages->total() }}</span>
                <div class="lei-sp-pager">
                    @if ($pages->onFirstPage())
                        <span class="disabled">Previous</span>
                    @else
                        <a href="{{ $pages->previousPageUrl() }}">Previous</a>
                    @endif
                    @for ($p = max(1, $pages->currentPage() - 1); $p <= min($pages->lastPage(), $pages->currentPage() + 2); $p++)
                        @if ($p == $pages->currentPage())
                            <span class="active">{{ $p }}</span>
                        @else
                            <a href="{{ $pages->url($p) }}">{{ $p }}</a>
                        @endif
                    @endfor
                    @if ($pages->hasMorePages())
                        <a href="{{ $pages->nextPageUrl() }}">Next</a>
                    @else
                        <span class="disabled">Next</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/lei-static-pages.js') }}?v=1"></script>
@endpush

@extends('admin.layouts.app')

@section('title', 'Application Management')
@section('body_class', 'lei-page-applications')

@section('content')
<div class="lei-app-page" data-show-url="{{ rtrim(config('app.url'), '/') }}/admin/applications/__ID__"
    data-action-url="{{ rtrim(config('app.url'), '/') }}/admin/applications/__ID__/action"
    data-export-url="{{ route('admin.applications.export', request()->query()) }}">

    <div id="leiAppToast" class="lei-app-toast" hidden></div>

    <div class="lei-app-metrics" id="leiAppMetrics">
        @foreach ($stats as $stat)
        <a href="{{ route('admin.applications.index', array_merge(request()->except('page'), ['status' => $stat->key])) }}"
            class="lei-app-metric-card {{ request('status') === $stat->key ? 'active' : '' }}">
            <div class="lei-app-metric-top">
                <span class="lei-app-metric-label">{{ strtoupper($stat->label) }}</span>

            </div>
            <div class="lei-app-metric-value" data-stat-key="{{ $stat->key }}">{{
                \App\Support\CurrencyFormatter::formatNumber($stat->value) }} @if ($stat->icon === 'check')
                <span class="lei-app-metric-icon lei-app-metric-icon--ok" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                </span>
                @elseif ($stat->icon === 'x')
                <span class="lei-app-metric-icon lei-app-metric-icon--bad" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </span>
                @elseif ($stat->badge)
                <span class="lei-app-metric-badge lei-app-metric-badge--{{ $stat->badge_tone }}">{{ $stat->badge
                    }}</span>
                @endif
            </div>
        </a>
        @endforeach
    </div>

    <div class="lei-app-filters-card">
        <form method="GET" action="{{ route('admin.applications.index') }}" class="lei-app-filters"
            id="leiAppFilterForm">
            <input type="hidden" name="selected" id="filterSelected" value="{{ $selected?->application_code }}">
            @if (request('q'))
            <input type="hidden" name="q" value="{{ request('q') }}">
            @endif
            <div class="lei-app-filter-field">
                <label>Date Range</label>
                <div class="lei-app-filter-input lei-app-filter-input--icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                    <input type="text" name="date_range"
                        value="{{ request('date_range', 'Oct 01, 2023 - Oct 31, 2023') }}">
                </div>
            </div>
            <div class="lei-app-filter-field">
                <label>Status</label>
                <select name="status" data-auto-filter>
                    <option value="">All Statuses</option>
                    <option value="new" @selected(request('status')==='new' )>New</option>
                    <option value="pending" @selected(request('status')==='pending' )>Pending</option>
                    <option value="under_review" @selected(request('status')==='under_review' )>Under Review</option>
                    <option value="clarification" @selected(request('status')==='clarification' )>Clarification</option>
                    <option value="approved" @selected(request('status')==='approved' )>Approved</option>
                    <option value="rejected" @selected(request('status')==='rejected' )>Rejected</option>
                </select>
            </div>
            <div class="lei-app-filter-field">
                <label>Assigned Team</label>
                <select name="team" data-auto-filter>
                    <option value="">All Teams</option>
                    @foreach ($teams as $team)
                    <option value="{{ $team }}" @selected(request('team')===$team)>{{ $team }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lei-app-filter-field lei-app-filter-field--priority">
                <label>Priority</label>
                <input type="hidden" name="priority" value="{{ request('priority', '') }}">
                <div class="lei-app-priority-group">
                    <button type="button" data-priority="high"
                        class="lei-app-priority-btn high {{ request('priority') === 'high' ? 'active' : '' }}">HIGH</button>
                    <button type="button" data-priority="med"
                        class="lei-app-priority-btn {{ request('priority') === 'med' ? 'active' : '' }}">MED</button>
                    <button type="button" data-priority="low"
                        class="lei-app-priority-btn {{ request('priority') === 'low' ? 'active' : '' }}">LOW</button>
                    <button type="button" data-priority=""
                        class="lei-app-priority-btn {{ !request('priority') ? 'active' : '' }}">ALL</button>
                </div>
            </div>
            <button type="submit" class="lei-app-btn-apply">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
                </svg>
                Apply Filters
            </button>
            @if (request()->hasAny(['status', 'team', 'priority', 'date_range', 'q']))
            <a href="{{ route('admin.applications.index') }}" class="lei-app-clear-filters">Clear</a>
            @endif
        </form>
    </div>

    <div class="lei-app-workspace">
        <div class="lei-app-pool-card">
            <div class="lei-app-pool-head">
                <div class="lei-app-pool-title">
                    <h2>Applications Pool</h2>
                    <span class="lei-app-pool-badge" id="leiActiveBadge">{{ $applications->total() }} TOTAL</span>
                </div>
                <div class="lei-app-pool-actions">
                    <a href="{{ route('admin.users.create') }}" class="lei-app-icon-btn" title="Add User">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <line x1="19" y1="8" x2="19" y2="14" />
                            <line x1="22" y1="11" x2="16" y2="11" />
                        </svg>
                    </a>
                    <a href="{{ route('admin.applications.export', request()->query()) }}" class="lei-app-icon-btn"
                        title="Download CSV" id="leiExportBtn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" y1="15" x2="12" y2="3" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="lei-app-table-head">
                <div class="lei-app-th lei-app-th--check">
                    <input type="checkbox" id="leiSelectAll" aria-label="Select all">
                </div>
                <div class="lei-app-th">ID</div>
                <div class="lei-app-th lei-app-th--entity">Entity Name</div>
                <div class="lei-app-th">Date</div>
                <div class="lei-app-th">Status</div>
                <div class="lei-app-th">Team</div>
            </div>

            <div class="lei-app-table-body" id="leiAppTableBody">
                @forelse ($applications as $app)
                <div class="lei-app-row {{ $selected && $selected->id === $app->id ? 'active' : '' }}" role="button"
                    tabindex="0" data-app-id="{{ $app->id }}" data-app-code="{{ $app->application_code }}">
                    <div class="lei-app-td lei-app-td--check" data-stop-prop>
                        <input type="checkbox" class="lei-app-row-check"
                            aria-label="Select {{ $app->application_code }}">
                    </div>
                    <div class="lei-app-td lei-app-td--id">{{ $app->application_code }}</div>
                    <div class="lei-app-td lei-app-td--entity">{{ $app->entity_name }}</div>
                    <div class="lei-app-td lei-app-td--date">{{ $businessSettings->formatDate($app->submitted_on) }}
                    </div>
                    <div class="lei-app-td lei-app-td--status">
                        <span class="lei-app-status lei-app-status--{{ $app->status_tone }}" data-status-pill>
                            <span class="dot"></span><span data-status-label>{{ $app->status_label }}</span>
                        </span>
                    </div>
                    <div class="lei-app-td lei-app-td--team">{{ $app->assigned_team }}</div>
                </div>
                @empty
                <div class="lei-app-empty">No applications found. Adjust filters or run database seed.</div>
                @endforelse
            </div>

            <div class="lei-app-pool-footer">
                <span id="leiPoolCount">Showing {{ $applications->firstItem() ?? 0 }} to {{ $applications->lastItem() ??
                    0 }} of {{ $applications->total() }} applications</span>
                <div class="lei-app-pager">
                    @if ($applications->onFirstPage())
                    <span class="disabled">Prev</span>
                    @else
                    <a href="{{ $applications->previousPageUrl() }}">Prev</a>
                    @endif
                    @for ($p = max(1, $applications->currentPage() - 1); $p <= min($applications->lastPage(),
                        $applications->currentPage() + 1); $p++)
                        @if ($p == $applications->currentPage())
                        <span class="active">{{ $p }}</span>
                        @else
                        <a href="{{ $applications->url($p) }}">{{ $p }}</a>
                        @endif
                        @endfor
                        @if ($applications->hasMorePages())
                        <a href="{{ $applications->nextPageUrl() }}">Next</a>
                        @else
                        <span class="disabled">Next</span>
                        @endif
                </div>
            </div>
        </div>

        <aside class="lei-app-detail-card" id="leiAppDetailPanel">
            @if ($selected)
            @include('admin.applications.partials.detail', ['application' => $selected])
            @else
            <div class="lei-app-detail-empty">Select an application to view details.</div>
            @endif
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/lei-applications.js') }}?v=3"></script>
@endpush
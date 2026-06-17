@extends('admin.layouts.app')

@section('title', 'Support')
@section('body_class', 'lei-page-support')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-support.css') }}?v=3">
@endpush

@section('content')
<div class="lei-support-page"
     data-ticket-url="{{ rtrim(config('app.url'), '/') }}/admin/support/tickets/__ID__"
     data-store-ticket-url="{{ route('admin.support.tickets.store') }}"
     data-store-category-url="{{ route('admin.support.categories.store') }}"
     data-update-category-url="{{ rtrim(config('app.url'), '/') }}/admin/support/categories/__ID__"
     data-message-url="{{ $selected ? route('admin.support.message', $selected) : '' }}"
     data-note-url="{{ $selected ? route('admin.support.note', $selected) : '' }}"
     data-action-url="{{ $selected ? route('admin.support.action', $selected) : '' }}"
     data-index-url="{{ route('admin.support.index') }}"
     data-admin-initials="{{ $adminInitials }}">

    <div id="leiSupportToast" class="lei-support-toast" hidden></div>

    @if ($statCards->isEmpty())
        <div class="lei-support-empty">Run <code>php artisan db:seed --class=SupportManagementSeeder</code></div>
    @else

    <div class="lei-support-stats-row" id="leiSupportStatsRow">
        @foreach ($statCards as $stat)
            <div class="lei-support-stat-card" data-stat-key="{{ $stat->stat_key }}">
                <div class="lei-support-stat-icon lei-support-stat-icon--{{ $stat->icon_tone }}">
                    @if ($stat->stat_key === 'total_active')
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    @elseif ($stat->stat_key === 'sla_health')
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    @elseif ($stat->stat_key === 'avg_resolution')
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    @else
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    @endif
                </div>
                <div class="lei-support-stat-body">
                    <span class="lei-support-stat-label">{{ $stat->label }}</span>
                    <strong class="lei-support-stat-value">{{ $stat->value }}</strong>
                </div>
                @if ($stat->badge_text)
                    <span class="lei-support-stat-badge lei-support-stat-badge--{{ $stat->badge_tone }}">{{ $stat->badge_text }}</span>
                @endif
            </div>
        @endforeach
    </div>

    <div class="lei-support-workspace">
        <div class="lei-support-main-col">
            <div class="lei-support-card lei-support-queue-card">
                <div class="lei-support-card-head">
                    <h2>Active Ticket Queue</h2>
                    <div class="lei-support-card-actions">
                        <button type="button" class="lei-support-btn-filter" id="leiSupportFilterBtn" aria-expanded="false">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                            Filter
                        </button>
                        <button type="button" class="lei-support-btn-primary" id="leiSupportNewTicket">+ New Ticket</button>
                    </div>
                </div>
<!-- 
                <form class="lei-support-filter-panel" id="leiSupportFilterPanel" method="GET" action="{{ route('admin.support.index') }}" hidden>
                    <input type="hidden" name="ticket" value="{{ $selected?->id }}">
                    <label>
                        <span>Status</span>
                        <select name="status">
                            <option value="active" {{ $filters['status'] === 'active' ? 'selected' : '' }}>Active (not closed)</option>
                            <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>All</option>
                            <option value="open" {{ $filters['status'] === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="progress" {{ $filters['status'] === 'progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="escalated" {{ $filters['status'] === 'escalated' ? 'selected' : '' }}>Escalated</option>
                            <option value="closed" {{ $filters['status'] === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </label>
                    <label>
                        <span>Priority</span>
                        <select name="priority">
                            <option value="all" {{ $filters['priority'] === 'all' ? 'selected' : '' }}>All</option>
                            <option value="high" {{ $filters['priority'] === 'high' ? 'selected' : '' }}>High</option>
                            <option value="medium" {{ $filters['priority'] === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="low" {{ $filters['priority'] === 'low' ? 'selected' : '' }}>Low</option>
                        </select>
                    </label>
                    <label>
                        <span>Category</span>
                        <select name="category">
                            <option value="all" {{ $filters['category'] === 'all' ? 'selected' : '' }}>All</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->name }}" {{ $filters['category'] === $cat->name ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="lei-support-filter-search">
                        <span>Search</span>
                        <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="ID, entity, title...">
                    </label>
                    <button type="submit" class="lei-support-btn-apply">Apply</button>
                    <a href="{{ route('admin.support.index', ['ticket' => $selected?->id]) }}" class="lei-support-btn-clear">Clear</a>
                </form> -->

                <div class="lei-support-table" id="leiSupportTable">
                    <div class="lei-support-row lei-support-row--head">
                        <span>Ticket ID</span>
                        <span>User/Entity</span>
                        <span>Category</span>
                        <span>Priority</span>
                        <span>Status</span>
                    </div>
                    @forelse ($tickets as $ticket)
                        <a href="{{ route('admin.support.index', array_merge(request()->query(), ['ticket' => $ticket->id, 'page' => $paginator->currentPage()])) }}"
                           class="lei-support-row js-support-row {{ $selected && $selected->id === $ticket->id ? 'lei-support-row--active' : '' }}"
                           data-ticket-id="{{ $ticket->id }}">
                            <span class="lei-support-ticket-id">{{ $ticket->ticket_code }}</span>
                            <span class="lei-support-entity">
                                <strong>{{ $ticket->user_entity }}</strong>
                                @if ($ticket->contact_email)
                                    <small>{{ $ticket->contact_email }}</small>
                                @endif
                            </span>
                            <span><span class="lei-support-cat-pill">{{ $ticket->category }}</span></span>
                            <span class="lei-support-priority">
                                <i class="lei-support-dot lei-support-dot--{{ $ticket->priority_tone }}"></i>{{ $ticket->priority }}
                            </span>
                            <span><span class="lei-support-status lei-support-status--{{ $ticket->status_tone }}">{{ $ticket->status }}</span></span>
                        </a>
                    @empty
                        <div class="lei-support-row lei-support-row--empty">No tickets match your filters.</div>
                    @endforelse
                </div>
                <div class="lei-support-pagination">
                    <span>Showing {{ $paginator->firstItem() ?? 0 }}-{{ $paginator->lastItem() ?? 0 }} of {{ \App\Support\CurrencyFormatter::formatNumber($grandTotal) }} tickets</span>
                    <div class="lei-support-pager">
                        @if ($paginator->onFirstPage())
                            <span class="lei-support-pager-btn lei-support-pager-btn--disabled">&lsaquo;</span>
                        @else
                            <a href="{{ $paginator->previousPageUrl() }}" class="lei-support-pager-btn">&lsaquo;</a>
                        @endif
                        @if ($paginator->hasMorePages())
                            <a href="{{ $paginator->nextPageUrl() }}" class="lei-support-pager-btn">&rsaquo;</a>
                        @else
                            <span class="lei-support-pager-btn lei-support-pager-btn--disabled">&rsaquo;</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="lei-support-card lei-support-categories-card">
                <div class="lei-support-card-head">
                    <h2>Category Management</h2>
                    <button type="button" class="lei-support-link-gold" id="leiSupportNewCategory">+ New Category</button>
                </div>
                <div class="lei-support-cat-grid" id="leiSupportCatGrid">
                    @foreach ($categories as $cat)
                        <div class="lei-support-cat-item" data-category-id="{{ $cat->id }}">
                            <button type="button" class="lei-support-cat-gear js-cat-edit" data-id="{{ $cat->id }}" data-name="{{ $cat->name }}" aria-label="Edit {{ $cat->name }}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                            </button>
                            <strong>{{ $cat->name }}</strong>
                            <span class="js-cat-count">{{ $cat->ticket_count_label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div id="leiSupportDetailWrap">
            @if ($selected)
                @include('admin.support.partials.detail', ['selected' => $selected, 'adminInitials' => $adminInitials, 'lastActivity' => $lastActivity])
            @else
                <aside class="lei-support-detail lei-support-detail--empty">
                    <p>Select a ticket to view details.</p>
                </aside>
            @endif
        </div>
    </div>

    @endif
</div>

<div class="lei-support-modal-overlay" id="leiSupportModalTicket" hidden>
    <div class="lei-support-modal">
        <h3>New Support Ticket</h3>
        <form id="leiSupportTicketForm">
            <label>User / Entity<input type="text" name="user_entity" required maxlength="128"></label>
            <label>Category
                <select name="category" required>
                    @foreach ($categories ?? [] as $cat)
                        <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>Priority
                <select name="priority_tone" required>
                    <option value="high">High</option>
                    <option value="medium" selected>Medium</option>
                    <option value="low">Low</option>
                </select>
            </label>
            <label>Subject<input type="text" name="title" required maxlength="255"></label>
            <div class="lei-support-modal-actions">
                <button type="button" class="lei-support-btn-cancel js-modal-close">Cancel</button>
                <button type="submit" class="lei-support-btn-primary">Create Ticket</button>
            </div>
        </form>
    </div>
</div>

<div class="lei-support-modal-overlay" id="leiSupportModalCategory" hidden>
    <div class="lei-support-modal">
        <h3 id="leiSupportCatModalTitle">New Category</h3>
        <form id="leiSupportCategoryForm">
            <input type="hidden" name="category_id" value="">
            <label>Name<input type="text" name="name" required maxlength="64"></label>
            <div class="lei-support-modal-actions">
                <button type="button" class="lei-support-btn-cancel js-modal-close">Cancel</button>
                <button type="submit" class="lei-support-btn-primary" id="leiSupportCatSubmit">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/lei-support.js') }}?v=3"></script>
@endpush

@extends('admin.layouts.app')

@section('title', 'User Management')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Registry</a>
    <span> / </span>
    <a href="{{ route('admin.dashboard') }}">System Overview</a>
    <span> / </span>
    <span>User Management</span>
@endsection

@section('content')
<div class="lei-users-header">
    <div class="lei-users-header-row">
        <div>
            <h2>User Management</h2>
            <p>Monitor, configure, and secure global administrative accounts.</p>
        </div>
        <div class="lei-header-actions">
            <button type="button"
                    class="lei-btn-filter"
                    data-lei-advanced-filters-toggle
                    aria-expanded="false"
                    aria-controls="leiAdvancedFilters">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                Advanced Filters
            </button>
            <a href="{{ route('admin.users.create') }}" class="lei-btn-create">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Create New User
            </a>
        </div>
    </div>
</div>

<div id="leiAdvancedFilters" class="lei-advanced-filters" data-lei-advanced-filters hidden>
    <form method="GET" action="{{ route('admin.users.index') }}" data-lei-users-filter-form>
        @if (request('q'))
            <input type="hidden" name="q" value="{{ request('q') }}">
        @endif
        @if (request('organization'))
            <input type="hidden" name="organization" value="{{ request('organization') }}">
        @endif
        <label>
            <span>Account Status</span>
            <select name="status" onchange="this.form.submit()">
                <option value="">All statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="locked" @selected(request('status') === 'locked')>Locked</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
            </select>
        </label>
        <label>
            <span>MFA</span>
            <select name="mfa" onchange="this.form.submit()">
                <option value="">All MFA states</option>
                <option value="enabled" @selected(request('mfa') === 'enabled')>Enabled</option>
                <option value="pending" @selected(request('mfa') === 'pending')>Pending</option>
                <option value="warning" @selected(request('mfa') === 'warning')>Warning</option>
                <option value="disabled" @selected(request('mfa') === 'disabled')>Disabled</option>
            </select>
        </label>
        @if (request()->hasAny(['status', 'mfa']))
            <a href="{{ route('admin.users.index', request()->only('q', 'organization')) }}" class="lei-clear-filters">Clear filters</a>
        @endif
    </form>
</div>

<div class="lei-users-layout">
    <aside class="lei-users-side">
        <div class="lei-health-stats-card">
            <h4>System Health</h4>
            @foreach ($stats as $stat)
                <div class="lei-mini-stat lei-accent-{{ $stat->accent }}">
                    <div class="lei-mini-stat-inner">
                        <div class="label">{{ $stat->label }}</div>
                        <div class="value-row">
                            <span class="value">{{ $stat->value_display }}</span>
                        </div>
                        @if ($stat->badge)
                            <div class="lei-mini-badge {{ $stat->badge_tone }}">{{ $stat->badge }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="lei-rbac-card">
            <p>Modify global RBAC permissions and organization hierarchy mapping for institutional partners.</p>
            <button type="button" class="lei-btn-gold">Manage Permissions</button>
        </div>
    </aside>

    <div class="lei-users-table-card">
        <form method="GET" action="{{ route('admin.users.index') }}" class="lei-table-toolbar" data-lei-users-filter-form>
            @if (request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            @if (request('mfa'))
                <input type="hidden" name="mfa" value="{{ request('mfa') }}">
            @endif
            <div class="lei-table-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                </svg>
                <input type="search"
                       name="q"
                       value="{{ request('q') }}"
                       placeholder="Filter by name, org..."
                       data-lei-user-search
                       autocomplete="off">
            </div>
            <select name="organization" class="lei-table-select" onchange="this.form.submit()">
                <option value="">All Organizations</option>
                @foreach ($organizations as $org)
                    <option value="{{ $org->id }}" @selected(request('organization') == $org->id)>{{ $org->name }}</option>
                @endforeach
            </select>
            <div class="lei-table-actions">
                <button type="button" class="lei-table-icon-btn" title="Export (coming soon)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                </button>
                <button type="button" class="lei-table-icon-btn" title="Table settings">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                    </svg>
                </button>
            </div>
        </form>

        <div class="lei-table-scroll">
            <table class="lei-users-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Organization</th>
                        <th>Status</th>
                        <th>MFA</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="lei-user-cell">
                                    <div class="lei-user-avatar" style="background-color: {{ $user->avatar_color }}">
                                        {{ $user->initials }}
                                    </div>
                                    <div>
                                        <span class="lei-user-name">{{ $user->name }}</span>
                                        <span class="lei-user-email">{{ $user->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->adminRole?->name ?? '—' }}</td>
                            <td>{{ $user->organization?->name ?? '—' }}</td>
                            <td>
                                <span class="lei-status-pill {{ $user->account_status }}">
                                    {{ strtoupper($user->account_status) }}
                                </span>
                            </td>
                            <td>
                                @if ($user->mfa_status === 'enabled')
                                    <span class="lei-mfa-icon ok" title="MFA Enabled">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                            <polyline points="9 12 11 14 15 10"/>
                                        </svg>
                                    </span>
                                @elseif ($user->mfa_status === 'warning')
                                    <span class="lei-mfa-icon warn" title="MFA Warning">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                            <line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                                        </svg>
                                    </span>
                                @else
                                    <span class="lei-mfa-icon off" title="MFA {{ ucfirst($user->mfa_status) }}">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                            <line x1="4" y1="4" x2="20" y2="20"/>
                                        </svg>
                                    </span>
                                @endif
                            </td>
                            <td>
                                @include('admin.partials.icon-actions', [
                                    'viewUrl' => route('admin.users.show', $user),
                                    'editUrl' => route('admin.users.edit', $user),
                                    'approveUrl' => $user->account_status === 'pending' ? route('admin.users.approve', $user) : null,
                                    'deleteUrl' => $user->id !== auth()->id() ? route('admin.users.destroy', $user) : null,
                                    'deleteConfirm' => 'Permanently delete '.$user->name.'? This cannot be undone.',
                                    'deleteTitle' => 'Delete User',
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="lei-table-empty">No users match your filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="lei-table-footer">
            <span>
                Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} global users
            </span>
            @include('admin.users.partials.pagination', ['paginator' => $users])
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/lei-users.js') }}"></script>
@endpush

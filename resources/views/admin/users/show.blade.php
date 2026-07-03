@extends('admin.layouts.app')

@section('title', $user->name . ' — User Profile')
@section('body_class', 'lei-page-users')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Registry</a>
    <span> / </span>
    <a href="{{ route('admin.users.index') }}">User Management</a>
    <span> / </span>
    <span>{{ $user->name }}</span>
@endsection

@section('content')
<div class="lei-users-header">
    <div class="lei-users-header-row">
        <div>
            <h2>{{ $user->name }}</h2>
            <p>{{ $user->email }}</p>
        </div>
        <div class="lei-header-actions">
            @php
                $userToolbar = [
                    'editUrl' => route('admin.users.edit', $user),
                    'deleteUrl' => $user->id !== auth()->id() ? route('admin.users.destroy', $user) : null,
                    'deleteConfirm' => 'Permanently delete this user? This cannot be undone.',
                    'deleteTitle' => 'Delete User',
                ];
                if ($user->account_status === 'pending') {
                    $userToolbar['approveUrl'] = route('admin.users.approve', $user);
                } elseif ($user->account_status === 'active') {
                    $userToolbar['confirmUrl'] = route('admin.users.lock', $user);
                    $userToolbar['confirmMessage'] = 'Lock this user account? They will not be able to sign in.';
                    $userToolbar['confirmTitle'] = 'Lock Account';
                    $userToolbar['confirmButton'] = 'Lock';
                    $userToolbar['confirmVariant'] = 'warning';
                    $userToolbar['confirmIcon'] = 'fa-lock';
                } elseif ($user->account_status === 'locked') {
                    $userToolbar['activateUrl'] = route('admin.users.activate', $user);
                }
            @endphp
            <div class="lei-icon-actions lei-icon-actions--lg">
                @include('admin.partials.icon-actions', $userToolbar)
                <a href="{{ route('admin.users.index') }}" class="lei-icon-btn" title="Back to list" aria-label="Back to list">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="lei-users-layout">
    <aside class="lei-users-side">
        <div class="lei-health-stats-card">
            <div class="lei-user-profile-card">
                <div class="lei-user-avatar" style="background-color: {{ $user->avatar_color }}">
                    {{ $user->initials }}
                </div>
                <span class="lei-user-name">{{ $user->name }}</span>
                <span class="lei-user-role-tag">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span>
            </div>

            <div class="lei-mini-stat lei-accent-blue">
                <div class="lei-mini-stat-inner">
                    <div class="label">Account Status</div>
                    <div class="value-row">
                        <span class="lei-status-pill {{ $user->account_status }}">{{ strtoupper($user->account_status) }}</span>
                    </div>
                </div>
            </div>

            <div class="lei-mini-stat lei-accent-blue">
                <div class="lei-mini-stat-inner">
                    <div class="label">Portal Access</div>
                    <div class="value-row">
                        <span class="lei-status-pill {{ $user->is_active ? 'active' : 'locked' }}">
                            {{ $user->is_active ? 'ENABLED' : 'DISABLED' }}
                        </span>
                    </div>
                </div>
            </div>

            @if ($user->account_status === 'pending')
                <div class="lei-mini-stat lei-accent-yellow lei-mini-stat--notice">
                    <div class="lei-mini-stat-inner">
                        <div class="label">Awaiting Approval</div>
                        <div class="value-row">
                            This user cannot sign in until an administrator approves the account.
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </aside>

    <div class="lei-users-table-card lei-user-detail-card">
        <h3 class="lei-user-detail-section">Account Details</h3>
        <dl class="lei-user-detail-dl">
            <div><dt>Full Name</dt><dd>{{ $user->name }}</dd></div>
            <div><dt>Email</dt><dd>{{ $user->email }}</dd></div>
            <div><dt>Account Type</dt><dd>{{ ucfirst(str_replace('_', ' ', $user->role)) }}</dd></div>
            @if ($user->isAdmin())
                <div><dt>Admin Role</dt><dd>{{ $user->adminRole?->name ?? '—' }}</dd></div>
                <div><dt>Organization</dt><dd>{{ $user->organization?->name ?? '—' }}</dd></div>
            @else
                <div><dt>Organization Name</dt><dd>{{ $user->organization_name ?? '—' }}</dd></div>
                <div><dt>LEI Code</dt><dd class="lei-user-detail-mono">{{ $user->lei_number ?? '—' }}</dd></div>
                <div><dt>Country</dt><dd>{{ $user->country_of_incorporation ?? '—' }}</dd></div>
                <div><dt>Phone</dt><dd>{{ $user->phone ?? '—' }}</dd></div>
            @endif
            <div><dt>MFA Status</dt><dd>{{ ucfirst($user->mfa_status) }}</dd></div>
            <div><dt>Last Login</dt><dd>{{ $user->last_login_at?->format('M j, Y g:i A') ?? 'Never' }}</dd></div>
            <div><dt>Member Since</dt><dd>{{ $user->created_at?->format('M j, Y g:i A') ?? '—' }}</dd></div>
        </dl>

        @if ($user->isAdmin() && $user->modulePermissions->isNotEmpty())
            <h3 class="lei-user-detail-section">Module Permissions</h3>
            <div class="lei-table-scroll">
                <table class="lei-users-table">
                    <thead>
                        <tr>
                            <th>Module</th>
                            <th>Read</th>
                            <th>Write</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($user->modulePermissions as $perm)
                            <tr>
                                <td>{{ $perm->module?->name ?? 'Module #'.$perm->system_module_id }}</td>
                                <td class="lei-users-td--mfa">
                                    <span class="lei-perm-flag {{ $perm->can_read ? 'lei-perm-flag--yes' : 'lei-perm-flag--no' }}" aria-label="{{ $perm->can_read ? 'Allowed' : 'Denied' }}">
                                        <i class="fa-solid {{ $perm->can_read ? 'fa-check' : 'fa-minus' }}" aria-hidden="true"></i>
                                    </span>
                                </td>
                                <td class="lei-users-td--mfa">
                                    <span class="lei-perm-flag {{ $perm->can_write ? 'lei-perm-flag--yes' : 'lei-perm-flag--no' }}" aria-label="{{ $perm->can_write ? 'Allowed' : 'Denied' }}">
                                        <i class="fa-solid {{ $perm->can_write ? 'fa-check' : 'fa-minus' }}" aria-hidden="true"></i>
                                    </span>
                                </td>
                                <td class="lei-users-td--mfa">
                                    <span class="lei-perm-flag {{ $perm->can_delete ? 'lei-perm-flag--yes' : 'lei-perm-flag--no' }}" aria-label="{{ $perm->can_delete ? 'Allowed' : 'Denied' }}">
                                        <i class="fa-solid {{ $perm->can_delete ? 'fa-check' : 'fa-minus' }}" aria-hidden="true"></i>
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

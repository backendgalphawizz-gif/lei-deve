@extends('admin.layouts.app')

@section('title', 'Edit User — ' . $user->name)
@section('body_class', 'lei-page-create-user')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Registry</a>
    <span> / </span>
    <a href="{{ route('admin.users.index') }}">User Management</a>
    <span> / </span>
    <a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a>
    <span> / </span>
    <span>Edit</span>
@endsection

@section('content')
<div class="lei-create-page">
    @if ($errors->any())
        <div class="lei-form-errors lei-form-errors--page">
            <strong>Please fix the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="lei-create-form">
        @csrf
        @method('PUT')
        <div class="lei-create-card">
            <header class="lei-create-header">
                <div class="lei-create-header-text">
                    <h2>Edit User Account</h2>
                    <p class="step">{{ $user->isAdmin() ? 'Administrative user' : 'Applicant user' }} · {{ $user->email }}</p>
                </div>
                <a href="{{ route('admin.users.show', $user) }}" class="lei-create-close" title="Close" aria-label="Close">&times;</a>
            </header>

            <div class="lei-create-body">
                <section class="lei-create-col lei-create-col--identity">
                    <h3>Identity Profile</h3>

                    <div class="lei-form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="lei-form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    </div>

                    @if ($user->isAdmin())
                        <div class="lei-form-group">
                            <label for="organization_id">Organization Entity</label>
                            <div class="lei-select-wrap">
                                <select id="organization_id" name="organization_id" required>
                                    @foreach ($organizations as $org)
                                        <option value="{{ $org->id }}" @selected(old('organization_id', $user->organization_id) == $org->id)>{{ $org->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="lei-form-group lei-form-group--roles">
                            <label class="lei-subsection-label">Role Assignment</label>
                            <div class="lei-role-cards">
                                @foreach ($adminRoles as $role)
                                    <label class="lei-role-card {{ (old('admin_role_id', $user->admin_role_id) == $role->id) ? 'selected' : '' }}">
                                        <input type="radio" name="admin_role_id" value="{{ $role->id }}"
                                               {{ old('admin_role_id', $user->admin_role_id) == $role->id ? 'checked' : '' }}>
                                        <strong>{{ $role->name }}</strong>
                                        <span>{{ $role->description }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="lei-form-group">
                            <label for="organization_name">Organization Name</label>
                            <input type="text" id="organization_name" name="organization_name" value="{{ old('organization_name', $user->organization_name) }}">
                        </div>
                        <div class="lei-form-group">
                            <label for="phone">Phone</label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                        </div>
                        <div class="lei-form-group">
                            <label for="country_of_incorporation">Country of Incorporation</label>
                            <input type="text" id="country_of_incorporation" name="country_of_incorporation" value="{{ old('country_of_incorporation', $user->country_of_incorporation) }}">
                        </div>
                    @endif

                    <div class="lei-form-group">
                        <label for="account_status">Account Status</label>
                        <div class="lei-select-wrap">
                            <select id="account_status" name="account_status" required>
                                @foreach (['pending' => 'Pending — awaiting approval', 'active' => 'Active — can sign in', 'locked' => 'Locked — access denied'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('account_status', $user->account_status) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @if ($user->isAdmin())
                        <div class="lei-form-group">
                            <label for="mfa_status">MFA Status</label>
                            <div class="lei-select-wrap">
                                <select id="mfa_status" name="mfa_status" required>
                                    @foreach (['enabled', 'pending', 'disabled', 'warning'] as $mfa)
                                        <option value="{{ $mfa }}" @selected(old('mfa_status', $user->mfa_status) === $mfa)>{{ ucfirst($mfa) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                </section>

                @if ($user->isAdmin())
                    <section class="lei-create-col lei-create-col--matrix">
                        <h3>Permission Assignment Matrix</h3>
                        <div class="lei-perm-matrix" id="permissionMatrix">
                            <div class="lei-perm-row lei-perm-row--head">
                                <div class="lei-perm-cell lei-perm-cell--module">System Module</div>
                                <div class="lei-perm-cell lei-perm-cell--action">Read</div>
                                <div class="lei-perm-cell lei-perm-cell--action">Write</div>
                                <div class="lei-perm-cell lei-perm-cell--action">Delete</div>
                            </div>

                            @foreach ($modules as $index => $module)
                                @php
                                    $perm = $userPermissions[$module->id] ?? null;
                                    $oldPerm = old("permissions.{$index}", []);
                                @endphp
                                <div class="lei-perm-row" data-module-id="{{ $module->id }}">
                                    <div class="lei-perm-cell lei-perm-cell--module">
                                        <input type="hidden" name="permissions[{{ $index }}][module_id]" value="{{ $module->id }}">
                                        <div class="lei-perm-name">{{ $module->name }}</div>
                                        <div class="lei-perm-desc">{{ $module->description }}</div>
                                    </div>
                                    <div class="lei-perm-cell lei-perm-cell--action">
                                        <label class="lei-checkbox-wrap">
                                            <input type="checkbox" name="permissions[{{ $index }}][can_read]" value="1"
                                                   @checked($oldPerm['can_read'] ?? $perm?->can_read)>
                                            <span class="lei-checkbox-ui"></span>
                                        </label>
                                    </div>
                                    <div class="lei-perm-cell lei-perm-cell--action">
                                        <label class="lei-checkbox-wrap">
                                            <input type="checkbox" name="permissions[{{ $index }}][can_write]" value="1"
                                                   @checked($oldPerm['can_write'] ?? $perm?->can_write)>
                                            <span class="lei-checkbox-ui"></span>
                                        </label>
                                    </div>
                                    <div class="lei-perm-cell lei-perm-cell--action">
                                        <label class="lei-checkbox-wrap">
                                            <input type="checkbox" name="permissions[{{ $index }}][can_delete]" value="1"
                                                   @checked($oldPerm['can_delete'] ?? $perm?->can_delete)>
                                            <span class="lei-checkbox-ui"></span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <footer class="lei-create-footer">
                <a href="{{ route('admin.users.show', $user) }}" class="lei-btn-filter">Cancel</a>
                <button type="submit" class="lei-btn-submit-user">Save Changes</button>
            </footer>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/lei-users.js') }}?v=2"></script>
@if ($user->isAdmin())
<script>
    const permissionsUrl = @json(route('admin.users.role.permissions', ['role' => '__ROLE__']));
    document.querySelectorAll('input[name="admin_role_id"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            if (this.checked) loadRolePermissions(this.value);
        });
    });
    async function loadRolePermissions(roleId) {
        const url = permissionsUrl.replace('__ROLE__', roleId);
        const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        if (!res.ok) return;
        const data = await res.json();
        document.querySelectorAll('#permissionMatrix .lei-perm-row[data-module-id]').forEach(function (row) {
            const moduleId = parseInt(row.dataset.moduleId, 10);
            const perm = data.permissions.find(function (p) { return p.module_id === moduleId; });
            const read = row.querySelector('input[name*="[can_read]"]');
            const write = row.querySelector('input[name*="[can_write]"]');
            const del = row.querySelector('input[name*="[can_delete]"]');
            if (read && write && del) {
                read.checked = perm ? perm.can_read : false;
                write.checked = perm ? perm.can_write : false;
                del.checked = perm ? perm.can_delete : false;
            }
        });
    }
</script>
@endif
@endpush

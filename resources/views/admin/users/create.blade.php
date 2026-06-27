@extends('admin.layouts.app')

@section('title', 'Create New System User')
@section('body_class', 'lei-page-create-user')

@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}">Registry</a>
    <span> / </span>
    <a href="{{ route('admin.users.index') }}">User Management</a>
    <span> / </span>
    <span>Create User</span>
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

    <form method="POST" action="{{ route('admin.users.store') }}" id="createUserForm" class="lei-create-form">
        @csrf
        <div class="lei-create-card">
            <header class="lei-create-header">
                <div class="lei-create-header-text">
                    <h2>Create New System User</h2>
                    <p class="step">Step 1: Account Definition &amp; Privileges</p>
                </div>
                <a href="{{ route('admin.users.index') }}" class="lei-create-close" title="Close" aria-label="Close">&times;</a>
            </header>

            <div class="lei-create-body">
                <section class="lei-create-col lei-create-col--identity">
                    <h3>Identity Profile</h3>

                    <div class="lei-form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="e.g. Elena Rodriguez" required data-rules="required|maxLen:120">
                    </div>

                    <div class="lei-form-group">
                        <label for="email">Official Email Address</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="e.g. e.rod@registry-ops.int" required data-rules="required|email|maxLen:150">
                    </div>

                    <div class="lei-form-group">
                        <label for="organization_id">Organization Entity</label>
                        <div class="lei-select-wrap">
                            <select id="organization_id" name="organization_id" required data-rules="required">
                                <option value="">Select Registry Node</option>
                                @foreach ($organizations as $org)
                                    <option value="{{ $org->id }}" @selected(old('organization_id') == $org->id)>{{ $org->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="lei-form-group lei-form-group--roles">
                        <label class="lei-subsection-label">Initial Role Assignment</label>
                        <div class="lei-role-cards">
                            @foreach ($adminRoles as $role)
                                <label class="lei-role-card {{ (old('admin_role_id', $defaultRole?->id) == $role->id) ? 'selected' : '' }}">
                                    <input type="radio" name="admin_role_id" value="{{ $role->id }}"
                                           {{ old('admin_role_id', $defaultRole?->id) == $role->id ? 'checked' : '' }}>
                                    <strong>{{ $role->name }}</strong>
                                    <span>{{ $role->description }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </section>

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
                                $perm = $rolePermissions[$module->id] ?? null;
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

                    <div class="lei-security-note">
                        <span class="lei-security-note-icon" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                        </span>
                        <span><strong>Security Note:</strong> Permission changes will be logged in the Global Audit Trail. The user will receive an invitation email to set their secure passkey once created.</span>
                    </div>
                </section>
            </div>

            <footer class="lei-create-footer">
                <div class="lei-create-status" data-credential-status>
                    <span class="dot"></span>
                    <span class="lei-status-text">Awaiting Mandatory Credentials</span>
                </div>
                <button type="submit" class="lei-btn-submit-user">+ Create User</button>
            </footer>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/lei-users.js') }}?v=2"></script>
<script>
    const permissionsUrl = @json(route('admin.users.role.permissions', ['role' => '__ROLE__']));

    async function loadRolePermissions(roleId) {
        const url = permissionsUrl.replace('__ROLE__', roleId);
        const res = await fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
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
@endpush

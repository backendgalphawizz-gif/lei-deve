<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\SystemModule;
use App\Models\User;
use App\Models\UserModulePermission;
use App\Services\UserManagementStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->with(['organization', 'adminRole'])
            ->where('role', '!=', 'super_admin')
            ->orderByDesc('created_at');

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('organization', fn ($oq) => $oq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($orgId = $request->integer('organization')) {
            $query->where('organization_id', $orgId);
        }

        if ($status = $request->string('status')->trim()->toString()) {
            if (in_array($status, ['active', 'locked', 'pending'], true)) {
                $query->where('account_status', $status);
            }
        }

        if ($mfa = $request->string('mfa')->trim()->toString()) {
            if (in_array($mfa, ['enabled', 'pending', 'disabled', 'warning'], true)) {
                $query->where('mfa_status', $mfa);
            }
        }

        $users = $query->paginate(5)->withQueryString();
        $organizations = Organization::where('is_active', true)->orderBy('name')->get();
        $stats = app(UserManagementStatsService::class)->compute();

        return view('admin.users.index', compact('users', 'organizations', 'stats'));
    }

    public function create()
    {
        $organizations = Organization::where('is_active', true)->orderBy('name')->get();
        $adminRoles = AdminRole::where('is_active', true)
            ->orderByRaw("CASE slug
                WHEN 'regional_admin' THEN 1
                WHEN 'data_analyst' THEN 2
                WHEN 'global_auditor' THEN 3
                WHEN 'security_analyst' THEN 4
                WHEN 'certificate_authority' THEN 5
                ELSE 6 END")
            ->get();
        $modules = SystemModule::orderBy('sort_order')->get();
        $defaultRole = $adminRoles->firstWhere('slug', 'data_analyst') ?? $adminRoles->first();

        $rolePermissions = [];
        if ($defaultRole) {
            $rolePermissions = $defaultRole->permissions()
                ->get()
                ->keyBy('system_module_id');
        }

        return view('admin.users.create', compact(
            'organizations',
            'adminRoles',
            'modules',
            'defaultRole',
            'rolePermissions'
        ));
    }

    public function rolePermissions(AdminRole $role)
    {
        $permissions = $role->permissions()->get()->map(fn ($p) => [
            'module_id' => $p->system_module_id,
            'can_read' => $p->can_read,
            'can_write' => $p->can_write,
            'can_delete' => $p->can_delete,
        ]);

        return response()->json(['permissions' => $permissions]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'organization_id' => ['required', 'exists:organizations,id'],
            'admin_role_id' => ['required', 'exists:admin_roles,id'],
            'permissions' => ['required', 'array'],
            'permissions.*.module_id' => ['required', 'exists:system_modules,id'],
            'permissions.*.can_read' => ['nullable', 'boolean'],
            'permissions.*.can_write' => ['nullable', 'boolean'],
            'permissions.*.can_delete' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'system_id' => $validated['email'],
                'password' => Hash::make(Str::random(32)),
                'role' => 'admin',
                'organization_id' => $validated['organization_id'],
                'admin_role_id' => $validated['admin_role_id'],
                'account_status' => 'pending',
                'mfa_status' => 'pending',
                'is_active' => false,
            ]);

            foreach ($validated['permissions'] as $perm) {
                UserModulePermission::create([
                    'user_id' => $user->id,
                    'system_module_id' => $perm['module_id'],
                    'can_read' => ! empty($perm['can_read']),
                    'can_write' => ! empty($perm['can_write']),
                    'can_delete' => ! empty($perm['can_delete']),
                ]);
            }
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created and set to Pending. Approve the account to allow portal access.');
    }

    public function show(User $user)
    {
        $this->guardManagedUser($user);
        $user->load(['organization', 'adminRole', 'modulePermissions.module']);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->guardManagedUser($user);

        $organizations = Organization::where('is_active', true)->orderBy('name')->get();
        $adminRoles = AdminRole::where('is_active', true)
            ->orderByRaw("CASE slug
                WHEN 'regional_admin' THEN 1
                WHEN 'data_analyst' THEN 2
                WHEN 'global_auditor' THEN 3
                WHEN 'security_analyst' THEN 4
                WHEN 'certificate_authority' THEN 5
                ELSE 6 END")
            ->get();
        $modules = SystemModule::orderBy('sort_order')->get();
        $user->load(['modulePermissions']);

        $userPermissions = $user->modulePermissions->keyBy('system_module_id');

        return view('admin.users.edit', compact(
            'user',
            'organizations',
            'adminRoles',
            'modules',
            'userPermissions',
        ));
    }

    public function update(Request $request, User $user)
    {
        $this->guardManagedUser($user);

        if ($user->isAdmin()) {
            return $this->updateAdminUser($request, $user);
        }

        return $this->updateApplicantUser($request, $user);
    }

    public function approve(Request $request, User $user)
    {
        $this->guardManagedUser($user);

        if ($user->account_status !== 'pending') {
            return redirect()
                ->route('admin.users.show', $user)
                ->with('info', 'This account is not awaiting approval.');
        }

        $user->update([
            'account_status' => 'active',
            'is_active' => true,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'approve_user',
            'module' => 'user_management',
            'description' => "Approved user account: {$user->email}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User approved. They can now sign in and begin using the portal.');
    }

    public function lock(Request $request, User $user)
    {
        $this->guardManagedUser($user);

        $user->update([
            'account_status' => 'locked',
            'is_active' => false,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'lock_user',
            'module' => 'user_management',
            'description' => "Locked user account: {$user->email}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User account has been locked.');
    }

    public function activate(Request $request, User $user)
    {
        $this->guardManagedUser($user);

        $user->update([
            'account_status' => 'active',
            'is_active' => true,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'activate_user',
            'module' => 'user_management',
            'description' => "Reactivated user account: {$user->email}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User account is now active.');
    }

    public function destroy(Request $request, User $user)
    {
        $this->guardManagedUser($user);

        if ($user->id === auth()->id()) {
            return redirect()
                ->route('admin.users.show', $user)
                ->with('error', 'You cannot delete your own account.');
        }

        $email = $user->email;
        $name = $user->name;

        DB::transaction(function () use ($user) {
            $user->modulePermissions()->delete();
            $user->delete();
        });

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_user',
            'module' => 'user_management',
            'description' => "Deleted user account: {$email}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User {$name} has been permanently deleted.");
    }

    private function updateAdminUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email,'.$user->id],
            'organization_id' => ['required', 'exists:organizations,id'],
            'admin_role_id' => ['required', 'exists:admin_roles,id'],
            'account_status' => ['required', 'in:active,pending,locked'],
            'mfa_status' => ['required', 'in:enabled,pending,disabled,warning'],
            'permissions' => ['required', 'array'],
            'permissions.*.module_id' => ['required', 'exists:system_modules,id'],
            'permissions.*.can_read' => ['nullable', 'boolean'],
            'permissions.*.can_write' => ['nullable', 'boolean'],
            'permissions.*.can_delete' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated, $user) {
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'system_id' => $validated['email'],
                'organization_id' => $validated['organization_id'],
                'admin_role_id' => $validated['admin_role_id'],
                'account_status' => $validated['account_status'],
                'mfa_status' => $validated['mfa_status'],
                'is_active' => $validated['account_status'] === 'active',
            ]);

            $user->modulePermissions()->delete();

            foreach ($validated['permissions'] as $perm) {
                UserModulePermission::create([
                    'user_id' => $user->id,
                    'system_module_id' => $perm['module_id'],
                    'can_read' => ! empty($perm['can_read']),
                    'can_write' => ! empty($perm['can_write']),
                    'can_delete' => ! empty($perm['can_delete']),
                ]);
            }
        });

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_user',
            'module' => 'user_management',
            'description' => "Updated admin user: {$user->email}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    private function updateApplicantUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:32'],
            'organization_name' => ['nullable', 'string', 'max:190'],
            'country_of_incorporation' => ['nullable', 'string', 'max:64'],
            'account_status' => ['required', 'in:active,pending,locked'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'organization_name' => $validated['organization_name'] ?? null,
            'country_of_incorporation' => $validated['country_of_incorporation'] ?? null,
            'account_status' => $validated['account_status'],
            'is_active' => $validated['account_status'] === 'active',
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_user',
            'module' => 'user_management',
            'description' => "Updated applicant user: {$user->email}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Applicant account updated successfully.');
    }

    private function guardManagedUser(User $user): void
    {
        abort_if($user->isSuperAdmin(), 404);
    }
}

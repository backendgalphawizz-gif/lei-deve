<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
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
                ELSE 5 END")
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
                'is_active' => true,
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
            ->with('success', 'User created successfully. Invitation email will be sent to set secure passkey.');
    }
}

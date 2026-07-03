<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        $user = auth()->user();

        if ($user && $user->is_active && $user->isAdmin() && $user->account_status === 'active') {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'system_id' => ['required', 'string', 'max:64'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');
        $identifier = $credentials['system_id'];

        $user = User::query()
            ->where('role', '!=', 'applicant')
            ->where(function ($query) use ($identifier) {
                $query->where('system_id', $identifier)
                    ->orWhere('email', $identifier);
            })
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'system_id' => 'Invalid admin identifier or secure token.',
            ]);
        }

        if (! $user->isAdmin()) {
            throw ValidationException::withMessages([
                'system_id' => 'Invalid admin identifier or secure token.',
            ]);
        }

        if ($user->account_status === 'pending') {
            throw ValidationException::withMessages([
                'system_id' => 'Your account is pending administrator approval. Please contact your registry administrator.',
            ]);
        }

        if ($user->account_status === 'locked' || ! $user->is_active) {
            throw ValidationException::withMessages([
                'system_id' => 'This account is locked or inactive. Contact your administrator.',
            ]);
        }

        Auth::login($user, $remember);

        $user = Auth::user();
        $user->update(['last_login_at' => now()]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'login',
            'module' => 'auth',
            'description' => 'Super admin portal login',
            'ip_address' => $request->ip(),
        ]);

        $request->session()->regenerate();

        $home = $user->adminRole?->slug === 'certificate_authority' && ! $user->isSuperAdmin()
            ? route('admin.certificates.index')
            : route('admin.dashboard');

        return redirect()->intended($home);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}

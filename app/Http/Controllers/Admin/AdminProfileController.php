<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeiBusinessSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Rules\PhoneTenDigits;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user()->load(['organization', 'adminRole']);

        return view('admin.profile.index', [
            'user' => $user,
            'settings' => LeiBusinessSetting::current(),
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'job_title' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', new PhoneTenDigits],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('remove_photo') && $user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $validated['avatar'] = null;
        } elseif ($request->hasFile('photo')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $ext = $request->file('photo')->getClientOriginalExtension() ?: 'jpg';
            $validated['avatar'] = $request->file('photo')->storeAs(
                'profiles',
                'user-' . $user->id . '-' . Str::random(6) . '.' . strtolower($ext),
                'public'
            );
        }

        unset($validated['photo'], $validated['remove_photo']);

        $user->update($validated);

        return redirect()
            ->route('admin.profile.show')
            ->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        auth()->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('admin.profile.show')
            ->with('success', 'Password changed successfully.');
    }
}

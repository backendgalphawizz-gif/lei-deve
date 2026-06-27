<?php

namespace App\Http\Controllers\Applicant;

use Illuminate\Http\Request;

class ProfileController extends ApplicantPortalController
{
    public function show()
    {
        $this->sharePortalContext();
        $user = auth()->user();

        return view('applicant.profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:190', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:32'],
            'country_of_incorporation' => ['nullable', 'string', 'max:64'],
        ]);

        $user->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }
}

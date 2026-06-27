<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\LeiContactSubmission;
use App\Models\LeiSiteSection;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $sections = LeiSiteSection::forPage('contact');

        return view('public.contact.index', [
            'sections' => $sections,
            'subjects' => LeiContactSubmission::subjectOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'subject' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        LeiContactSubmission::create([
            ...$data,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Your message has been sent. Our team will respond shortly.');
    }
}

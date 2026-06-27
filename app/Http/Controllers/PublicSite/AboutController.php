<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\LeiSiteSection;

class AboutController extends Controller
{
    public function index()
    {
        $sections = LeiSiteSection::forPage('about');

        return view('public.about.index', compact('sections'));
    }
}

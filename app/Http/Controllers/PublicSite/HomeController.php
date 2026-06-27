<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\LeiSiteSection;

class HomeController extends Controller
{
    public function index()
    {
        $sections = LeiSiteSection::forPage('home');

        return view('public.home.index', compact('sections'));
    }
}

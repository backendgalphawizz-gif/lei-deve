<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\LeiHomeLeiBlock;
use App\Models\LeiSiteSection;

class HomeController extends Controller
{
    public function index()
    {
        $sections = LeiSiteSection::forPage('home');
        $leiBlocks = LeiHomeLeiBlock::published()->get();

        return view('public.home.index', compact('sections', 'leiBlocks'));
    }
}

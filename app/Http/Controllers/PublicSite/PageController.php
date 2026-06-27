<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\LeiStaticPage;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = LeiStaticPage::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return view('public.pages.show', compact('page'));
    }
}

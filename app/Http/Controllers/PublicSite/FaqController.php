<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\LeiFaq;
use App\Models\LeiFaqCategory;
use App\Models\LeiSiteSection;

class FaqController extends Controller
{
    public function index()
    {
        $sections = LeiSiteSection::forPage('faq');
        $categories = LeiFaqCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->with(['publishedFaqs'])
            ->get();

        $commonFaqs = LeiFaq::query()
            ->where('is_published', true)
            ->whereHas('category', fn ($q) => $q->where('slug', 'registration-basics'))
            ->orderBy('sort_order')
            ->get();

        if ($commonFaqs->isEmpty()) {
            $commonFaqs = LeiFaq::query()
                ->where('is_published', true)
                ->orderBy('sort_order')
                ->limit(5)
                ->get();
        }

        return view('public.faq.index', compact('sections', 'categories', 'commonFaqs'));
    }
}

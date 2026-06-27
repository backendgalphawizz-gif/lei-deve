<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeiFaq;
use App\Models\LeiFaqCategory;
use Illuminate\Http\Request;

class FaqManagementController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->string('tab')->trim()->toString() ?: 'faqs';
        $categoryId = $request->integer('category');

        $categories = LeiFaqCategory::query()->orderBy('sort_order')->withCount('faqs')->get();

        $faqsQuery = LeiFaq::query()->with('category')->orderBy('sort_order');

        if ($categoryId) {
            $faqsQuery->where('category_id', $categoryId);
        }

        if ($search = $request->string('q')->trim()->toString()) {
            $faqsQuery->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        return view('admin.faq.index', [
            'tab' => $tab,
            'categories' => $categories,
            'faqs' => $faqsQuery->paginate(15)->withQueryString(),
            'stats' => [
                'categories' => LeiFaqCategory::count(),
                'published' => LeiFaq::where('is_published', true)->count(),
                'pricing' => LeiFaq::where('show_on_pricing', true)->count(),
            ],
            'activeCategoryId' => $categoryId ?: null,
        ]);
    }

    public function create()
    {
        return view('admin.faq.create', [
            'faq' => new LeiFaq,
            'categories' => LeiFaqCategory::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function edit(LeiFaq $faq)
    {
        return view('admin.faq.edit', [
            'faq' => $faq,
            'categories' => LeiFaqCategory::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:32'],
        ]);

        LeiFaqCategory::create([
            ...$data,
            'slug' => LeiFaqCategory::uniqueSlug($data['title']),
            'sort_order' => (int) LeiFaqCategory::max('sort_order') + 1,
            'is_active' => true,
        ]);

        return back()->with('success', 'FAQ category created.');
    }

    public function updateCategory(Request $request, LeiFaqCategory $category)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:32'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category->update([
            ...$data,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Category updated.');
    }

    public function destroyCategory(LeiFaqCategory $category)
    {
        LeiFaq::where('category_id', $category->id)->update(['category_id' => null]);
        $category->delete();

        return back()->with('success', 'Category deleted.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'exists:lei_faq_categories,id'],
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
            'show_on_pricing' => ['nullable', 'boolean'],
        ]);

        LeiFaq::create([
            ...$data,
            'sort_order' => (int) LeiFaq::max('sort_order') + 1,
            'is_published' => $request->boolean('is_published', true),
            'show_on_pricing' => $request->boolean('show_on_pricing'),
        ]);

        return redirect()
            ->route('admin.faq.index')
            ->with('success', 'FAQ added successfully.');
    }

    public function update(Request $request, LeiFaq $faq)
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'exists:lei_faq_categories,id'],
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
            'show_on_pricing' => ['nullable', 'boolean'],
        ]);

        $faq->update([
            ...$data,
            'is_published' => $request->boolean('is_published'),
            'show_on_pricing' => $request->boolean('show_on_pricing'),
        ]);

        return redirect()
            ->route('admin.faq.edit', $faq)
            ->with('success', 'FAQ updated.');
    }

    public function destroy(LeiFaq $faq)
    {
        $faq->delete();

        return redirect()
            ->route('admin.faq.index')
            ->with('success', 'FAQ deleted.');
    }
}

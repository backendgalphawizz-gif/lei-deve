<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeiStaticPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StaticPageController extends Controller
{
    public function index(Request $request)
    {
        $query = LeiStaticPage::query()->orderBy('sort_order')->orderByDesc('updated_at');

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($status = $request->string('status')->trim()->toString()) {
            if (array_key_exists($status, LeiStaticPage::statuses())) {
                $query->where('status', $status);
            }
        }

        if ($type = $request->string('type')->trim()->toString()) {
            if (array_key_exists($type, LeiStaticPage::pageTypes())) {
                $query->where('page_type', $type);
            }
        }

        $pages = $query->paginate(10)->withQueryString();

        return view('admin.static-pages.index', [
            'pages' => $pages,
            'stats' => [
                'total' => LeiStaticPage::count(),
                'published' => LeiStaticPage::where('status', 'published')->count(),
                'draft' => LeiStaticPage::where('status', 'draft')->count(),
            ],
            'pageTypes' => LeiStaticPage::pageTypes(),
            'statuses' => LeiStaticPage::statuses(),
        ]);
    }

    public function create()
    {
        return view('admin.static-pages.create', [
            'page' => new LeiStaticPage([
                'status' => 'draft',
                'page_type' => 'legal',
                'sort_order' => (int) LeiStaticPage::max('sort_order') + 1,
            ]),
            'pageTypes' => LeiStaticPage::pageTypes(),
            'statuses' => LeiStaticPage::statuses(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if (empty($data['slug'])) {
            $data['slug'] = LeiStaticPage::uniqueSlug($data['title']);
        }

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        LeiStaticPage::create($data);

        return redirect()
            ->route('admin.static-pages.index')
            ->with('success', 'Static page created successfully.');
    }

    public function edit(LeiStaticPage $staticPage)
    {
        return view('admin.static-pages.edit', [
            'page' => $staticPage,
            'pageTypes' => LeiStaticPage::pageTypes(),
            'statuses' => LeiStaticPage::statuses(),
        ]);
    }

    public function update(Request $request, LeiStaticPage $staticPage)
    {
        $data = $this->validated($request, $staticPage->id);

        if (empty($data['slug'])) {
            $data['slug'] = LeiStaticPage::uniqueSlug($data['title'], $staticPage->id);
        }

        if ($data['status'] === 'published' && ! $staticPage->published_at) {
            $data['published_at'] = now();
        }

        if ($data['status'] !== 'published') {
            $data['published_at'] = null;
        }

        $staticPage->update($data);

        return redirect()
            ->route('admin.static-pages.index')
            ->with('success', 'Static page updated successfully.');
    }

    public function destroy(LeiStaticPage $staticPage)
    {
        $title = $staticPage->title;
        $staticPage->delete();

        return redirect()
            ->route('admin.static-pages.index')
            ->with('success', "“{$title}” deleted.");
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'slug' => [
                'nullable',
                'string',
                'max:120',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('lei_static_pages', 'slug')->ignore($ignoreId),
            ],
            'page_type' => ['required', Rule::in(array_keys(LeiStaticPage::pageTypes()))],
            'status' => ['required', Rule::in(array_keys(LeiStaticPage::statuses()))],
            'content' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:150'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'is_in_footer' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);

        $validated['slug'] = $validated['slug'] ?? '';
        if ($validated['slug'] !== '') {
            $validated['slug'] = Str::slug($validated['slug']);
        }

        $validated['is_in_footer'] = $request->boolean('is_in_footer');
        $validated['meta_title'] = $validated['meta_title'] ?: null;
        $validated['meta_description'] = $validated['meta_description'] ?: null;
        $validated['published_at'] = null;

        return $validated;
    }
}

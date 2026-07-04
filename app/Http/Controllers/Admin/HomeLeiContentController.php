<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeiHomeLeiBlock;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HomeLeiContentController extends Controller
{
    public function index()
    {
        $blocks = LeiHomeLeiBlock::query()->orderBy('sort_order')->get();

        return view('admin.home-content.index', [
            'blocks' => $blocks,
            'blockTypes' => LeiHomeLeiBlock::blockTypes(),
            'stats' => [
                'total' => $blocks->count(),
                'active' => $blocks->where('is_active', true)->count(),
                'categories' => $blocks->where('block_type', 'category')->count(),
            ],
        ]);
    }

    public function create(Request $request)
    {
        $type = $request->string('type')->toString() ?: 'category';

        if (! array_key_exists($type, LeiHomeLeiBlock::blockTypes())) {
            $type = 'category';
        }

        return view('admin.home-content.edit', [
            'block' => new LeiHomeLeiBlock([
                'block_type' => $type,
                'sort_order' => (int) LeiHomeLeiBlock::max('sort_order') + 1,
                'is_active' => true,
                'items' => [],
            ]),
            'blockTypes' => LeiHomeLeiBlock::blockTypes(),
        ]);
    }

    public function edit(LeiHomeLeiBlock $homeContent)
    {
        return view('admin.home-content.edit', [
            'block' => $homeContent,
            'blockTypes' => LeiHomeLeiBlock::blockTypes(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        LeiHomeLeiBlock::create($data);

        return redirect()
            ->route('admin.home-content.index')
            ->with('success', 'Homepage section created.');
    }

    public function update(Request $request, LeiHomeLeiBlock $homeContent)
    {
        $homeContent->update($this->validated($request, $homeContent));

        return redirect()
            ->route('admin.home-content.index')
            ->with('success', 'Homepage section updated.');
    }

    public function destroy(LeiHomeLeiBlock $homeContent)
    {
        if (in_array($homeContent->block_type, ['intro', 'reasons', 'benefits', 'mandatory'], true)) {
            return back()->with('error', 'Core homepage sections cannot be deleted. You can disable them instead.');
        }

        $homeContent->delete();

        return redirect()
            ->route('admin.home-content.index')
            ->with('success', 'Section removed.');
    }

    private function validated(Request $request, ?LeiHomeLeiBlock $existing = null): array
    {
        $type = $request->input('block_type', $existing?->block_type);

        $data = $request->validate([
            'block_type' => ['required', Rule::in(array_keys(LeiHomeLeiBlock::blockTypes()))],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
            'category_number' => ['nullable', 'integer', 'min:1', 'max:99'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
            'items' => ['nullable', 'array'],
            'items.*' => ['nullable', 'string', 'max:500'],
            'benefit_items' => ['nullable', 'array'],
            'benefit_items.*.title' => ['nullable', 'string', 'max:255'],
            'benefit_items.*.text' => ['nullable', 'string', 'max:1000'],
        ]);

        $items = [];
        if ($type === 'benefits') {
            $items = collect($data['benefit_items'] ?? [])
                ->filter(fn ($row) => ! empty(trim($row['title'] ?? '')) || ! empty(trim($row['text'] ?? '')))
                ->map(fn ($row) => [
                    'title' => trim($row['title'] ?? ''),
                    'text' => trim($row['text'] ?? ''),
                ])
                ->values()
                ->all();
        } else {
            $items = collect($data['items'] ?? [])
                ->map(fn ($item) => trim((string) $item))
                ->filter()
                ->values()
                ->all();
        }

        return [
            'block_type' => $data['block_type'],
            'title' => $data['title'] ?? null,
            'subtitle' => $data['subtitle'] ?? null,
            'body' => $data['body'] ?? null,
            'category_number' => $type === 'category' ? ($data['category_number'] ?? null) : null,
            'items' => $items ?: null,
            'sort_order' => (int) $data['sort_order'],
            'is_active' => $request->boolean('is_active', true),
        ];
    }
}

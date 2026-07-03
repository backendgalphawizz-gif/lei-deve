@extends('admin.layouts.app')

@section('title', 'FAQ Management')
@section('body_class', 'lei-page-website-admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-website-admin.css') }}?v=3">
@endpush

@section('breadcrumbs')
    @include('admin.partials.breadcrumbs', ['current' => 'FAQ Management'])
@endsection

@section('content')
<div class="lei-wm-page">
    <div class="lei-wm-head">
        <div>
            <h2>FAQ Management</h2>
            <p>Manage categories and questions shown on the public FAQ and pricing pages.</p>
        </div>
        <div class="lei-wm-head-actions">
            <a href="{{ route('faq') }}" target="_blank" class="lei-wm-btn-ghost">View Public FAQ</a>
            @if ($tab === 'faqs')
                <a href="{{ route('admin.faq.create') }}" class="lei-wm-btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add FAQ
                </a>
            @else
                <button type="button" class="lei-wm-btn-primary" onclick="document.getElementById('leiAddCategoryForm').scrollIntoView({behavior:'smooth'})">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Category
                </button>
            @endif
        </div>
    </div>

    <div class="lei-wm-stats">
        <div class="lei-wm-stat"><span>Categories</span><strong>{{ $stats['categories'] }}</strong></div>
        <div class="lei-wm-stat lei-wm-stat--ok"><span>Published FAQs</span><strong>{{ $stats['published'] }}</strong></div>
        <div class="lei-wm-stat"><span>On Pricing Page</span><strong>{{ $stats['pricing'] }}</strong></div>
    </div>

    <div class="lei-wm-tabs">
        <a href="{{ route('admin.faq.index', array_merge(request()->except('tab'), ['tab' => 'faqs'])) }}"
           class="lei-wm-tab {{ $tab === 'faqs' ? 'is-active' : '' }}">FAQs</a>
        <a href="{{ route('admin.faq.index', ['tab' => 'categories']) }}"
           class="lei-wm-tab {{ $tab === 'categories' ? 'is-active' : '' }}">Categories</a>
    </div>

    @if ($tab === 'faqs')
        <div class="lei-wm-toolbar-card">
            <form method="GET" action="{{ route('admin.faq.index') }}" class="lei-wm-toolbar">
                <input type="hidden" name="tab" value="faqs">
                <div class="lei-wm-search">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search questions or answers...">
                </div>
                <label>Category
                    <select name="category" onchange="this.form.submit()">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected($activeCategoryId == $category->id)>{{ $category->title }}</option>
                        @endforeach
                    </select>
                </label>
                <button type="submit" class="lei-wm-btn-secondary">Search</button>
            </form>
        </div>

        <div class="lei-wm-table-card">
            <div class="lei-wm-table lei-wm-table--faq">
                <div class="lei-wm-table-row lei-wm-table-row--head">
                    <div class="lei-wm-td">Question</div>
                    <div class="lei-wm-td">Category</div>
                    <div class="lei-wm-td">Published</div>
                    <div class="lei-wm-td">Pricing</div>
                    <div class="lei-wm-td">Updated</div>
                    <div class="lei-wm-td">Actions</div>
                </div>
                @forelse ($faqs as $faq)
                    <div class="lei-wm-table-row">
                        <div class="lei-wm-td">
                            <strong>{{ Str::limit($faq->question, 70) }}</strong>
                            <small>{{ Str::limit(strip_tags($faq->answer), 80) }}</small>
                        </div>
                        <div class="lei-wm-td">{{ $faq->category?->title ?? 'General' }}</div>
                        <div class="lei-wm-td">
                            @if ($faq->is_published)<span class="lei-wm-badge lei-wm-badge--published">Yes</span>@else<span class="lei-wm-badge lei-wm-badge--pending">No</span>@endif
                        </div>
                        <div class="lei-wm-td">
                            @if ($faq->show_on_pricing)<span class="lei-wm-badge lei-wm-badge--pricing">Yes</span>@else—@endif
                        </div>
                        <div class="lei-wm-td">{{ $faq->updated_at->format('M j, Y') }}</div>
                        <div class="lei-wm-td lei-wm-td--actions">
                            @include('admin.partials.icon-actions', [
                                'editUrl' => route('admin.faq.edit', $faq),
                                'deleteUrl' => route('admin.faq.destroy', $faq),
                                'deleteConfirm' => 'Delete this FAQ permanently?',
                                'deleteTitle' => 'Delete FAQ',
                            ])
                        </div>
                    </div>
                @empty
                    <div class="lei-wm-empty">No FAQs found. <a href="{{ route('admin.faq.create') }}">Add your first FAQ</a></div>
                @endforelse
            </div>
            @if ($faqs->hasPages())
                <div class="lei-wm-table-footer">
                    <span>Showing {{ $faqs->firstItem() }}–{{ $faqs->lastItem() }} of {{ $faqs->total() }}</span>
                    <div class="lei-wm-pagination">{{ $faqs->links() }}</div>
                </div>
            @endif
        </div>
    @else
        <div class="lei-wm-form-card" id="leiAddCategoryForm">
            <h3>Add Category</h3>
            <form method="POST" action="{{ route('admin.faq.categories.store') }}" class="lei-wm-form">
                @csrf
                <div class="lei-wm-form-row">
                    <label>Category title<input type="text" name="title" placeholder="e.g. Registration Basics" required data-rules="required|maxLen:120"></label>
                    <label>Description<textarea name="description" rows="2" placeholder="Short description" data-rules="maxLen:500"></textarea></label>
                </div>
                <div class="lei-wm-form-actions">
                    <button type="submit" class="lei-wm-btn-primary">Add Category</button>
                </div>
            </form>
        </div>

        <div class="lei-wm-table-card">
            <div class="lei-wm-table lei-wm-table--categories">
                <div class="lei-wm-table-row lei-wm-table-row--head">
                    <div class="lei-wm-td">Title</div>
                    <div class="lei-wm-td">Description</div>
                    <div class="lei-wm-td">FAQs</div>
                    <div class="lei-wm-td">Actions</div>
                </div>
                @forelse ($categories as $category)
                    <div class="lei-wm-table-row">
                        <div class="lei-wm-td"><strong>{{ $category->title }}</strong></div>
                        <div class="lei-wm-td">{{ Str::limit($category->description, 80) ?: '—' }}</div>
                        <div class="lei-wm-td">{{ $category->faqs_count }}</div>
                        <div class="lei-wm-td lei-wm-td--actions">
                            @include('admin.partials.icon-actions', [
                                'viewUrl' => route('admin.faq.index', ['tab' => 'faqs', 'category' => $category->id]),
                                'deleteUrl' => route('admin.faq.categories.destroy', $category),
                                'deleteConfirm' => 'Delete this category? FAQs will become uncategorized.',
                                'deleteTitle' => 'Delete Category',
                            ])
                        </div>
                    </div>
                @empty
                    <div class="lei-wm-empty">No categories yet. Add one using the form above.</div>
                @endforelse
            </div>
        </div>
    @endif
</div>
@endsection

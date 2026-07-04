@php
    $isEdit = $block->exists;
    $action = $isEdit
        ? route('admin.home-content.update', $block)
        : route('admin.home-content.store');
    $items = old('items', $block->items ?? []);
    $benefitItems = old('benefit_items', $block->block_type === 'benefits' ? ($block->items ?? []) : []);
    if ($benefitItems === [] && $block->block_type === 'benefits') {
        $benefitItems = [['title' => '', 'text' => '']];
    }
    if ($items === [] && in_array($block->block_type, ['category', 'reasons'], true)) {
        $items = [''];
    }
@endphp

@extends('admin.layouts.app')

@section('title', ($isEdit ? 'Edit' : 'Add') . ' Homepage Section')
@section('body_class', 'lei-page-website-admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-website-admin.css') }}?v=4">
@endpush

@section('breadcrumbs')
    @include('admin.partials.breadcrumbs', [
        'trail' => [
            ['label' => 'Homepage LEI Content', 'url' => route('admin.home-content.index')],
        ],
        'current' => $isEdit ? 'Edit Section' : 'Add Section',
    ])
@endsection

@section('content')
<div class="lei-wm-page">
    <div class="lei-wm-head">
        <div>
            <h2>{{ $isEdit ? 'Edit Section' : 'Add Section' }}</h2>
            <p>{{ $blockTypes[$block->block_type] ?? 'Homepage block' }}</p>
        </div>
        <a href="{{ route('admin.home-content.index') }}" class="lei-wm-btn-ghost">← Back to list</a>
    </div>

    @if ($errors->any())
        <div class="lei-wm-alert lei-wm-alert--error">
            <strong>Please fix the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $action }}" class="lei-wm-form-card" data-lei-home-content-form>
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="lei-wm-form-grid">
            <label>
                <span>Section Type</span>
                <select name="block_type" required @disabled($isEdit && in_array($block->block_type, ['intro', 'reasons', 'benefits', 'mandatory'], true))>
                    @foreach ($blockTypes as $key => $label)
                        <option value="{{ $key }}" @selected(old('block_type', $block->block_type) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @if ($isEdit && in_array($block->block_type, ['intro', 'reasons', 'benefits', 'mandatory'], true))
                    <input type="hidden" name="block_type" value="{{ $block->block_type }}">
                @endif
            </label>

            <label>
                <span>Display Order</span>
                <input type="number" name="sort_order" min="0" max="999" value="{{ old('sort_order', $block->sort_order) }}" required>
            </label>

            <label class="lei-wm-form-check">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $block->is_active))>
                <span>Show on homepage</span>
            </label>
        </div>

        <label>
            <span>Title</span>
            <input type="text" name="title" value="{{ old('title', $block->title) }}" maxlength="255">
        </label>

        @if (in_array($block->block_type, ['intro', 'category', 'reasons'], true))
            <label>
                <span>{{ $block->block_type === 'intro' ? 'List Heading (before categories)' : ($block->block_type === 'reasons' ? 'Intro Line' : 'Sub-heading (optional)') }}</span>
                <input type="text" name="subtitle" value="{{ old('subtitle', $block->subtitle) }}" maxlength="500" placeholder="e.g. Organizations that:">
            </label>
        @endif

        @if ($block->block_type === 'category')
            <label>
                <span>Category Number</span>
                <input type="number" name="category_number" min="1" max="99" value="{{ old('category_number', $block->category_number) }}">
            </label>
        @endif

        @if (in_array($block->block_type, ['intro', 'mandatory'], true))
            <label>
                <span>Body Text</span>
                <textarea name="body" rows="6">{{ old('body', $block->body) }}</textarea>
            </label>
        @endif

        @if ($block->block_type === 'benefits')
            <div class="lei-wm-repeat" data-lei-repeat="benefits">
                <div class="lei-wm-repeat-head">
                    <strong>Benefit Items</strong>
                    <button type="button" class="lei-wm-btn-secondary" data-lei-add-benefit>Add Benefit</button>
                </div>
                <div class="lei-wm-repeat-list" data-lei-benefit-list>
                    @foreach ($benefitItems as $i => $row)
                        <div class="lei-wm-repeat-row lei-wm-repeat-row--benefit" data-lei-repeat-row>
                            <input type="text" name="benefit_items[{{ $i }}][title]" value="{{ $row['title'] ?? '' }}" placeholder="Benefit title">
                            <textarea name="benefit_items[{{ $i }}][text]" rows="2" placeholder="Description">{{ $row['text'] ?? '' }}</textarea>
                            <button type="button" class="lei-wm-btn-danger lei-wm-btn-sm" data-lei-remove-row aria-label="Remove">&times;</button>
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif (in_array($block->block_type, ['category', 'reasons'], true))
            <div class="lei-wm-repeat" data-lei-repeat="items">
                <div class="lei-wm-repeat-head">
                    <strong>List Items</strong>
                    <button type="button" class="lei-wm-btn-secondary" data-lei-add-item>Add Item</button>
                </div>
                <div class="lei-wm-repeat-list" data-lei-item-list>
                    @foreach ($items as $i => $item)
                        <div class="lei-wm-repeat-row" data-lei-repeat-row>
                            <input type="text" name="items[{{ $i }}]" value="{{ $item }}" placeholder="List item">
                            <button type="button" class="lei-wm-btn-danger lei-wm-btn-sm" data-lei-remove-row aria-label="Remove">&times;</button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="lei-wm-form-actions">
            <a href="{{ route('admin.home-content.index') }}" class="lei-wm-btn-ghost">Cancel</a>
            <button type="submit" class="lei-wm-btn-primary">Save Section</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/lei-home-content-admin.js') }}?v=1"></script>
@endpush

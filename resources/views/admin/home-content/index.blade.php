@extends('admin.layouts.app')

@section('title', 'Homepage LEI Content')
@section('body_class', 'lei-page-website-admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-website-admin.css') }}?v=4">
@endpush

@section('breadcrumbs')
    @include('admin.partials.breadcrumbs', ['current' => 'Homepage LEI Content'])
@endsection

@section('content')
<div class="lei-wm-page">
    <div class="lei-wm-head">
        <div>
            <h2>Homepage LEI Content</h2>
            <p>Manage the “Who Needs an LEI?” guide, entity categories, reasons, benefits, and mandatory notice on the landing page.</p>
        </div>
        <div class="lei-wm-head-actions">
            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="lei-wm-btn-ghost">View Homepage</a>
            <a href="{{ route('admin.home-content.create', ['type' => 'category']) }}" class="lei-wm-btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Category
            </a>
        </div>
    </div>

    <div class="lei-wm-stats">
        <div class="lei-wm-stat"><span>Total Sections</span><strong>{{ $stats['total'] }}</strong></div>
        <div class="lei-wm-stat lei-wm-stat--ok"><span>Active</span><strong>{{ $stats['active'] }}</strong></div>
        <div class="lei-wm-stat"><span>Entity Categories</span><strong>{{ $stats['categories'] }}</strong></div>
    </div>

    <div class="lei-wm-table-card">
        <div class="lei-wm-table-scroll">
            <table class="lei-wm-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Type</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($blocks as $block)
                        <tr>
                            <td>{{ $block->sort_order }}</td>
                            <td><span class="lei-wm-badge">{{ $block->typeLabel() }}</span></td>
                            <td>
                                <strong>{{ $block->title ?? '—' }}</strong>
                                @if ($block->block_type === 'category' && $block->category_number)
                                    <span class="lei-wm-muted">#{{ $block->category_number }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($block->is_active)
                                    <span class="lei-wm-status lei-wm-status--ok">Active</span>
                                @else
                                    <span class="lei-wm-status lei-wm-status--muted">Hidden</span>
                                @endif
                            </td>
                            <td>
                                @include('admin.partials.icon-actions', [
                                    'editUrl' => route('admin.home-content.edit', $block),
                                    'deleteUrl' => $block->block_type === 'category' ? route('admin.home-content.destroy', $block) : null,
                                    'deleteConfirm' => 'Remove this category from the homepage?',
                                    'deleteTitle' => 'Remove Section',
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="lei-wm-empty">
                                No content yet.
                                <a href="{{ route('admin.home-content.create') }}">Add a section</a>
                                or run <code>php artisan db:seed --class=HomeLeiContentSeeder</code>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

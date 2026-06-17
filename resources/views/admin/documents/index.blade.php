@extends('admin.layouts.app')

@section('title', 'Documents')
@section('body_class', 'lei-page-documents')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-file-icons.css') }}?v=2">
<link rel="stylesheet" href="{{ asset('css/lei-documents.css') }}?v=2">
@endpush

@section('content')
<div class="lei-doc-page"
     data-doc-url="{{ rtrim(config('app.url'), '/') }}/admin/documents/items/__ID__"
     data-verify-url="{{ $selected ? route('admin.documents.verify', $selected) : '' }}"
     data-reject-url="{{ $selected ? route('admin.documents.reject', $selected) : '' }}"
     data-filter-url="{{ route('admin.documents.index') }}">

    <div id="leiDocToast" class="lei-doc-toast" hidden></div>

    @if ($statCards->isEmpty() || !$config)
        <div class="lei-doc-empty">Run <code>php artisan db:seed --class=DocumentManagementSeeder</code></div>
    @else

    <div class="lei-doc-stats-row" id="leiDocStatsRow">
        @foreach ($statCards as $stat)
            <div class="lei-doc-stat-card" data-stat-key="{{ $stat->stat_key }}">
                <div class="lei-doc-stat-icon lei-doc-stat-icon--{{ $stat->icon_tone }}">
                    @if ($stat->stat_key === 'pending_verification')
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg>
                    @elseif ($stat->stat_key === 'malware_detected')
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    @elseif ($stat->stat_key === 'avg_sla')
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    @else
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    @endif
                </div>
                <div class="lei-doc-stat-body">
                    <span class="lei-doc-stat-label">{{ $stat->label }}</span>
                    <strong class="lei-doc-stat-value lei-doc-stat-value--{{ $stat->icon_tone }}">{{ $stat->value }}</strong>
                </div>
            </div>
        @endforeach
    </div>

    <div class="lei-doc-workspace">
        <div class="lei-doc-main-col">
            <div class="lei-doc-card lei-doc-queue-card">
                <div class="lei-doc-card-head">
                    <div class="lei-doc-card-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <h2>Active Document Queue</h2>
                    </div>
                    <div class="lei-doc-card-actions">
                        <form method="GET" action="{{ route('admin.documents.index') }}" class="lei-doc-filter-form" id="leiDocFilterForm">
                            <input type="hidden" name="doc" value="{{ $selected?->id }}">
                            <select name="type" id="leiDocTypeFilter">
                                <option value="all" {{ $filterType === 'all' ? 'selected' : '' }}>Filter By Type</option>
                                <option value="pdf" {{ $filterType === 'pdf' ? 'selected' : '' }}>PDF</option>
                                <option value="docx" {{ $filterType === 'docx' ? 'selected' : '' }}>Word</option>
                                <option value="jpg" {{ $filterType === 'jpg' ? 'selected' : '' }}>Image</option>
                            </select>
                        </form>
                        <button type="button" class="lei-doc-btn-config" id="leiDocConfigure">Configure Rules</button>
                    </div>
                </div>
                <div class="lei-doc-table" id="leiDocTable">
                    <div class="lei-doc-row lei-doc-row--head">
                        <span>Document ID</span>
                        <span class="lei-doc-col-filename">File Name</span>
                        <span>Security</span>
                        <span>Status</span>
                    </div>
                    @foreach ($documents as $doc)
                        <a href="{{ route('admin.documents.index', ['doc' => $doc->id, 'type' => $filterType]) }}"
                           class="lei-doc-row js-doc-row {{ $selected && $selected->id === $doc->id ? 'lei-doc-row--active' : '' }}"
                           data-doc-id="{{ $doc->id }}">
                            <span class="lei-doc-code">{{ $doc->document_code }}</span>
                            <span class="lei-doc-file lei-file-name-cell">
                                @include('admin.partials.file-type-icon', ['type' => $doc->file_type])
                                <span>{{ $doc->file_name }}</span>
                            </span>
                            <span class="lei-doc-security lei-doc-security--{{ $doc->security_tone }}">
                                @if ($doc->security_tone === 'clean')
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                @endif
                                {{ $doc->security_label }}
                            </span>
                            <span><span class="lei-doc-status lei-doc-status--{{ $doc->status_tone }}">{{ $doc->status }}</span></span>
                        </a>
                    @endforeach
                </div>
            </div>

            <div id="leiDocViewerWrap">
                @if ($selected)
                    @include('admin.documents.partials.viewer', ['selected' => $selected])
                @endif
            </div>
        </div>

        <div id="leiDocSideWrap">
            @if ($selected)
                @include('admin.documents.partials.side-panel', ['selected' => $selected, 'config' => $config])
            @endif
        </div>
    </div>

    @endif
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/lei-documents.js') }}?v=1"></script>
@endpush

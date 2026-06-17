<div class="lei-doc-viewer-card" id="leiDocViewer">
    <div class="lei-doc-viewer-head">
        <h3>Active Viewer: <span id="leiDocViewerName">{{ $selected->file_name }}</span></h3>
        <div class="lei-doc-viewer-tools">
            <span class="lei-doc-meta-tab">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Encrypted Metadata
            </span>
            <button type="button" class="lei-doc-tool-btn" id="leiDocZoomIn" aria-label="Zoom in">+</button>
            <button type="button" class="lei-doc-tool-btn" id="leiDocZoomOut" aria-label="Zoom out">−</button>
            <a href="{{ $selected->preview_url ?: '#' }}" class="lei-doc-tool-btn" id="leiDocDownload" target="_blank" rel="noopener" aria-label="Download" {{ $selected->preview_url ? '' : 'hidden' }}>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            </a>
        </div>
    </div>
    <div class="lei-doc-preview-wrap">
        @if ($selected->preview_url)
            <img src="{{ $selected->preview_url }}" alt="{{ $selected->file_name }}" class="lei-doc-preview-img" id="leiDocPreviewImg">
        @else
            <div class="lei-doc-preview-placeholder" id="leiDocPreviewPlaceholder">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span>No preview available</span>
            </div>
        @endif
    </div>
</div>

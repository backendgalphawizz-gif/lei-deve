@extends('admin.layouts.app')

@section('title', 'Registry Management')
@section('body_class', 'lei-page-registry')

@section('content')
<div class="lei-reg-page"
     data-save-url="{{ route('admin.registry.save') }}"
     data-publish-url="{{ route('admin.registry.publish') }}"
     data-sandbox-url="{{ route('admin.registry.sandbox') }}">

    <div id="leiRegToast" class="lei-reg-toast" hidden></div>

    <div class="lei-reg-head">
        <h1>Registry Management</h1>
        <div class="lei-reg-head-actions">
            <button type="button" class="lei-reg-btn-discard" id="leiRegDiscard">Discard</button>
            <button type="button" class="lei-reg-btn-publish" id="leiRegPublish">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><polyline points="9 15 12 12 15 15"/></svg>
                Publish Template
            </button>
        </div>
    </div>

    @if (!$template)
        <div class="lei-reg-empty">Run <code>php artisan db:seed --class=RegistryManagementSeeder</code></div>
    @else
    @php
        $formats = $template->file_formats ?? ['pdf', 'docx'];
    @endphp

    <div class="lei-reg-top-row">
        <div class="lei-reg-card">
            <h2 class="lei-reg-card-title lei-reg-card-title--core">
                <span class="lei-reg-accent lei-reg-accent--core"></span>
                Core Configuration
            </h2>
            <div class="lei-reg-form">
                <div class="lei-reg-field">
                    <label>DOCUMENT NAME</label>
                    <input type="text" id="leiRegDocName" value="{{ $template->document_name }}" placeholder="e.g., Certificate of Incorporation">
                    <small>Ensure naming matches official institutional nomenclature.</small>
                </div>
                <div class="lei-reg-field-row">
                    <div class="lei-reg-field">
                        <label>PRIMARY CATEGORY</label>
                        <select id="leiRegPrimary">
                            @foreach ($primaryCategories as $key => $label)
                                <option value="{{ $key }}" @selected($template->primary_category === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lei-reg-field">
                        <label>SUB-CATEGORY (OPTIONAL)</label>
                        <select id="leiRegSub">
                            @foreach ($subCategories as $key => $label)
                                <option value="{{ $key }}" @selected($template->sub_category === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="lei-reg-toggle-row">
                    <div class="lei-reg-toggle-item">
                        <div>
                            <strong>Mandatory Flag</strong>
                            <span>Require for all registry entries</span>
                        </div>
                        <label class="lei-reg-switch">
                            <input type="checkbox" id="leiRegMandatory" {{ $template->mandatory_flag ? 'checked' : '' }}>
                            <span class="lei-reg-switch-track"></span>
                        </label>
                    </div>
                    <div class="lei-reg-toggle-item">
                        <div>
                            <strong>OCR Verification</strong>
                            <span>Enable automated text extraction</span>
                        </div>
                        <label class="lei-reg-switch">
                            <input type="checkbox" id="leiRegOcr" {{ $template->ocr_verification ? 'checked' : '' }}>
                            <span class="lei-reg-switch-track"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="lei-reg-card">
            <h2 class="lei-reg-card-title lei-reg-card-title--gold">
                <span class="lei-reg-accent lei-reg-accent--gold"></span>
                Validation Rules
            </h2>
            <div class="lei-reg-form">
                <div class="lei-reg-field">
                    <label>PERMITTED FILE FORMATS</label>
                    <div class="lei-reg-format-grid" id="leiRegFormats">
                        @foreach ($formatOptions as $key => $label)
                            <button type="button"
                                    class="lei-reg-format-pill {{ in_array($key, $formats) ? 'active' : '' }}"
                                    data-format="{{ $key }}">
                                @if (in_array($key, $formats))
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                @endif
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>
                <div class="lei-reg-field">
                    <label>MAXIMUM FILE SIZE</label>
                    <div class="lei-reg-slider-wrap">
                        <div class="lei-reg-slider-inner">
                            <input type="range" id="leiRegMaxSize" min="1" max="50" value="{{ $template->max_file_size_mb }}">
                            <div class="lei-reg-slider-track">
                                <span class="lei-reg-slider-fill" id="leiRegSliderFill"></span>
                            </div>
                        </div>
                        <span class="lei-reg-size-badge" id="leiRegSizeBadge">{{ $template->max_file_size_mb }} MB</span>
                    </div>
                </div>
                <div class="lei-reg-alert">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <p>High-security document types are limited to 50MB regardless of global registry settings.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="lei-reg-card lei-reg-card--wide">
        <div class="lei-reg-card-wide-head">
            <h2 class="lei-reg-card-title lei-reg-card-title--core">
                <span class="lei-reg-accent lei-reg-accent--core"></span>
                Advanced Workflow parameters
            </h2>
            <span class="lei-reg-pill">Expert Mode Only</span>
        </div>
        <div class="lei-reg-advanced-row">
            <div class="lei-reg-advanced-col">
                <label class="lei-reg-col-label">VERSIONING CONTROL</label>
                <label class="lei-reg-radio">
                    <input type="radio" name="versioning" value="audit_trail" {{ $template->versioning_mode === 'audit_trail' ? 'checked' : '' }}>
                    <span>Maintain full audit trail</span>
                </label>
                <label class="lei-reg-radio">
                    <input type="radio" name="versioning" value="overwrite" {{ $template->versioning_mode === 'overwrite' ? 'checked' : '' }}>
                    <span>Overwrite legacy copies</span>
                </label>
            </div>
            <div class="lei-reg-advanced-col">
                <label class="lei-reg-col-label">APPROVAL FLOW</label>
                <select id="leiRegApproval">
                    @foreach ($approvalFlows as $key => $label)
                        <option value="{{ $key }}" @selected($template->approval_flow === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lei-reg-advanced-col">
                <label class="lei-reg-col-label">SECURITY TIER</label>
                <div class="lei-reg-tier-row" id="leiRegTiers">
                    <button type="button" class="lei-reg-tier lei-reg-tier--standard {{ $template->security_tier === 'standard' ? 'active' : '' }}" data-tier="standard">Standard</button>
                    <button type="button" class="lei-reg-tier lei-reg-tier--encrypted {{ $template->security_tier === 'encrypted' ? 'active' : '' }}" data-tier="encrypted">Encrypted</button>
                    <button type="button" class="lei-reg-tier lei-reg-tier--air {{ $template->security_tier === 'air_gapped' ? 'active' : '' }}" data-tier="air_gapped">Air-Gapped</button>
                </div>
            </div>
        </div>
    </div>

    <div class="lei-reg-status-bar">
        <span class="lei-reg-status-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            Live Preview: Default Form Template
        </span>
        <span class="lei-reg-status-item" id="leiRegModified">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Last modified by {{ $template->last_modified_by }} at {{ $template->last_modified_at?->format('h:i A') ?? '—' }}
        </span>
        <button type="button" class="lei-reg-btn-sandbox" id="leiRegSandbox">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18h8M4 14h10M10 10h8M6 6h12"/></svg>
            Run Validation Sandbox
        </button>
    </div>
    @endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-registry.css') }}?v=1">
@endpush

@push('scripts')
<script src="{{ asset('js/lei-registry.js') }}?v=1"></script>
@endpush

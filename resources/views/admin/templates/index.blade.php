@extends('admin.layouts.app')

@section('title', 'Template Management')
@section('body_class', 'lei-page-templates')

@section('content')
<div class="lei-tpl-page"
     data-save-url="{{ route('admin.templates.save') }}"
     data-state-url="{{ route('admin.templates.states.store') }}">

    <div id="leiTplToast" class="lei-tpl-toast" hidden></div>

    <div class="lei-tpl-head">
        <div class="lei-tpl-head-text">
            <h1>Template Management</h1>
            <p>Define systemic operational flows and SLA enforcement rules.</p>
        </div>
        <div class="lei-tpl-head-actions">
            <button type="button" class="lei-tpl-btn-cancel" id="leiTplCancel">Cancel</button>
            <button type="button" class="lei-tpl-btn-save" id="leiTplSave">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save
            </button>
        </div>
    </div>

    @if (!$template)
        <div class="lei-tpl-empty">
            <p>No workflow template found.</p>
            <code>php artisan db:seed --class=TemplateManagementSeeder</code>
        </div>
    @else
    <div class="lei-tpl-main">
    <div class="lei-tpl-workspace">
        <div class="lei-tpl-left">
            <div class="lei-tpl-card lei-tpl-card--basic">
                <div class="lei-tpl-card-title">
                    <span class="lei-tpl-card-icon lei-tpl-card-icon--gold">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    </span>
                    <h2>Basic Parameters</h2>
                </div>
                <div class="lei-tpl-form">
                    <div class="lei-tpl-field">
                        <label>WORKFLOW NAME</label>
                        <input type="text" id="leiTplName" value="{{ $template->name }}" placeholder="e.g., Enterprise Security Clearance">
                    </div>
                    <div class="lei-tpl-field">
                        <label>ASSOCIATED MODULE</label>
                        <select id="leiTplModule">
                            @foreach ($modules as $key => $label)
                                <option value="{{ $key }}" @selected($template->module === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lei-tpl-field-row">
                        <div class="lei-tpl-field">
                            <label>INITIAL STATE</label>
                            <div class="lei-tpl-input-locked">
                                <input type="text" value="{{ $template->initial_state }}" readonly>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </div>
                        </div>
                        <div class="lei-tpl-field">
                            <label>SLA TARGET (HOURS)</label>
                            <input type="number" id="leiTplSla" min="1" max="720" value="{{ $template->sla_hours }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="lei-tpl-card lei-tpl-card--summary">
                <span class="lei-tpl-summary-watermark" aria-hidden="true">
                    <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                </span>
                <h2>Technical Summary</h2>
                <dl class="lei-tpl-summary-list">
                    <div><dt>Total Nodes:</dt><dd id="leiTplTotalNodes" class="lei-tpl-summary-tag">{{ $template->total_nodes_label }}</dd></div>
                    <div><dt>Escalation Depth:</dt><dd>{{ $template->escalation_depth }}</dd></div>
                    <div><dt>Automation Tier:</dt><dd>{{ $template->automation_tier }}</dd></div>
                </dl>
            </div>
        </div>

        <div class="lei-tpl-right">
            <div class="lei-tpl-card lei-tpl-card--flow">
                <div class="lei-tpl-flow-head">
                    <div class="lei-tpl-card-title">
                        <span class="lei-tpl-card-icon lei-tpl-card-icon--gold">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><path d="M10 7h4M17 10v4M7 10v4M10 17h4"/></svg>
                        </span>
                        <h2>State Transitions Logic</h2>
                    </div>
                    <button type="button" class="lei-tpl-btn-add-state" id="leiTplAddState">+ Add State</button>
                </div>

                <div class="lei-tpl-flow-canvas">
                    <div class="lei-tpl-flow-track" id="leiTplFlowCanvas">
                        @php $flowFirst = true; @endphp
                        @foreach ($states as $state)
                            @if (!$flowFirst)
                                <div class="lei-tpl-flow-vline" aria-hidden="true"></div>
                            @endif
                            @php $flowFirst = false; @endphp
                            @if ($state->rule_type === 'final_placeholder')
                                <div class="lei-tpl-node lei-tpl-node--placeholder" tabindex="0" role="button">
                                    <span class="lei-tpl-plus-ring"><span class="lei-tpl-plus-icon">+</span></span>
                                    <span>Define Final State</span>
                                </div>
                            @else
                                <div class="lei-tpl-node lei-tpl-node--{{ $state->accent }}" data-state-id="{{ $state->id }}">
                                    <span class="lei-tpl-node-accent lei-tpl-node-accent--{{ $state->accent }}" aria-hidden="true"></span>
                                    <div class="lei-tpl-node-body">
                                        @if ($state->rule_label)
                                            <span class="lei-tpl-node-label">{{ $state->rule_label }}</span>
                                        @endif
                                        <strong>{{ $state->title }}</strong>
                                        @if ($state->description)
                                            <p>{{ $state->description }}</p>
                                        @endif
                                    </div>
                                    <button type="button" class="lei-tpl-node-grip" aria-label="Reorder state">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>
                                    </button>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <div class="lei-tpl-flow-footer">
                        <div class="lei-tpl-legend">
                            <span><i class="lei-tpl-legend-swatch lei-tpl-legend-swatch--core"></i> Core</span>
                            <span><i class="lei-tpl-legend-swatch lei-tpl-legend-swatch--auto"></i> Automated</span>
                            <span><i class="lei-tpl-legend-swatch lei-tpl-legend-swatch--approval"></i> Approval</span>
                        </div>
                        <span class="lei-tpl-sync" id="leiTplSyncText">
                            Last synchronized with Registry Service at {{ $template->last_synced_at?->format('H:i') ?? '—' }} UTC
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="lei-tpl-info-row">
        <div class="lei-tpl-info-card">
            <span class="lei-tpl-info-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            </span>
            <div>
                <strong>Authoritative Rule</strong>
                <p>Initial states cannot be bypassed without L3 clearance. All transitions are logged at L2-level granularity for audit compliance.</p>
            </div>
        </div>
        <div class="lei-tpl-info-card lei-tpl-info-card--warn">
            <span class="lei-tpl-info-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </span>
            <div>
                <strong>SLA Criticality</strong>
                <p>Exceeding the {{ $template->sla_hours }}-hour SLA target triggers automatic escalation to the Executive Oversight Dashboard.</p>
            </div>
        </div>
        <div class="lei-tpl-info-card">
            <span class="lei-tpl-info-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
            </span>
            <div>
                <strong>Registry Hooks</strong>
                <p>Changes sync via the Template Manager API. Webhook endpoints fire on each state transition for cross-module registry sync.</p>
            </div>
        </div>
    </div>
    </div>
    @endif
</div>

<div class="lei-tpl-modal" id="leiTplStateModal" hidden>
    <div class="lei-tpl-modal-backdrop" data-close-tpl></div>
    <div class="lei-tpl-modal-box">
        <h3>Add Workflow State</h3>
        <form id="leiTplStateForm" class="lei-tpl-form">
            <div class="lei-tpl-field">
                <label>STATE TITLE</label>
                <input type="text" name="title" required placeholder="e.g., Compliance Review">
            </div>
            <div class="lei-tpl-field">
                <label>DESCRIPTION</label>
                <input type="text" name="description" placeholder="Brief step description">
            </div>
            <div class="lei-tpl-field">
                <label>RULE LABEL</label>
                <input type="text" name="rule_label" value="TRANSITION RULE: MANUAL">
            </div>
            <div class="lei-tpl-field">
                <label>ACCENT TYPE</label>
                <select name="accent">
                    <option value="core">Core (Initial)</option>
                    <option value="auto">Automated</option>
                    <option value="approval" selected>Approval (Manual)</option>
                </select>
            </div>
            <div class="lei-tpl-modal-actions">
                <button type="button" class="lei-tpl-btn-cancel" data-close-tpl>Cancel</button>
                <button type="submit" class="lei-tpl-btn-save lei-tpl-btn-save--sm">Add State</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-templates.css') }}?v=6">
@endpush

@push('scripts')
<script src="{{ asset('js/lei-templates.js') }}?v=4"></script>
@endpush

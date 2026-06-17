<div class="lei-doc-side-col" id="leiDocSideCol">
    <div class="lei-doc-card lei-doc-verify-card">
        <h3>Verification Action</h3>
        <form id="leiDocVerifyForm">
            <label class="lei-doc-label">Decision Reasoning</label>
            <textarea name="reason" rows="4" placeholder="Enter reason for approval or rejection...">{{ $selected->decision_reason }}</textarea>
            <button type="button" class="lei-doc-btn-verify" id="leiDocBtnVerify" {{ in_array($selected->status_tone, ['verified', 'rejected']) ? 'disabled' : '' }}>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Verify Document
            </button>
            <button type="button" class="lei-doc-btn-reject" id="leiDocBtnReject" {{ in_array($selected->status_tone, ['verified', 'rejected']) ? 'disabled' : '' }}>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Reject Application
            </button>
        </form>
    </div>

    <div class="lei-doc-card lei-doc-audit-card">
        <div class="lei-doc-audit-head">
            <h3>Audit Trail</h3>
            @if ($config)
                <span class="lei-doc-version">{{ $config->version_label }}</span>
            @endif
        </div>
        <div class="lei-doc-timeline" id="leiDocTimeline">
            @forelse ($selected->auditEvents as $event)
                <div class="lei-doc-timeline-item">
                    <span class="lei-doc-timeline-dot lei-doc-timeline-dot--{{ $event->indicator_tone }} {{ $event->is_in_progress ? 'lei-doc-timeline-dot--hollow' : '' }}"></span>
                    <div class="lei-doc-timeline-body">
                        <strong>{{ $event->title }}</strong>
                        <span class="lei-doc-timeline-date">{{ $event->event_label }}</span>
                        @if ($event->description)
                            <p>{{ $event->description }}</p>
                        @endif
                    </div>
                </div>
            @empty
                <p class="lei-doc-timeline-empty">No audit events yet.</p>
            @endforelse
        </div>
    </div>

    @if ($config)
        <div class="lei-doc-ledger-card">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <p>{{ $config->ledger_text }}</p>
        </div>
    @endif
</div>

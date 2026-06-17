<div class="lei-app-detail-inner" data-app-id="{{ $application->id }}">
    <div class="lei-app-detail-head">
        <h3>Application Details</h3>
        <span class="lei-app-detail-id">ID: {{ $application->application_code }}</span>
    </div>

    <div class="lei-app-detail-meta">
        <div class="lei-app-meta-item">
            <span class="label">Country</span>
            <span class="value" data-field="country">{{ $application->country }}</span>
        </div>
        <div class="lei-app-meta-item">
            <span class="label">Type</span>
            <span class="value" data-field="issuance_type">{{ $application->issuance_type }}</span>
        </div>
        <!-- <div class="lei-app-meta-item">
            <span class="label">Priority</span>
            <span class="value" data-field="priority">{{ strtoupper($application->priority) }}</span>
        </div>
        <div class="lei-app-meta-item">
            <span class="label">Team</span>
            <span class="value" data-field="assigned_team">{{ $application->assigned_team }}</span>
        </div> -->
    </div>

    <div class="lei-app-audit">
        <h4>Audit History</h4>
        <div class="lei-app-timeline" data-audit-timeline>
            @foreach ($application->auditEvents as $event)
                <div class="lei-app-timeline-item {{ $event->is_highlight ? 'highlight' : '' }}">
                    <span class="lei-app-timeline-dot"></span>
                    <div class="lei-app-timeline-body">
                        <span class="time">{{ $event->occurred_at->format('M d, H:i') }}</span>
                        <p>{{ $event->description }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="lei-app-detail-actions">
        <button type="button" class="lei-app-btn-final" data-action="approve" {{ $application->status === 'approved' ? 'disabled' : '' }}>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Final Approval
        </button>
        <div class="lei-app-btn-row">
            <button type="button" class="lei-app-btn-secondary" data-action="clarify">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                Clarify
            </button>
            <button type="button" class="lei-app-btn-secondary" data-action="reassign">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Reassign
            </button>
        </div>
        <button type="button" class="lei-app-btn-reject" data-action="reject" {{ $application->status === 'rejected' ? 'disabled' : '' }}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
            Reject Application
        </button>
    </div>
</div>

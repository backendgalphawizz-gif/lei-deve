<aside class="lei-support-detail" id="leiSupportDetail">
    <div class="lei-support-detail-head">
        @if ($selected->is_urgent)
            <span class="lei-support-urgent">URGENT</span>
        @endif
        <span class="lei-support-detail-code">{{ $selected->ticket_code }}</span>
        <h3>{{ $selected->title }}</h3>
        <p class="lei-support-detail-meta">{{ $lastActivity }}</p>
    </div>

    <div class="lei-support-thread-block">
        <div class="lei-support-section-title">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            Communication Thread
        </div>
        <div class="lei-support-thread" id="leiSupportThread">
            @forelse ($selected->messages as $msg)
                <div class="lei-support-msg {{ $msg->is_outgoing ? 'lei-support-msg--out' : '' }}">
                    <div class="lei-support-avatar lei-support-avatar--{{ $msg->sender_tone }}">{{ $msg->sender_initials }}</div>
                    <div class="lei-support-bubble-wrap">
                        <div class="lei-support-bubble">{{ $msg->body }}</div>
                        <span class="lei-support-time">
                            {{ $msg->time_label }}
                            @if ($msg->is_outgoing && $msg->sender_name)
                                · {{ $msg->sender_name }}@if($msg->sender_role) [{{ $msg->sender_role }}]@endif
                            @endif
                        </span>
                    </div>
                </div>
            @empty
                <p class="lei-support-thread-empty">No messages yet. Start the conversation below.</p>
            @endforelse
        </div>
    </div>

    <form class="lei-support-compose" id="leiSupportCompose">
        <input type="text" name="body" placeholder="Type message to client..." autocomplete="off">
        <button type="submit" aria-label="Send">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        </button>
    </form>

    <div class="lei-support-notes">
        <div class="lei-support-section-title">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Admin Notes
        </div>
        <form id="leiSupportNoteForm">
            <textarea name="body" rows="3" placeholder="Private internal notes..."></textarea>
        </form>
        <div class="lei-support-notes-log" id="leiSupportNotesLog">
            @foreach ($selected->notes as $note)
                <div class="lei-support-note-entry">
                    <div class="lei-support-avatar lei-support-avatar--{{ $note->author_tone }}">{{ $note->author_initials }}</div>
                    <div class="lei-support-note-text">
                        <p><strong>{{ $note->author_name ?? 'Admin' }} added:</strong> <em>{{ $note->body }}</em></p>
                        <span class="lei-support-time">{{ $note->time_label }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="lei-support-actions">
        <button type="button" class="lei-support-action-btn" data-action="assign" {{ $selected->status === 'Closed' ? 'disabled' : '' }}>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
            Assign
        </button>
        <button type="button" class="lei-support-action-btn lei-support-action-btn--danger" data-action="escalate" {{ $selected->status === 'Closed' ? 'disabled' : '' }}>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Escalate
        </button>
        <button type="button" class="lei-support-action-btn" data-action="priority" {{ $selected->status === 'Closed' ? 'disabled' : '' }}>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            Priority
        </button>
        <button type="button" class="lei-support-action-btn lei-support-action-btn--close" data-action="close" {{ $selected->status === 'Closed' ? 'disabled' : '' }}>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Close
        </button>
    </div>
</aside>

@extends('applicant.layouts.app')

@section('title', $ticket->ticket_code)

@section('content')
@php
    $statusClass = match ($ticket->status_tone) {
        'progress' => 'orange',
        'open' => 'blue',
        'escalated' => 'red',
        'closed' => 'green',
        default => 'gray',
    };
@endphp

<div class="lei-portal-page-head">
    <div>
        <p class="lei-portal-ticket-meta"><a href="{{ route('applicant.support.index') }}" class="lei-btn-link">← All tickets</a></p>
        <h1>{{ $ticket->title }}</h1>
        <p>{{ $ticket->ticket_code }} &bull; {{ $ticket->category }} &bull; Opened {{ $ticket->created_at->format('M j, Y') }}</p>
    </div>
    <span class="lei-portal-badge {{ $statusClass }}">{{ $ticket->status }}</span>
</div>

<div class="lei-portal-split lei-portal-split--support">
    <div class="lei-portal-card lei-portal-support-thread">
        <h3>Conversation</h3>

        @if ($ticket->messages->isNotEmpty() && ! $hasStaffReply && $ticket->status !== 'Closed')
            <p class="lei-portal-support-waiting muted">Waiting for a response from our support team. You will see their reply in this thread.</p>
        @endif

        <div class="lei-portal-support-messages">
            @forelse ($ticket->messages as $threadMessage)
                <div class="lei-portal-support-message {{ $threadMessage->is_outgoing ? 'lei-portal-support-message--staff' : 'lei-portal-support-message--client' }}">
                    <div class="lei-portal-support-message-head">
                        <strong>
                            @if ($threadMessage->is_outgoing)
                                {{ $threadMessage->sender_name ? $threadMessage->sender_name . ' (Support Team)' : 'Support Team' }}
                            @else
                                {{ $threadMessage->sender_name ?? 'You' }}
                            @endif
                        </strong>
                        <span>{{ $threadMessage->created_at->format('M j, Y g:i A') }}</span>
                    </div>
                    <p>{{ $threadMessage->body }}</p>
                </div>
            @empty
                <p class="muted">No messages on this ticket yet.</p>
            @endforelse
        </div>

        @if ($ticket->status !== 'Closed')
            <form method="POST" action="{{ route('applicant.support.reply', $ticket) }}" class="lei-portal-support-reply">
                @csrf
                <div class="lei-portal-field">
                    <label for="reply_body">Add a reply</label>
                    <textarea id="reply_body" name="reply_body" rows="4" placeholder="Type your message..." required>{{ old('reply_body') }}</textarea>
                    @error('reply_body')<p class="lei-portal-field-error">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="lei-btn-primary">Send Reply</button>
            </form>
        @else
            <p class="lei-portal-support-closed muted">This ticket is closed. <a href="{{ route('applicant.support.create') }}">Raise a new ticket</a> if you need more help.</p>
        @endif
    </div>

    <aside class="lei-portal-card lei-portal-support-aside">
        <h3>Ticket Details</h3>
        <dl class="lei-portal-review-dl">
            <div>
                <dt>Ticket ID</dt>
                <dd>{{ $ticket->ticket_code }}</dd>
            </div>
            <div>
                <dt>Priority</dt>
                <dd>{{ $ticket->priority }}</dd>
            </div>
            <div>
                <dt>Category</dt>
                <dd>{{ $ticket->category }}</dd>
            </div>
            <div>
                <dt>Status</dt>
                <dd>{{ $ticket->status }}</dd>
            </div>
            <div class="full">
                <dt>Contact Email</dt>
                <dd>{{ $ticket->contact_email }}</dd>
            </div>
        </dl>
        <a href="{{ route('applicant.support.create') }}" class="lei-btn-secondary full"><i class="fa-solid fa-plus"></i> Raise New Ticket</a>
    </aside>
</div>
@endsection

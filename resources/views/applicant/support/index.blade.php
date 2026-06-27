@extends('applicant.layouts.app')

@section('title', 'Support Tickets')

@section('content')
<div class="lei-portal-page-head">
    <div>
        <h1>Support Tickets</h1>
        <p>View and manage your support inquiries. Raise a ticket anytime you need help with registration, payments, or technical issues.</p>
    </div>
    <a href="{{ route('applicant.support.create') }}" class="lei-btn-primary"><i class="fa-solid fa-plus"></i> Raise New Ticket</a>
</div>

<div class="lei-portal-stats">
    <div class="lei-portal-stat"><strong>{{ $stats['total'] }}</strong><span>Total Tickets</span></div>
    <div class="lei-portal-stat"><strong>{{ $stats['in_progress'] }}</strong><span>In Progress</span></div>
    <div class="lei-portal-stat"><strong>{{ $stats['awaiting'] }}</strong><span>Awaiting Action</span></div>
    <div class="lei-portal-stat"><strong>{{ $stats['resolved'] }}</strong><span>Resolved</span></div>
</div>

<div class="lei-portal-card">
    @if ($tickets->isEmpty())
        <div class="lei-portal-empty-state">
            <i class="fa-regular fa-life-ring"></i>
            <h3>No support tickets yet</h3>
            <p>Need help with your LEI application, renewal, or payment? Raise a ticket and our team will get back to you.</p>
            <a href="{{ route('applicant.support.create') }}" class="lei-btn-primary"><i class="fa-solid fa-plus"></i> Raise Your First Ticket</a>
        </div>
    @else
        <div class="lei-portal-table-wrap">
            <table class="lei-portal-table lei-portal-table--responsive">
                <thead>
                    <tr>
                        <th>Ticket ID</th>
                        <th>Subject</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tickets as $ticket)
                        @php
                            $statusClass = match ($ticket->status_tone) {
                                'progress' => 'orange',
                                'open' => 'blue',
                                'escalated' => 'red',
                                'closed' => 'green',
                                default => 'gray',
                            };
                            $priorityClass = match ($ticket->priority_tone) {
                                'high' => 'high',
                                'medium' => 'medium',
                                default => 'low',
                            };
                        @endphp
                        <tr>
                            <td data-label="Ticket ID"><strong>{{ $ticket->ticket_code }}</strong></td>
                            <td data-label="Subject">{{ $ticket->title }}</td>
                            <td data-label="Category">{{ $ticket->category }}</td>
                            <td data-label="Priority">
                                <span class="lei-portal-priority">
                                    <span class="lei-portal-priority-dot {{ $priorityClass }}"></span>
                                    {{ $ticket->priority }}
                                </span>
                            </td>
                            <td data-label="Status"><span class="lei-portal-badge {{ $statusClass }}">{{ $ticket->status }}</span></td>
                            <td data-label="Last Updated" class="muted">{{ $ticket->updated_at->diffForHumans() }}</td>
                            <td data-label="Actions">
                                <a href="{{ route('applicant.support.show', $ticket) }}" class="lei-btn-link">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

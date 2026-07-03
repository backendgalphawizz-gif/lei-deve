@extends('admin.layouts.app')

@section('title', 'Contact Enquiries')
@section('body_class', 'lei-page-website-admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-website-admin.css') }}?v=2">
@endpush

@section('breadcrumbs')
    @include('admin.partials.breadcrumbs', ['current' => 'Contact Enquiries'])
@endsection

@section('content')
<div class="lei-wm-page">
    <div class="lei-wm-head">
        <div>
            <h2>Contact Enquiries</h2>
            <p>Messages submitted from the public Contact Us form.</p>
        </div>
        <a href="{{ route('contact') }}" target="_blank" class="lei-wm-btn-primary">View Contact Page</a>
    </div>

    <div class="lei-wm-stats">
        <div class="lei-wm-stat"><span>Total Enquiries</span><strong>{{ $stats['total'] }}</strong></div>
        <div class="lei-wm-stat lei-wm-stat--warn"><span>New</span><strong>{{ $stats['new'] }}</strong></div>
        <div class="lei-wm-stat lei-wm-stat--ok"><span>Replied</span><strong>{{ $stats['replied'] }}</strong></div>
    </div>

    <div class="lei-wm-split">
        <div class="lei-wm-card">
            <form method="GET" action="{{ route('admin.contact-enquiries.index') }}" class="lei-wm-toolbar">
                <div class="lei-wm-search">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search name, email, subject...">
                </div>
                <label>Status
                    <select name="status" onchange="this.form.submit()">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $key => $label)
                            <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                @if ($selected)<input type="hidden" name="id" value="{{ $selected->id }}">@endif
            </form>

            <div class="lei-wm-table-head">
                <div class="lei-wm-col lei-wm-col--wide">Name</div>
                <div class="lei-wm-col">Subject</div>
                <div class="lei-wm-col lei-wm-col--sm">Status</div>
                <div class="lei-wm-col lei-wm-col--sm">Date</div>
            </div>

            @forelse ($submissions as $row)
                <a href="{{ route('admin.contact-enquiries.index', array_merge(request()->query(), ['id' => $row->id])) }}"
                   class="lei-wm-table-row {{ $selected && $selected->id === $row->id ? 'is-active' : '' }}">
                    <div class="lei-wm-col lei-wm-col--wide"><strong>{{ $row->full_name }}</strong><small>{{ $row->email }}</small></div>
                    <div class="lei-wm-col">{{ Str::limit($row->subject, 36) }}</div>
                    <div class="lei-wm-col lei-wm-col--sm"><span class="lei-wm-badge lei-wm-badge--{{ $row->status }}">{{ $statuses[$row->status] ?? $row->status }}</span></div>
                    <div class="lei-wm-col lei-wm-col--sm">{{ $row->created_at->format('M j, Y') }}</div>
                </a>
            @empty
                <p class="lei-wm-empty">No enquiries yet. Submissions from the contact form will appear here.</p>
            @endforelse

            @if ($submissions->hasPages())
                <div class="lei-wm-pagination">{{ $submissions->links() }}</div>
            @endif
        </div>

        <div class="lei-wm-card">
            @if ($selected)
                <div class="lei-wm-card-head">
                    <h3>Enquiry Detail</h3>
                    <span class="lei-wm-badge lei-wm-badge--{{ $selected->status }}">{{ $statuses[$selected->status] ?? $selected->status }}</span>
                </div>
                <div class="lei-wm-card-body">
                    <div class="lei-wm-detail-grid">
                        <div class="lei-wm-detail-item"><span>Name</span><p>{{ $selected->full_name }}</p></div>
                        <div class="lei-wm-detail-item"><span>Email</span><p><a href="mailto:{{ $selected->email }}">{{ $selected->email }}</a></p></div>
                        <div class="lei-wm-detail-item"><span>Subject</span><p>{{ $selected->subject }}</p></div>
                        <div class="lei-wm-detail-item"><span>Submitted</span><p>{{ $selected->created_at->format('M j, Y g:i A') }}</p></div>
                        <div class="lei-wm-detail-item lei-wm-detail-item--full">
                            <span>Message</span>
                            <div class="lei-wm-message-box">{{ $selected->message }}</div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.contact-enquiries.update', $selected) }}" class="lei-wm-form">
                        @csrf @method('PUT')
                        <label>Status
                            <select name="status">
                                @foreach ($statuses as $key => $label)
                                    <option value="{{ $key }}" @selected($selected->status === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Admin Notes<textarea name="admin_notes" rows="4" placeholder="Internal notes about this enquiry..." data-rules="maxLen:5000">{{ $selected->admin_notes }}</textarea></label>
                        <div class="lei-wm-form-actions">
                            <button type="submit" class="lei-wm-btn-primary">Update Enquiry</button>
                        </div>
                    </form>
                    <div class="lei-wm-detail-actions">
                        @include('admin.partials.icon-actions', [
                            'deleteUrl' => route('admin.contact-enquiries.destroy', $selected),
                            'deleteConfirm' => 'Delete this enquiry?',
                            'deleteTitle' => 'Delete Enquiry',
                        ])
                    </div>
                </div>
            @else
                <div class="lei-wm-card-body">
                    <p class="lei-wm-empty">Select an enquiry from the list to view details.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

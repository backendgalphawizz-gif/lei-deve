@extends('applicant.layouts.app')

@section('title', 'My Applications')

@section('content')
<div class="lei-portal-page-head">
    <div>
        <h1>My Applications</h1>
        <p>Track and manage your Legal Entity Identifier registrations and renewals.</p>
    </div>
</div>

<div class="lei-portal-stats">
    <div class="lei-portal-stat"><strong>{{ $stats['pending'] }}</strong><span>Pending Actions</span></div>
    <div class="lei-portal-stat"><strong>{{ $stats['in_review'] }}</strong><span>In Review</span></div>
    <div class="lei-portal-stat"><strong>{{ $stats['approved'] }}</strong><span>Approved</span></div>
    <div class="lei-portal-stat"><strong>{{ $stats['clarification'] }}</strong><span>Clarification Required</span></div>
</div>

<div class="lei-portal-card">
    <div class="lei-portal-table-wrap">
        <table class="lei-portal-table lei-portal-table--responsive">
        <thead>
            <tr>
                <th>Application ID</th>
                <th>Entity Name</th>
                <th>Type</th>
                <th>LEI Code</th>
                <th>Submission Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($applications as $application)
                <tr>
                    <td data-label="Application ID">#{{ $application->application_code }}</td>
                    <td data-label="Entity Name">{{ $application->entity_name }}</td>
                    <td data-label="Type">{{ ucfirst(str_replace('_', ' ', $application->application_type)) }}</td>
                    <td data-label="LEI Code">
                        @if ($application->lei_number)
                            <span class="lei-portal-mono" style="font-size:12px;letter-spacing:0.06em;">{{ $application->lei_number }}</span>
                        @else
                            <span style="color:#94a3b8;font-size:12px;">Not assigned yet</span>
                        @endif
                    </td>
                    <td data-label="Submission Date">{{ $application->submitted_on?->format('M j, Y') ?? $application->created_at?->format('M j, Y') }}</td>
                    <td data-label="Status"><span class="lei-portal-badge {{ $application->status_tone }}">{{ $application->status_label }}</span></td>
                    <td data-label="Action"><a href="{{ route('applicant.applications.show', $application) }}" class="lei-btn-link">Track Status</a></td>
                </tr>
            @empty
                <tr><td colspan="7">No submitted applications yet.</td></tr>
            @endforelse
        </tbody>
        </table>
    </div>
</div>
@endsection

@extends('applicant.layouts.app')

@section('title', 'My Transfers')

@section('content')
<div class="lei-portal-page-head">
    <div>
        <h1>My Transfers</h1>
        <p>View and manage LEI transfer requests between Local Operating Units.</p>
    </div>
    <a href="#" class="lei-btn-primary"><i class="fa-solid fa-plus"></i> Transfer New LEI</a>
</div>

<div class="lei-portal-stats">
    <div class="lei-portal-stat"><strong>{{ $stats['total'] }}</strong><span>Total</span></div>
    <div class="lei-portal-stat"><strong>{{ $stats['in_progress'] }}</strong><span>In Progress</span></div>
    <div class="lei-portal-stat"><strong>{{ $stats['action_required'] }}</strong><span>Action Required</span></div>
    <div class="lei-portal-stat"><strong>{{ $stats['completed'] }}</strong><span>Completed</span></div>
</div>

<div class="lei-portal-card">
    <div class="lei-portal-table-wrap">
        <table class="lei-portal-table">
        <thead>
            <tr>
                <th>Entity Name</th>
                <th>LEI Number</th>
                <th>Current LOU</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transfers as $transfer)
                <tr>
                    <td><strong>{{ $transfer['entity'] }}</strong><div class="muted">{{ $transfer['country'] }}</div></td>
                    <td>{{ $transfer['lei'] }}</td>
                    <td>{{ $transfer['lou'] }}</td>
                    <td><span class="lei-portal-badge {{ $transfer['status'] === 'action_required' ? 'red' : ($transfer['status'] === 'in_progress' ? 'blue' : 'gray') }}">{{ str_replace('_', ' ', ucfirst($transfer['status'])) }}</span></td>
                    <td>{{ $transfer['submitted'] }}</td>
                    <td><a href="#" class="lei-btn-link">View Details</a></td>
                </tr>
            @endforeach
        </tbody>
        </table>
    </div>
</div>
@endsection

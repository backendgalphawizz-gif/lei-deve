@extends('applicant.layouts.app')

@section('title', 'My Entities')

@php
    $registrationSubscription = app(\App\Services\ApplicantApplicationService::class)->registrationSubscription(auth()->user());
    $registerUrl = $registrationSubscription
        ? route('applicant.registration.step', ['step' => 1])
        : route('applicant.payments.index');
@endphp

@section('content')
<div class="lei-portal-page-head">
    <div>
        <h1>My Entities</h1>
        <p>Manage and monitor your global legal entity identifiers. Ensure your compliance status is up to date to avoid cross-border transaction delays.</p>
    </div>
    <a href="{{ $registerUrl }}" class="lei-btn-primary"><i class="fa-solid fa-plus"></i> Register New LEI</a>
</div>

<div class="lei-portal-stats">
    <div class="lei-portal-stat"><strong>{{ $stats['total'] }}</strong><span>Total Entities</span></div>
    <div class="lei-portal-stat"><strong>{{ $stats['active'] }}</strong><span>Active LEIs</span></div>
    <div class="lei-portal-stat"><strong>{{ $stats['lapsed'] }}</strong><span>Lapsed Action Required</span></div>
    <div class="lei-portal-stat"><strong>{{ $stats['pending_renewal'] }}</strong><span>Pending Renewal</span></div>
</div>

<div class="lei-portal-card lei-portal-card--flush">
    <div class="lei-portal-toolbar lei-portal-toolbar--card">
        <input type="search" placeholder="Search by Legal Entity Name or LEI..." aria-label="Search entities">
        <select aria-label="Filter by status"><option>All Statuses</option></select>
        <select aria-label="Sort entities"><option>Sort by: Expiry Date</option></select>
    </div>

    <div class="lei-portal-table-wrap">
        <table class="lei-portal-table lei-portal-table--responsive">
        <thead>
            <tr>
                <th>Legal Entity Name</th>
                <th>LEI Number</th>
                <th>Status</th>
                <th>Expiry Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($entities as $entity)
                <tr>
                    <td data-label="Legal Entity Name">
                        <strong>{{ $entity->entity_name }}</strong>
                        <div class="muted">{{ $entity->country }}</div>
                    </td>
                    <td data-label="LEI Number" class="lei-portal-mono">{{ $entity->lei_number ?: '—' }}</td>
                    <td data-label="Status">
                        <span class="lei-portal-badge {{ $entity->status === 'approved' ? 'green' : ($entity->status === 'clarification' ? 'red' : 'orange') }}">
                            {{ $entity->status_label }}
                        </span>
                    </td>
                    <td data-label="Expiry Date">{{ $entity->expiry_date?->format('M j, Y') ?? '—' }}</td>
                    <td data-label="Actions" class="lei-portal-table-actions">
                        <a href="{{ route('applicant.applications.show', $entity) }}" class="lei-btn-link">View Details</a>
                        @if ($entity->status === 'approved' && $entity->lei_number && in_array($entity->id, $renewalEligibleIds, true))
                            <a href="{{ route('applicant.payments.index') }}#renewal" class="lei-btn-secondary lei-portal-btn-xs">Renew Now</a>
                        @elseif ($entity->expiry_date && $entity->expiry_date->isFuture())
                            <span class="muted" style="font-size:12px;">Renews {{ $window > 0 ? 'within ' . $window . ' days of expiry' : 'after expiry' }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <p style="margin:0;">No entities yet. <a href="{{ $registerUrl }}">Start your first LEI registration</a>.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
        </table>
    </div>
</div>
@endsection

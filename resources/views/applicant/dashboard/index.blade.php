@extends('applicant.layouts.app')

@section('title', 'My Entities')

@php
    $registrationSubscription = app(\App\Services\ApplicantApplicationService::class)->registrationSubscription(auth()->user());
    $registerUrl = $hasSubmittedRegistration ?? false
        ? ($submittedRegistration ? route('applicant.applications.show', $submittedRegistration) : route('applicant.applications.index'))
        : ($registrationSubscription
            ? route('applicant.registration.step', ['step' => 1])
            : route('applicant.payments.index'));
@endphp

@section('content')
<div class="lei-portal-page-head">
    <div>
        <h1>My Entities</h1>
        <p>Manage and monitor your global legal entity identifiers. Ensure your compliance status is up to date to avoid cross-border transaction delays.</p>
    </div>
    @if (! ($hasSubmittedRegistration ?? false))
        <a href="{{ $registerUrl }}" class="lei-btn-primary"><i class="fa-solid fa-plus"></i> Register New LEI</a>
    @else
        <a href="{{ $registerUrl }}" class="lei-btn-secondary"><i class="fa-solid fa-file-lines"></i> View Registration</a>
    @endif
</div>

<div class="lei-portal-quick-stats">
    <div class="lei-portal-quick-stat">
        <div class="lei-portal-quick-stat-icon lei-portal-quick-stat-icon--blue"><i class="fa-solid fa-building"></i></div>
        <div><strong>{{ $stats['total'] }}</strong><span>Total Entities</span></div>
    </div>
    <div class="lei-portal-quick-stat">
        <div class="lei-portal-quick-stat-icon lei-portal-quick-stat-icon--green"><i class="fa-solid fa-circle-check"></i></div>
        <div><strong>{{ $stats['active'] }}</strong><span>Active LEIs</span></div>
    </div>
    <div class="lei-portal-quick-stat">
        <div class="lei-portal-quick-stat-icon lei-portal-quick-stat-icon--orange"><i class="fa-solid fa-rotate"></i></div>
        <div><strong>{{ $stats['pending_renewal'] }}</strong><span>Pending Renewal</span></div>
    </div>
    <div class="lei-portal-quick-stat">
        <div class="lei-portal-quick-stat-icon lei-portal-quick-stat-icon--red"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div><strong>{{ $stats['lapsed'] }}</strong><span>Lapsed / Action Required</span></div>
    </div>
</div>

@if ($activeLeis->isNotEmpty() || $accountLei)
    <div class="lei-portal-lei-cards">
        @if ($accountLei)
            <div class="lei-portal-lei-banner lei-portal-lei-banner--pending">
                <div>
                    <span class="lei-portal-eyebrow">Your LEI — {{ $accountLei['entity_name'] }}</span>
                    <strong class="lei-portal-lei-code" id="lei-code-account">{{ $accountLei['lei_number'] }}</strong>
                    <span class="lei-portal-lei-expiry">Assigned at registration — complete your LEI application to activate</span>
                </div>
                <div class="lei-portal-lei-banner-actions">
                    <button type="button" class="lei-btn-secondary lei-portal-btn-xs lei-copy-lei" data-target="lei-code-account">
                        <i class="fa-regular fa-copy" aria-hidden="true"></i> Copy
                    </button>
                    <span class="lei-portal-badge orange">Registered</span>
                </div>
            </div>
        @endif
        @foreach ($activeLeis as $activeLei)
            <div class="lei-portal-lei-banner {{ $activeLei->status === 'approved' ? '' : 'lei-portal-lei-banner--pending' }}">
                <div>
                    <span class="lei-portal-eyebrow">Your LEI — {{ $activeLei->entity_name }}</span>
                    <strong class="lei-portal-lei-code" id="lei-code-{{ $activeLei->id }}">{{ $activeLei->lei_number }}</strong>
                    @if ($activeLei->status === 'approved' && $activeLei->expiry_date)
                        <span class="lei-portal-lei-expiry">Valid until {{ $activeLei->expiry_date->format('M j, Y') }}</span>
                    @elseif ($activeLei->status !== 'approved')
                        <span class="lei-portal-lei-expiry">Assigned — awaiting admin approval to activate</span>
                    @endif
                </div>
                <div class="lei-portal-lei-banner-actions">
                    <button type="button" class="lei-btn-secondary lei-portal-btn-xs lei-copy-lei" data-target="lei-code-{{ $activeLei->id }}">
                        <i class="fa-regular fa-copy" aria-hidden="true"></i> Copy
                    </button>
                    <span class="lei-portal-badge {{ $activeLei->status === 'approved' ? 'green' : 'orange' }}">
                        {{ $activeLei->status === 'approved' ? 'Active' : $activeLei->status_label }}
                    </span>
                    <a href="{{ route('applicant.applications.show', $activeLei) }}" class="lei-btn-link">View Details</a>
                </div>
            </div>
        @endforeach
    </div>
@endif

@if ($profileCompletion < 100)
    <div class="lei-portal-profile-completion">
        <div class="lei-portal-profile-completion-head">
            <strong><i class="fa-solid fa-user-circle" style="margin-right:6px;color:#3b82f6;"></i> Complete Your Profile</strong>
            <span>{{ $profileCompletion }}%</span>
        </div>
        <div class="lei-portal-completion-bar" role="progressbar" aria-valuenow="{{ $profileCompletion }}" aria-valuemin="0" aria-valuemax="100">
            <div class="lei-portal-completion-fill" style="width:{{ $profileCompletion }}%;"></div>
        </div>
        <p style="font-size:12px;color:#64748b;margin:6px 0 0;">
            A complete profile speeds up LEI verification.
            <a href="{{ route('applicant.profile') }}" style="color:#3b82f6;">Update profile →</a>
        </p>
    </div>
@endif

<div class="lei-portal-card lei-portal-card--flush">
    <div class="lei-portal-toolbar lei-portal-toolbar--card">
        <input type="search" id="lei-entity-search" placeholder="Search by Legal Entity Name or LEI..." aria-label="Search entities">
        <select id="lei-status-filter" aria-label="Filter by status">
            <option value="">All Statuses</option>
            <option value="approved">Active</option>
            <option value="new">Submitted</option>
            <option value="in_review">In Review</option>
            <option value="clarification">Clarification Required</option>
        </select>
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
                <tr data-entity-name="{{ strtolower($entity->entity_name) }}" data-lei="{{ strtolower($entity->lei_number ?? '') }}" data-status="{{ $entity->status }}">
                    <td data-label="Legal Entity Name">
                        <strong>{{ $entity->entity_name }}</strong>
                        <div class="muted">{{ $entity->country }}</div>
                    </td>
                    <td data-label="LEI Number" class="lei-portal-mono">
                        @if ($entity->lei_number)
                            {{ $entity->lei_number }}
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>
                    <td data-label="Status">
                        <span class="lei-portal-badge {{ $entity->status === 'approved' ? 'green' : ($entity->status === 'clarification' ? 'red' : 'orange') }}">
                            {{ $entity->status_label }}
                        </span>
                    </td>
                    <td data-label="Expiry Date">
                        @if ($entity->expiry_date)
                            {{ $entity->expiry_date->format('M j, Y') }}
                            @php $daysLeft = now()->diffInDays($entity->expiry_date, false); @endphp
                            @if ($daysLeft >= 0 && $daysLeft <= 90)
                                <br>
                                <span class="lei-portal-renewal-badge {{ $daysLeft <= 30 ? 'urgent' : '' }}">
                                    <i class="fa-regular fa-clock"></i>
                                    {{ $daysLeft === 0 ? 'Expires today' : $daysLeft . ' days left' }}
                                </span>
                            @elseif ($daysLeft < 0)
                                <br><span class="lei-portal-badge red" style="font-size:10px;">Expired</span>
                            @endif
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>
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
            <tr id="lei-no-results-row" hidden>
                <td colspan="5" style="text-align:center;color:#64748b;padding:24px 0;">
                    <i class="fa-solid fa-magnifying-glass" style="font-size:18px;margin-bottom:8px;display:block;opacity:.4;"></i>
                    No entities match your search. <a href="#" onclick="document.getElementById('lei-entity-search').value='';document.getElementById('lei-status-filter').value='';document.querySelectorAll('[data-entity-name]').forEach(r=>r.hidden=false);this.closest('tr').hidden=true;return false;">Clear filters</a>
                </td>
            </tr>
        </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
(function () {
    /* —— Copy LEI button —— */
    document.querySelectorAll('.lei-copy-lei').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const target = document.getElementById(this.dataset.target);
            if (!target) return;
            navigator.clipboard.writeText(target.textContent.trim()).then(function () {
                const original = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-check" aria-hidden="true"></i> Copied';
                setTimeout(function () { btn.innerHTML = original; }, 2000);
            });
        });
    });

    /* —— Client-side entity search & status filter —— */
    const searchInput  = document.getElementById('lei-entity-search');
    const statusFilter = document.getElementById('lei-status-filter');
    const rows = Array.from(document.querySelectorAll('[data-entity-name]'));

    function filterRows() {
        const query  = (searchInput ? searchInput.value.toLowerCase().trim() : '');
        const status = (statusFilter ? statusFilter.value : '');
        let visible = 0;
        rows.forEach(function (row) {
            const matchName   = !query  || row.dataset.entityName.includes(query) || row.dataset.lei.includes(query);
            const matchStatus = !status || row.dataset.status === status;
            const show = matchName && matchStatus;
            row.hidden = !show;
            if (show) visible++;
        });
        /* Show "no results" row */
        const emptyRow = document.getElementById('lei-no-results-row');
        if (emptyRow) emptyRow.hidden = visible > 0;
    }

    if (searchInput)  searchInput.addEventListener('input',  filterRows);
    if (statusFilter) statusFilter.addEventListener('change', filterRows);
})();
</script>
@endpush
@endsection

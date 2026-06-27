@php
    $draft = $application->draft_data ?? [];
    $documentFields = [
        'certificate_of_incorporation' => 'Certificate of Incorporation',
        'articles_of_association' => 'Articles of Association',
        'proof_of_authority' => 'Proof of Authority',
        'renewal_certificate' => 'Renewal Certificate',
    ];
@endphp

<div class="lei-app-detail-inner" data-app-id="{{ $application->id }}">
    <div class="lei-app-detail-head">
        <div class="lei-app-detail-head-top">
            <h3>{{ $application->entity_name }}</h3>
            <span class="lei-app-status lei-app-status--{{ $application->status_tone }}">
                <span class="dot"></span>{{ $application->status_label }}
            </span>
        </div>
        <span class="lei-app-detail-id">{{ $application->application_code }}</span>
        @if ($application->submitted_on)
            <span class="lei-app-detail-date">Submitted {{ $application->submitted_on->format('M j, Y') }}</span>
        @endif
    </div>

    <div class="lei-app-detail-section">
        <h4>Overview</h4>
        <div class="lei-app-detail-grid">
            <div class="lei-app-meta-item">
                <span class="label">Country</span>
                <span class="value" data-field="country">{{ $application->country }}</span>
            </div>
            <div class="lei-app-meta-item">
                <span class="label">Type</span>
                <span class="value" data-field="issuance_type">{{ $application->issuance_type }}</span>
            </div>
            @if ($application->lei_number)
                <div class="lei-app-meta-item">
                    <span class="label">LEI</span>
                    <span class="value">{{ $application->lei_number }}</span>
                </div>
            @endif
            @if ($application->user)
                <div class="lei-app-meta-item">
                    <span class="label">Applicant</span>
                    <span class="value">{{ $application->user->name }}</span>
                    <span class="lei-app-meta-sub">{{ $application->user->email }}</span>
                </div>
            @endif
            @if ($application->subscription)
                <div class="lei-app-meta-item lei-app-meta-item--wide">
                    <span class="label">Subscription</span>
                    <span class="value">{{ $application->subscription->plan_name }}</span>
                    <span class="lei-app-meta-sub">
                        {{ $application->subscription->reference }} ·
                        {{ $application->subscription->formattedAmount() }} ·
                        {{ $application->subscription->duration_years }} {{ Str::plural('year', $application->subscription->duration_years) }}
                    </span>
                </div>
            @endif
        </div>
    </div>

    @if (! empty($draft))
        <div class="lei-app-detail-section">
            <h4>Submitted Information</h4>
            <div class="lei-app-detail-grid">
                @foreach ($draft as $key => $value)
                    @continue(is_array($value))
                    @continue(isset($documentFields[$key]))
                    @continue(in_array($key, ['authority_confirmed', 'accuracy_confirmed', 'terms_confirmed', 'modify_entity', 'lei_search'], true))
                    <div class="lei-app-meta-item">
                        <span class="label">{{ ucwords(str_replace('_', ' ', $key)) }}</span>
                        <span class="value">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @php
        $documents = collect($documentFields)->filter(fn ($label, $key) => ! empty($draft[$key]));
    @endphp
    @if ($documents->isNotEmpty())
        <div class="lei-app-detail-section">
            <h4>Documents</h4>
            <ul class="lei-app-docs-list">
                @foreach ($documents as $key => $label)
                    <li>
                        <a href="{{ asset('storage/' . $draft[$key]) }}" target="_blank" rel="noopener">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            {{ $label }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($application->user_id && $application->status !== 'approved' && $application->workflow_type === 'registration')
        <div class="lei-app-detail-section">
            <h4>LEI on Approval</h4>
            <p class="lei-app-detail-hint">Leave blank to auto-generate a LEI number when you approve.</p>
            <input id="lei_number_input" name="lei_number" type="text" maxlength="20" class="lei-app-lei-input" placeholder="549300XXXXXXXXXXXX" value="{{ old('lei_number', $application->lei_number) }}">
        </div>
    @endif

    <div class="lei-app-detail-section">
        <h4>Audit History</h4>
        <div class="lei-app-timeline" data-audit-timeline>
            @forelse ($application->auditEvents as $event)
                <div class="lei-app-timeline-item {{ $event->is_highlight ? 'highlight' : '' }}">
                    <span class="lei-app-timeline-dot"></span>
                    <div class="lei-app-timeline-body">
                        <span class="time">{{ $event->occurred_at->format('M d, H:i') }}</span>
                        <p>{{ $event->description }}</p>
                    </div>
                </div>
            @empty
                <p class="lei-app-detail-hint">No audit events yet.</p>
            @endforelse
        </div>
    </div>

    <div class="lei-app-detail-actions">
        <button type="button" class="lei-app-btn-final" data-action="approve" {{ $application->status === 'approved' ? 'disabled' : '' }}>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Final Approval
        </button>
        <div class="lei-app-btn-row">
            <button type="button" class="lei-app-btn-secondary" data-action="clarify" {{ in_array($application->status, ['approved', 'rejected']) ? 'disabled' : '' }}>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                Clarify
            </button>
            <button type="button" class="lei-app-btn-secondary" data-action="reassign" {{ in_array($application->status, ['approved', 'rejected']) ? 'disabled' : '' }}>
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

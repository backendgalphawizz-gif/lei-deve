@extends('applicant.layouts.app')

@section('title', 'Application Details')

@section('content')
<a href="{{ route('applicant.applications.index') }}" class="lei-portal-back">
    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
    Back to My Applications
</a>

<div class="lei-portal-page-head lei-portal-page-head--detail">
    <div>
        <p class="lei-portal-eyebrow">Application Tracking</p>
        <h1>{{ $application->entity_name }}</h1>
        <p class="lei-portal-ref">Reference: <strong>{{ $application->application_code }}</strong></p>
    </div>
    <span class="lei-portal-badge lei-portal-page-badge {{ $application->status_tone }}">{{ $application->status_label }}</span>
</div>

@if ($application->status === 'rejected')
    <div class="lei-portal-alert lei-portal-alert--error">
        <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>
        <div>
            <strong>Application rejected</strong>
            <span>Review the feedback below and resubmit your application when ready.</span>
        </div>
    </div>
@endif

@if ($application->status === 'clarification')
    <div class="lei-portal-alert lei-portal-alert--warning">
        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
        <div>
            <strong>Clarification required</strong>
            <span>Our review team needs additional information before they can continue processing your application.</span>
        </div>
        <a href="{{ route('applicant.applications.clarify', $application) }}" class="lei-btn-primary lei-portal-btn-sm">Respond Now</a>
    </div>
@endif

@if ($application->status === 'approved' && $application->certificate?->isPendingCa())
    <div class="lei-portal-alert lei-portal-alert--info">
        <i class="fa-solid fa-certificate" aria-hidden="true"></i>
        <div>
            <strong>Certificate in progress</strong>
            <span>Your application is approved. An unsigned ISO 17442-2 certificate has been sent to our Certificate Authority for digital signing. You will receive an email when your signed certificate is ready to download.</span>
        </div>
    </div>
@endif

@if ($application->status === 'approved' && $application->certificate?->isSigned())
    <div class="lei-portal-alert lei-portal-alert--success">
        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
        <div>
            <strong>Certificate ready</strong>
            <span>Your LEI certificate has been digitally signed and is available for download below.</span>
        </div>
    </div>
@endif

@if ($application->lei_number)
    <div class="lei-portal-lei-banner {{ $application->status === 'approved' ? '' : 'lei-portal-lei-banner--pending' }}">
        <div>
            <span class="lei-portal-eyebrow">Your LEI Code</span>
            <strong class="lei-portal-lei-code" id="lei-code-show">{{ $application->lei_number }}</strong>
            @if ($application->status === 'approved' && $application->expiry_date)
                <span class="lei-portal-lei-expiry">Valid until {{ $application->expiry_date->format('M j, Y') }}</span>
            @elseif ($application->status !== 'approved')
                <span class="lei-portal-lei-expiry">Assigned on submission — pending admin approval to activate</span>
            @endif
        </div>
        <div class="lei-portal-lei-banner-actions">
            <button type="button" class="lei-btn-secondary lei-portal-btn-xs lei-copy-lei" data-target="lei-code-show"
                    aria-label="Copy LEI code to clipboard">
                <i class="fa-regular fa-copy" aria-hidden="true"></i> Copy
            </button>
            @if ($application->status === 'approved')
                @php $cert = $application->certificate; @endphp
                @if ($cert?->isSigned())
                    <a href="{{ route('applicant.applications.certificate', $application) }}"
                       class="lei-btn-primary lei-portal-btn-xs"
                       aria-label="Download signed LEI Certificate PDF">
                        <i class="fa-solid fa-file-arrow-down" aria-hidden="true"></i> Download Certificate
                    </a>
                @elseif ($cert?->isPendingCa())
                    <span class="lei-portal-badge orange" title="ISO 17442-2 certificate awaiting CA digital signature">
                        <i class="fa-solid fa-clock" aria-hidden="true"></i> Awaiting CA Signature
                    </span>
                @endif
            @endif
            <span class="lei-portal-badge {{ $application->status === 'approved' ? 'green' : 'orange' }}">
                {{ $application->status === 'approved' ? 'Active' : $application->status_label }}
            </span>
        </div>
    </div>
@endif

<div class="lei-portal-split lei-portal-split--detail">
    <div class="lei-portal-stack">
        <div class="lei-portal-card">
            <h2>Application Progress</h2>
            @include('applicant.partials.application-progress', ['application' => $application])
            @if ($application->status === 'clarification')
                <div class="lei-portal-card-footer">
                    <a href="{{ route('applicant.applications.clarify', $application) }}" class="lei-btn-primary">
                        <i class="fa-solid fa-reply" aria-hidden="true"></i> Submit Clarification
                    </a>
                </div>
            @endif
        </div>

        @if ($events->isNotEmpty())
            <div class="lei-portal-card">
                <h2>Activity Log</h2>
                <ul class="lei-portal-timeline">
                    @foreach ($events as $event)
                        <li class="lei-portal-timeline-item {{ $event->is_highlight ? 'highlight' : '' }}">
                            <span class="lei-portal-timeline-dot" aria-hidden="true"></span>
                            <div>
                                <time>{{ $event->occurred_at->format('M j, Y · g:i A') }}</time>
                                <p>{{ $event->description }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <aside class="lei-portal-summary lei-portal-summary--sticky">
        <h3>Entity Summary</h3>
        <dl class="lei-portal-dl">
            <div class="lei-portal-dl-row">
                <dt>Legal Name</dt>
                <dd>{{ $application->entity_name }}</dd>
            </div>
            <div class="lei-portal-dl-row">
                <dt>Jurisdiction</dt>
                <dd>{{ $application->country }}</dd>
            </div>
            <div class="lei-portal-dl-row">
                <dt>Application Type</dt>
                <dd>{{ ucfirst(str_replace('_', ' ', $application->application_type)) }}</dd>
            </div>
            <div class="lei-portal-dl-row">
                <dt>Workflow</dt>
                <dd>{{ ucfirst($application->workflow_type) }}</dd>
            </div>
            @if ($application->submitted_on)
                <div class="lei-portal-dl-row">
                    <dt>Submitted</dt>
                    <dd>{{ $application->submitted_on->format('M j, Y') }}</dd>
                </div>
            @endif
            @if ($application->lei_number)
                <div class="lei-portal-dl-row">
                    <dt>LEI Number</dt>
                    <dd class="lei-portal-mono">{{ $application->lei_number }}</dd>
                </div>
            @endif
            @if ($application->expiry_date)
                <div class="lei-portal-dl-row">
                    <dt>Expiry Date</dt>
                    <dd>{{ $application->expiry_date->format('M j, Y') }}</dd>
                </div>
            @endif
        </dl>

        @if ($application->subscription)
            <div class="lei-portal-summary-divider"></div>
            <h3>Subscription</h3>
            <dl class="lei-portal-dl">
                <div class="lei-portal-dl-row">
                    <dt>Plan</dt>
                    <dd>{{ $application->subscription->plan_name }}</dd>
                </div>
                <div class="lei-portal-dl-row">
                    <dt>Reference</dt>
                    <dd class="lei-portal-mono">{{ $application->subscription->reference }}</dd>
                </div>
                <div class="lei-portal-dl-row">
                    <dt>Amount Paid</dt>
                    <dd>{{ $application->subscription->formattedAmount() }}</dd>
                </div>
            </dl>
        @endif

        <div class="lei-portal-summary-actions">
            <a href="{{ route('applicant.applications.index') }}" class="lei-btn-secondary full">All Applications</a>
            @if ($application->status === 'approved' && $application->lei_number)
                <a href="{{ route('applicant.applications.certificate', $application) }}"
                   class="lei-btn-primary full"
                   aria-label="Download LEI Certificate PDF">
                    <i class="fa-solid fa-file-arrow-down" aria-hidden="true"></i> Download Certificate
                </a>
            @endif
            @if ($application->status === 'approved' && $application->lei_number && app(\App\Services\SubscriptionService::class)->isEntityEligibleForRenewal($application))
                <a href="{{ route('applicant.payments.index') }}#renewal" class="lei-btn-secondary full">Renew LEI</a>
            @endif
        </div>
    </aside>
</div>
@push('scripts')
<script>
(function () {
    document.querySelectorAll('.lei-copy-lei').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = document.getElementById(btn.dataset.target);
            if (!target) return;
            navigator.clipboard.writeText(target.textContent.trim()).then(function () {
                var original = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-check" aria-hidden="true"></i> Copied';
                setTimeout(function () { btn.innerHTML = original; }, 2000);
            });
        });
    });
})();
</script>
@endpush
@endsection

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

@if ($application->status === 'approved' && $application->lei_number)
    <div class="lei-portal-lei-banner">
        <div>
            <span class="lei-portal-eyebrow">Your LEI</span>
            <strong class="lei-portal-lei-code">{{ $application->lei_number }}</strong>
            @if ($application->expiry_date)
                <span class="lei-portal-lei-expiry">Valid until {{ $application->expiry_date->format('M j, Y') }}</span>
            @endif
        </div>
        <span class="lei-portal-badge green">Active</span>
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
            @if ($application->status === 'approved' && $application->lei_number && app(\App\Services\SubscriptionService::class)->isEntityEligibleForRenewal($application))
                <a href="{{ route('applicant.payments.index') }}#renewal" class="lei-btn-primary full">Renew LEI</a>
            @endif
        </div>
    </aside>
</div>
@endsection

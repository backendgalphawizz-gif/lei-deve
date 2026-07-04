@extends('applicant.layouts.app')

@section('title', 'Plans & Payments')

@section('content')
<div class="lei-portal-page-head">
    <div>
        <h1>Plans & Payments</h1>
        <p>View your subscriptions, purchase admin-managed plans, and renew LEIs when eligible.</p>
    </div>
    <a href="{{ route('pricing') }}" target="_blank" rel="noopener" class="lei-btn-secondary">View Public Pricing</a>
</div>

<div class="lei-portal-stats">
    <div class="lei-portal-stat"><strong>{{ $subscriptions->count() }}</strong><span>Total Subscriptions</span></div>
    <div class="lei-portal-stat"><strong>{{ $subscriptions->where('status', 'active')->count() }}</strong><span>Active</span></div>
    <div class="lei-portal-stat"><strong>{{ $pending->count() }}</strong><span>Pending Payment</span></div>
    <div class="lei-portal-stat"><strong>{{ $eligibleRenewals->count() }}</strong><span>LEIs Ready to Renew</span></div>
</div>

@if ($unusedRegistration || $unusedRenewal)
    <div class="lei-portal-stack" style="margin-bottom:24px;">
        @if ($unusedRegistration)
            <div class="lei-portal-alert lei-portal-alert--warning">
                <i class="fa-solid fa-file-circle-plus" aria-hidden="true"></i>
                <div>
                    <strong>Registration plan ready</strong>
                    <span>You have paid for {{ $unusedRegistration->plan_name }} but have not finished the application.</span>
                </div>
                <a href="{{ route('applicant.registration.step', ['step' => 1]) }}" class="lei-btn-primary lei-portal-btn-sm">Continue</a>
            </div>
        @endif
        @if ($unusedRenewal)
            <div class="lei-portal-alert lei-portal-alert--warning">
                <i class="fa-solid fa-rotate" aria-hidden="true"></i>
                <div>
                    <strong>Renewal plan ready</strong>
                    <span>You have paid for {{ $unusedRenewal->plan_name }} but have not finished the renewal.</span>
                </div>
                <a href="{{ route('applicant.renewal.step', ['step' => 1]) }}" class="lei-btn-primary lei-portal-btn-sm">Continue</a>
            </div>
        @endif
    </div>
@endif

<div class="lei-portal-card" style="margin-bottom:24px;">
    <div class="lei-portal-plan-step-head">
        <span class="lei-portal-plan-step-num">1</span>
        <div>
            <h2 style="margin:0;">Select a plan</h2>
            <p class="muted" style="margin:6px 0 0;">Save money and avoid annual renewal hassle with multiyear plans.</p>
        </div>
    </div>
    @include('applicant.partials.plan-cards', [
        'plans' => $registrationPlans,
        'blocks' => $registrationBlocks,
        'section' => 'registration',
        'unusedSubscription' => $unusedRegistration,
    ])
</div>

<div class="lei-portal-card" id="renewal" style="margin-bottom:24px;">
    <h2>Renewal Plans</h2>
    <p class="muted" style="margin:0 0 16px;">Available when your LEI has expired or is within the renewal window set by admin.</p>
    @if ($eligibleRenewals->isNotEmpty())
        <div class="lei-portal-renewal-entities" style="margin-bottom:16px;">
            @foreach ($eligibleRenewals as $entity)
                <div class="lei-portal-renewal-entity">
                    <strong>{{ $entity->entity_name }}</strong>
                    <span class="lei-portal-mono">{{ $entity->lei_number }}</span>
                    <span class="lei-portal-badge {{ $entity->expiry_date->isPast() ? 'red' : 'orange' }}">
                        {{ $entity->expiry_date->isPast() ? 'Expired' : 'Expires ' . $entity->expiry_date->format('M j, Y') }}
                    </span>
                </div>
            @endforeach
        </div>
    @endif
    @include('applicant.partials.plan-cards', [
        'plans' => $renewalPlans,
        'blocks' => $renewalBlocks,
        'section' => 'renewal',
        'eligibleEntities' => $eligibleRenewals,
        'unusedSubscription' => $unusedRenewal,
    ])
</div>

<div class="lei-portal-card lei-portal-card--flush">
    <div class="lei-portal-card-body lei-portal-card-body--compact">
        <h2>Payment History</h2>
    </div>
    <div class="lei-portal-table-wrap">
        <table class="lei-portal-table lei-portal-table--responsive">
        <thead>
            <tr>
                <th>Reference</th>
                <th>Plan</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Payment</th>
                <th>Valid Until</th>
                <th>Invoice</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($subscriptions as $subscription)
                <tr>
                    <td data-label="Reference" class="lei-portal-mono">{{ $subscription->reference }}</td>
                    <td data-label="Plan">{{ $subscription->plan_name }}</td>
                    <td data-label="Type">{{ ucfirst($subscription->plan_section) }}</td>
                    <td data-label="Amount">{{ $subscription->formattedAmount() }}</td>
                    <td data-label="Status">
                        <span class="lei-portal-badge {{ $subscription->status === 'active' ? 'green' : ($subscription->status === 'expired' ? 'red' : 'gray') }}">
                            {{ $subscription->statusLabel() }}
                        </span>
                    </td>
                    <td data-label="Payment">
                        <span class="lei-portal-badge {{ $subscription->payment_status === 'paid' ? 'green' : 'orange' }}">
                            {{ $subscription->paymentStatusLabel() }}
                        </span>
                    </td>
                    <td data-label="Valid Until">{{ $subscription->expires_at?->format('M j, Y') ?? '—' }}</td>
                    <td data-label="Invoice">
                        @if ($subscription->payment_status === 'paid')
                            <a href="{{ route('applicant.payments.invoice', $subscription) }}"
                               class="lei-btn-link" style="font-size:12px;"
                               aria-label="Download GST invoice for {{ $subscription->reference }}">
                                <i class="fa-solid fa-file-invoice" aria-hidden="true"></i> GST Invoice
                            </a>
                        @else
                            <span class="muted" style="font-size:12px;">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">No payment history yet.</td></tr>
            @endforelse
        </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    document.querySelectorAll('.lei-portal-renewal-lei-select').forEach(function (select) {
        var base = select.dataset.planUrl;
        var btn = select.closest('.lei-portal-plan-card')?.querySelector('.lei-btn-primary');
        if (!btn || !base) return;
        select.addEventListener('change', function () {
            btn.href = base + '?lei=' + encodeURIComponent(select.value);
        });
    });
})();
</script>
@endpush

@php
    use App\Support\CurrencyFormatter;
    $years = (int) ($subscription->duration_years ?: 1);
@endphp
<aside class="lei-portal-summary">
    <h3>{{ $title ?? 'Order Summary' }}</h3>
    <div class="lei-portal-summary-row">
        <span>{{ $subscription->plan_name }}</span>
        <span>{{ CurrencyFormatter::format((float) $subscription->amount, 0) }}</span>
    </div>
    <div class="lei-portal-summary-row">
        <span>Duration</span>
        <span>{{ $years }} {{ Str::plural('year', $years) }}</span>
    </div>
    <div class="lei-portal-summary-row">
        <span>Reference</span>
        <span class="lei-portal-summary-ref">{{ $subscription->reference }}</span>
    </div>
    <div class="lei-portal-summary-total">
        <span>Total Paid</span>
        <span>{{ CurrencyFormatter::format((float) $subscription->amount, 0) }}</span>
    </div>

    @if ($showSubmitNote ?? false)
        <p class="lei-portal-summary-legal">
            <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
            By proceeding, you agree to our
            <a href="{{ route('pages.show', 'terms-of-service') }}" target="_blank" rel="noopener">Terms of Service</a>
            and
            <a href="{{ route('pages.show', 'privacy-policy') }}" target="_blank" rel="noopener">Privacy Policy</a>.
            Your application will be reviewed by our team after submission.
        </p>
    @else
        <p class="lei-portal-summary-legal muted">Payment completed when you subscribed. Submit to send your application for admin review.</p>
    @endif
</aside>

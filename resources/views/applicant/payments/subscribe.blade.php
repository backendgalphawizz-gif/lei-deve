@extends('applicant.layouts.app')

@section('title', 'Confirm Plan')

@section('content')
<a href="{{ route('applicant.payments.index') }}" class="lei-portal-back">
    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
    Back to Plans & Payments
</a>

<div class="lei-portal-page-head">
    <div>
        <p class="lei-portal-eyebrow">Checkout</p>
        <h1>Confirm Your Plan</h1>
        <p>Review your selection before completing your subscription.</p>
    </div>
</div>

<div class="lei-portal-split">
    <div class="lei-portal-card lei-portal-plan-card featured" style="max-width:520px;">
        @if ($plan->label)<span class="lei-portal-plan-label">{{ $plan->label }}</span>@endif
        <h2 style="margin:0 0 8px;font-size:22px;">{{ $plan->name }}</h2>
        <div class="lei-portal-plan-price">{{ $plan->formattedPrice() }}<span>{{ $plan->price_suffix }}</span></div>
        @if ($plan->savings_label)<div class="lei-portal-plan-save">{{ $plan->savings_label }}</div>@endif

        <dl class="lei-portal-dl" style="margin:16px 0;">
            <div class="lei-portal-dl-row">
                <dt>Duration</dt>
                <dd>{{ $plan->durationLabel() }}</dd>
            </div>
            <div class="lei-portal-dl-row">
                <dt>Type</dt>
                <dd>{{ ucfirst($plan->section) }}</dd>
            </div>
            <div class="lei-portal-dl-row">
                <dt>Billing</dt>
                <dd>One-time payment</dd>
            </div>
            @if ($lei ?? null)
                <div class="lei-portal-dl-row">
                    <dt>LEI to renew</dt>
                    <dd class="lei-portal-mono">{{ $lei }}</dd>
                </div>
            @endif
        </dl>

        <ul class="lei-portal-plan-features">
            @foreach ($plan->features ?? [] as $feature)
                @if ($feature['included'] ?? true)
                    <li>{{ $feature['text'] }}</li>
                @endif
            @endforeach
        </ul>
    </div>

    <aside class="lei-portal-summary lei-portal-summary--sticky">
        <h3>Order Summary</h3>

        @php
            $basePrice = $plan->price ?? 0;
            $gstRate   = 18;
            $gstAmount = round($basePrice * $gstRate / 100, 2);
            $total     = $basePrice + $gstAmount;
            $currency  = $plan->currency ?? '₹';
        @endphp

        {{-- Promo code --}}
        <div class="lei-portal-promo-row" id="lei-promo-wrap">
            <input type="text" id="lei-promo-input" placeholder="Promo / Referral code" aria-label="Promo code"
                   style="flex:1;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;font-size:13px;">
            <button type="button" id="lei-promo-apply" class="lei-btn-secondary"
                    style="padding:8px 14px;font-size:13px;flex-shrink:0;">Apply</button>
        </div>
        <p id="lei-promo-msg" style="font-size:12px;color:#16a34a;margin:0 0 10px;display:none;"></p>

        <div class="lei-portal-summary-row">
            <span>{{ $plan->name }}</span>
            <span>{{ $currency }}{{ number_format($basePrice, 2) }}</span>
        </div>
        <div class="lei-portal-summary-row">
            <span>GST ({{ $gstRate }}%)</span>
            <span>{{ $currency }}{{ number_format($gstAmount, 2) }}</span>
        </div>
        <div id="lei-discount-row" class="lei-portal-summary-row" style="display:none;color:#16a34a;">
            <span>Promo Discount</span>
            <span id="lei-discount-amount">–{{ $currency }}0.00</span>
        </div>
        <div class="lei-portal-summary-total" id="lei-total-row">
            <span>Total Payable</span>
            <span id="lei-total-amount">{{ $currency }}{{ number_format($total, 2) }}</span>
        </div>
        <p class="muted" style="font-size:11px;margin:6px 0 0;">Inclusive of GST @ {{ $gstRate }}%. A valid GST tax invoice will be emailed to you.</p>

        <p class="muted" style="font-size:12px;margin:12px 0 16px;background:#fffbeb;border:1px solid #fde68a;padding:8px 10px;border-radius:8px;">
            <i class="fa-solid fa-info-circle" style="color:#d97706;"></i>
            Demo portal — your plan activates immediately for testing.
        </p>

        <form method="POST" action="{{ route('applicant.plans.subscribe.submit', $plan) }}{{ ($lei ?? null) ? '?lei=' . urlencode($lei) : '' }}">
            @csrf
            @if ($lei ?? null)
                <input type="hidden" name="lei" value="{{ $lei }}">
            @endif
            <input type="hidden" name="promo_code" id="lei-promo-code-val">
            <button type="submit" class="lei-btn-primary full">
                <i class="fa-solid fa-lock" aria-hidden="true"></i> Confirm & Pay {{ $currency }}{{ number_format($total, 2) }}
            </button>
        </form>
        <a href="{{ route('applicant.payments.index') }}" class="lei-btn-secondary full" style="margin-top:10px;">Cancel</a>

        <div class="lei-pub-secure-bar" style="margin-top:16px;border-top:1px solid #e2e8f0;padding-top:12px;">
            <span><i class="fa-solid fa-lock"></i> SSL Secured</span>
            <span><i class="fa-solid fa-shield-halved"></i> 256-bit Encryption</span>
            <span><i class="fa-solid fa-file-invoice"></i> GST Invoice</span>
        </div>
    </aside>
</div>
@push('scripts')
<script>
(function () {
    /* Demo promo codes */
    var PROMOS = { 'LEI10': 10, 'GLEIF20': 20, 'INDIA15': 15 };
    var base  = parseFloat('{{ $basePrice }}') || 0;
    var gst   = parseFloat('{{ $gstRate }}') || 18;
    var currency = '{{ $currency }}';

    var applyBtn   = document.getElementById('lei-promo-apply');
    var promoInput = document.getElementById('lei-promo-input');
    var promoMsg   = document.getElementById('lei-promo-msg');
    var discountRow = document.getElementById('lei-discount-row');
    var discountAmt = document.getElementById('lei-discount-amount');
    var totalEl    = document.getElementById('lei-total-amount');
    var promoCodeVal = document.getElementById('lei-promo-code-val');

    function fmt(n) { return currency + n.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'); }

    if (applyBtn) {
        applyBtn.addEventListener('click', function () {
            var code = (promoInput.value || '').trim().toUpperCase();
            var pct  = PROMOS[code];
            if (!pct) {
                promoMsg.style.color = '#dc2626';
                promoMsg.textContent = 'Invalid promo code.';
                promoMsg.style.display = 'block';
                discountRow.style.display = 'none';
                recalc(0);
                return;
            }
            var discount = base * pct / 100;
            var gstAmt   = (base - discount) * gst / 100;
            var total    = base - discount + gstAmt;
            discountAmt.textContent = '–' + fmt(discount);
            totalEl.textContent = fmt(total);
            discountRow.style.display = 'flex';
            promoMsg.style.color = '#16a34a';
            promoMsg.textContent = pct + '% discount applied!';
            promoMsg.style.display = 'block';
            promoCodeVal.value = code;
        });
    }

    function recalc(discount) {
        var gstAmt = (base - discount) * gst / 100;
        var total  = base - discount + gstAmt;
        if (totalEl) totalEl.textContent = fmt(total);
    }
})();
</script>
@endpush
@endsection

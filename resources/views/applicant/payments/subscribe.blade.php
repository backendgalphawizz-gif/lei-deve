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
        <div class="lei-portal-summary-row">
            <span>{{ $plan->name }}</span>
            <span>{{ $plan->formattedPrice() }}</span>
        </div>
        <div class="lei-portal-summary-total">
            <span>Total</span>
            <span>{{ $plan->formattedPrice() }}</span>
        </div>
        <p class="muted" style="font-size:13px;margin:12px 0 16px;">Mock payment — your plan activates immediately for portal use.</p>

        <form method="POST" action="{{ route('applicant.plans.subscribe.submit', $plan) }}{{ ($lei ?? null) ? '?lei=' . urlencode($lei) : '' }}">
            @csrf
            @if ($lei ?? null)
                <input type="hidden" name="lei" value="{{ $lei }}">
            @endif
            <button type="submit" class="lei-btn-primary full">
                <i class="fa-solid fa-lock" aria-hidden="true"></i> Confirm & Subscribe
            </button>
        </form>
        <a href="{{ route('applicant.payments.index') }}" class="lei-btn-secondary full" style="margin-top:10px;">Cancel</a>
    </aside>
</div>
@endsection

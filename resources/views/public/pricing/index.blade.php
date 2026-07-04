@extends('public.layouts.app')

@section('title', 'Bulk Management & Pricing')
@section('body_class', 'page-pricing')

@section('content')
@php $hero = $sections->get('hero')?->content ?? []; @endphp
<section class="lei-pub-section lei-pub-pricing-hero">
    <div class="lei-pub-container lei-pub-center">
        <span class="lei-pub-badge-pill">{{ $sections->get('hero')?->subtitle }}</span>
        <h1>{{ $sections->get('hero')?->title }}</h1>
        <p>{{ $hero['description'] ?? '' }}</p>
    </div>
</section>

<section class="lei-pub-section">
    <div class="lei-pub-container">
        <div class="lei-pub-plan-step-head">
            <span class="lei-pub-plan-step-num">1</span>
            <div>
                <h2 class="lei-pub-divider-title" style="margin:0;border:0;padding:0;">Select a plan</h2>
                <p class="lei-pub-plan-step-sub">Save money and avoid annual renewal hassle with multiyear plans.</p>
            </div>
        </div>
        @if ($activeRegistrationSubscription ?? null)
            <p class="lei-pub-pricing-active-note">
                Your current plan: <strong>{{ $activeRegistrationSubscription->plan_name }}</strong>
                @if ($activeRegistrationSubscription->expires_at)
                    (expires {{ $activeRegistrationSubscription->expires_at->format('M j, Y') }})
                @endif
            </p>
        @endif
        @include('partials.pricing-plan-select-cards', [
            'plans' => $registrationPlans,
            'blocks' => $purchaseBlocks,
            'section' => 'registration',
            'variant' => 'public',
        ])
    </div>
</section>

<section class="lei-pub-section lei-pub-muted" id="renewal">
    <div class="lei-pub-container">
        <h2 class="lei-pub-divider-title">Renewal & Maintenance</h2>
        @auth
            @if (auth()->user()->isApplicant())
                @if ($eligibleRenewalEntities->isNotEmpty())
                    <p class="lei-pub-pricing-active-note">
                        Eligible for renewal:
                        @foreach ($eligibleRenewalEntities as $entity)
                            <strong>{{ $entity->entity_name }}</strong> ({{ $entity->lei_number }})@if (! $loop->last), @endif
                        @endforeach
                    </p>
                @else
                    <p class="lei-pub-pricing-active-note muted">
                        Renewal plans unlock when your LEI expires or enters the renewal window.
                    </p>
                @endif
            @endif
        @endauth
        <div class="lei-pub-cards-3">
            @foreach ($renewalPlans as $plan)
                @php
                    $blocked = $renewalPurchaseBlocks[$plan->id] ?? null;
                    $lei = $renewalLei ?? ($eligibleRenewalEntities->first()?->lei_number ?? null);
                    $renewUrl = ! $blocked && (float) $plan->price > 0
                        ? route('pricing.subscribe', $plan) . ($lei ? '?lei=' . urlencode($lei) : '')
                        : null;
                @endphp
                <article class="lei-pub-renewal-card {{ $blocked ? 'unavailable' : '' }}">
                    <h3>{{ $plan->name }}</h3>
                    @if (($plan->features[0]['text'] ?? null))
                        <p>{{ $plan->features[0]['text'] }}</p>
                    @endif
                    <div class="lei-pub-price-sm">
                        @if ((float) $plan->price > 0)
                            {{ $plan->formattedPrice() }}{{ $plan->price_suffix }}
                        @else
                            Contact Sales
                        @endif
                    </div>
                    @if ($blocked)
                        <span class="lei-pub-btn outline full disabled">Not Available</span>
                        <p class="lei-pub-plan-block-note">{{ $blocked }}</p>
                    @elseif ($renewUrl)
                        <a href="{{ $renewUrl }}" class="lei-pub-btn outline">{{ $plan->button_text }}</a>
                    @else
                        <a href="{{ route('contact') }}" class="lei-pub-btn outline">{{ $plan->button_text }}</a>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="lei-pub-section">
    <div class="lei-pub-container">
        <h2 class="lei-pub-center">High-Precision Service Matrix</h2>
        <p class="lei-pub-center muted">Compare standard individual services against our multi-year compliance bundles.</p>
        <table class="lei-pub-matrix">
            <thead>
                <tr><th>Service Component</th><th>Standard Individual</th><th>Multi-Year Bundle</th></tr>
            </thead>
            <tbody>
                @foreach ($matrixRows as $row)
                    <tr>
                        <td>{{ $row->component }}</td>
                        <td>{{ $row->standard_value }}</td>
                        <td><strong>{{ $row->bundle_value }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<section class="lei-pub-section lei-pub-muted">
    <div class="lei-pub-container">
        <h2 class="lei-pub-center">Frequently Asked Questions</h2>
        <div class="lei-pub-accordion narrow">
            @foreach ($pricingFaqs as $faq)
                <details class="lei-pub-accordion-item">
                    <summary>{{ $faq->question }}</summary>
                    <p>{{ $faq->answer }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>
@endsection

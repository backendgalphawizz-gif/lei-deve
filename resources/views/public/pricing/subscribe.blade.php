@extends('public.layouts.app')

@section('title', 'Confirm Subscription')

@section('content')
<section class="lei-pub-section lei-pub-subscribe-section">
    <div class="lei-pub-container">
        <div class="lei-pub-subscribe-card">
            <div class="lei-pub-subscribe-header">
                <h1>Confirm Your Plan</h1>
                <p>Review your selection before completing your subscription.</p>
            </div>

            <article class="lei-pub-price-card lei-pub-subscribe-plan {{ $plan->is_featured ? 'featured' : '' }}">
                @if ($plan->is_featured)
                    <span class="lei-pub-best-value">BEST VALUE</span>
                @endif
                @if ($plan->label)
                    <small>{{ $plan->label }}</small>
                @endif
                <h2>{{ $plan->name }}</h2>
                <div class="lei-pub-price">{{ $plan->formattedPrice() }} <span>{{ $plan->price_suffix }}</span></div>
                @if ($plan->savings_label)
                    <div class="lei-pub-savings">{{ $plan->savings_label }}</div>
                @endif

                <dl class="lei-pub-subscribe-meta">
                    <div>
                        <dt>Duration</dt>
                        <dd>{{ $plan->durationLabel() }}</dd>
                    </div>
                    <div>
                        <dt>Billing</dt>
                        <dd>One-time</dd>
                    </div>
                </dl>

                <ul>
                    @foreach ($plan->features ?? [] as $feature)
                        @if ($feature['included'] ?? true)
                            <li class="yes">{{ $feature['text'] }}</li>
                        @endif
                    @endforeach
                </ul>
            </article>

            <form method="POST" action="{{ route('pricing.subscribe.submit', $plan) }}">
                @csrf
                <button type="submit" class="lei-pub-btn full">Confirm & Subscribe →</button>
            </form>
            <p class="lei-pub-auth-footer"><a href="{{ route('pricing') }}">← Back to pricing</a></p>
        </div>
    </div>
</section>
@endsection

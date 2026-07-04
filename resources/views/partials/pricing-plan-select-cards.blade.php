@props([
    'plans',
    'blocks' => [],
    'section' => 'registration',
    'variant' => 'portal',
    'eligibleEntities' => collect(),
    'selectedPlanId' => null,
])

@php
    $gridClass = $variant === 'public' ? 'lei-pub-plan-select-grid' : 'lei-portal-plan-select-grid';
    $cardClass = $variant === 'public' ? 'lei-pub-plan-select-card' : 'lei-portal-plan-select-card';
    $defaultSelectedId = $selectedPlanId ?? $plans->firstWhere('is_featured', true)?->id ?? $plans->first()?->id;
@endphp

<div class="{{ $gridClass }}" data-plan-select-grid>
    @foreach ($plans as $plan)
        @php
            $blocked = $blocks[$plan->id] ?? null;
            $lei = $eligibleEntities->first()?->lei_number;
            $subscribeUrl = $variant === 'public'
                ? route('pricing.subscribe', $plan) . ($lei ? '?lei=' . urlencode($lei) : '')
                : route('applicant.plans.subscribe', $plan) . ($lei ? '?lei=' . urlencode($lei) : '');
            $isSelected = (int) $defaultSelectedId === (int) $plan->id;
        @endphp
        <article class="{{ $cardClass }} {{ $plan->is_featured ? 'featured' : '' }} {{ $isSelected ? 'selected' : '' }} {{ $blocked ? 'blocked' : '' }}"
                 data-plan-card
                 data-plan-id="{{ $plan->id }}"
                 @if (! $blocked) tabindex="0" role="button" @endif>
            @if ($plan->label)
                <span class="lei-plan-select-badge">{{ $plan->label }}</span>
            @endif

            <div class="lei-plan-select-head">
                <h3>{{ $plan->yearLabel() }}</h3>
                <span class="lei-plan-select-radio" aria-hidden="true"></span>
            </div>

            <div class="lei-plan-select-rate">
                {{ $plan->formattedYearlyPrice() }} <span>/ year</span>
            </div>

            <p class="lei-plan-select-perk">+ Free LEI certificate</p>

            @if ($plan->savings_label)
                <p class="lei-plan-select-save">{{ $plan->savings_label }}</p>
            @endif

            <div class="lei-plan-select-total">
                <span>Total</span>
                <strong>{{ $plan->formattedTotalPrice() }}</strong>
            </div>

            @if ($section === 'renewal' && $eligibleEntities->count() > 1 && ! $blocked)
                <div class="lei-plan-select-renew-lei">
                    <label>Renew LEI</label>
                    <select class="lei-portal-renewal-lei-select" data-plan-url="{{ $variant === 'public' ? route('pricing.subscribe', $plan) : route('applicant.plans.subscribe', $plan) }}">
                        @foreach ($eligibleEntities as $entity)
                            <option value="{{ $entity->lei_number }}">{{ $entity->entity_name }} ({{ $entity->lei_number }})</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if ($blocked)
                <span class="lei-plan-select-btn disabled">{{ $plan->button_text ?? 'Not Available' }}</span>
                <p class="lei-plan-select-block-note">{{ $blocked }}</p>
            @else
                <a href="{{ $subscribeUrl }}" class="lei-plan-select-btn">{{ $plan->button_text ?? 'Select Plan' }}</a>
            @endif
        </article>
    @endforeach
</div>

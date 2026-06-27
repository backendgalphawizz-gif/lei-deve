@props([
    'plans',
    'blocks' => [],
    'section' => 'registration',
    'eligibleEntities' => collect(),
    'unusedSubscription' => null,
])

@if ($unusedSubscription)
    <div class="lei-portal-alert lei-portal-alert--warning lei-portal-alert--flat">
        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
        <div>
            <strong>Unused plan: {{ $unusedSubscription->plan_name }}</strong>
            <span>
                @if ($section === 'registration')
                    <a href="{{ route('applicant.registration.step', ['step' => 1]) }}" class="lei-btn-link">Continue registration</a>
                @else
                    <a href="{{ route('applicant.renewal.step', ['step' => 1]) }}" class="lei-btn-link">Continue renewal</a>
                @endif
            </span>
        </div>
    </div>
@endif

@if ($section === 'renewal' && $eligibleEntities->isEmpty())
    <p class="lei-portal-plan-empty muted">No LEIs are in the renewal window yet. Renewal plans appear when your LEI expires or enters the configured renewal period.</p>
@elseif ($plans->isEmpty())
    <p class="lei-portal-plan-empty muted">No {{ $section }} plans are available right now. Please check back later.</p>
@else
    <div class="lei-portal-plan-grid">
        @foreach ($plans as $plan)
            @php
                $blocked = $blocks[$plan->id] ?? null;
                $lei = $eligibleEntities->first()?->lei_number;
                $subscribeUrl = route('applicant.plans.subscribe', $plan) . ($lei ? '?lei=' . urlencode($lei) : '');
            @endphp
            <article class="lei-portal-plan-card {{ $plan->is_featured ? 'featured' : '' }} {{ $blocked ? 'blocked' : '' }}">
                @if ($plan->label)<span class="lei-portal-plan-label">{{ $plan->label }}</span>@endif
                <h3>{{ $plan->name }}</h3>
                <div class="lei-portal-plan-price">{{ $plan->formattedPrice() }}<span>{{ $plan->price_suffix }}</span></div>
                @if ($plan->savings_label)<div class="lei-portal-plan-save">{{ $plan->savings_label }}</div>@endif
                <ul class="lei-portal-plan-features">
                    @foreach (array_slice($plan->features ?? [], 0, 4) as $feature)
                        <li>{{ $feature['text'] ?? '' }}</li>
                    @endforeach
                </ul>
                @if ($section === 'renewal' && $eligibleEntities->count() > 1)
                    <div class="lei-portal-field" style="margin-bottom:12px;">
                        <label style="font-size:12px;">Renew LEI</label>
                        <select class="lei-portal-renewal-lei-select" data-plan-url="{{ route('applicant.plans.subscribe', $plan) }}">
                            @foreach ($eligibleEntities as $entity)
                                <option value="{{ $entity->lei_number }}">{{ $entity->entity_name }} ({{ $entity->lei_number }})</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if ($blocked)
                    <span class="lei-btn-secondary full disabled">Not Available</span>
                    <p class="lei-portal-plan-block-note">{{ $blocked }}</p>
                @else
                    <a href="{{ $subscribeUrl }}" class="lei-btn-primary full">{{ $plan->button_text }}</a>
                @endif
            </article>
        @endforeach
    </div>
@endif

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
    @include('partials.pricing-plan-select-cards', [
        'plans' => $plans,
        'blocks' => $blocks,
        'section' => $section,
        'variant' => 'portal',
        'eligibleEntities' => $eligibleEntities,
    ])
@endif

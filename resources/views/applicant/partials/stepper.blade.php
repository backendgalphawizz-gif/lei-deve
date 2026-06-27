@php
    $steps = $steps ?? [
        1 => 'Company Information',
        2 => 'Document Upload',
        3 => 'Declaration Submission',
        4 => 'Review & Payment',
    ];
    $routeName = $routeName ?? 'applicant.registration.step';
@endphp
<nav class="lei-portal-stepper" aria-label="Progress">
    @foreach ($steps as $num => $label)
        <a href="{{ route($routeName, ['step' => $num]) }}"
           class="lei-portal-step {{ $num < $currentStep ? 'done' : '' }} {{ $num === $currentStep ? 'active' : '' }} {{ $num > $currentStep ? 'upcoming' : '' }}">
            <span class="lei-portal-step-num">Step {{ $num }}</span>
            <span class="lei-portal-step-label">{{ $label }}</span>
        </a>
    @endforeach
</nav>

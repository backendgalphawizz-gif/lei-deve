@php
    $activeStep = match ($application->status) {
        'new', 'pending' => 2,
        'under_review', 'clarification' => 3,
        'approved' => 5,
        'rejected' => 4,
        default => 1,
    };

    $steps = [
        1 => [
            'title' => 'Application Submitted',
            'desc' => $application->submitted_on?->format('M j, Y') ?? 'Awaiting submission',
        ],
        2 => [
            'title' => 'Document Verification',
            'desc' => 'Registry documents under review',
        ],
        3 => [
            'title' => 'LOU Review',
            'desc' => $application->status === 'clarification' ? 'Action required from you' : 'Compliance and data validation',
        ],
        4 => [
            'title' => 'Final Approval & Issuance',
            'desc' => $application->status === 'approved' ? 'LEI issued' : 'LEI number assignment',
        ],
    ];
@endphp

<ol class="lei-portal-progress">
    @foreach ($steps as $num => $step)
        @php
            $state = $application->status === 'approved'
                ? 'done'
                : ($application->status === 'rejected' && $num >= 3 ? 'rejected' : ($num < $activeStep ? 'done' : ($num === $activeStep ? 'active' : 'pending')));
        @endphp
        <li class="lei-portal-progress-step {{ $state }}">
            <span class="lei-portal-progress-marker" aria-hidden="true">
                @if ($state === 'done')
                    <i class="fa-solid fa-check"></i>
                @elseif ($state === 'rejected')
                    <i class="fa-solid fa-xmark"></i>
                @else
                    {{ $num }}
                @endif
            </span>
            <div class="lei-portal-progress-body">
                <strong>{{ $step['title'] }}</strong>
                <span>{{ $step['desc'] }}</span>
            </div>
        </li>
    @endforeach
</ol>

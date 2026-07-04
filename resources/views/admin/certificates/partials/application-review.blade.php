@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $application = $certificate->application;
    $draft = $application->draft_data ?? [];
    $subscription = $application->subscription;
    $applicant = $application->user;

    $proofTypes = [
        'poa' => 'Power of Attorney (PoA)',
        'registry_extract' => 'Extract from official registry showing authorized signatories',
        'letter_of_authorization' => 'Letter of Authorization signed by a director',
    ];

    $documentFields = [
        'certificate_of_incorporation' => 'Certificate of Incorporation',
        'articles_of_association' => 'Articles of Association',
        'proof_of_authority' => $proofTypes[$draft['proof_of_authority_type'] ?? ''] ?? 'Proof of Authority',
    ];

    $entityFields = [
        'entity_name' => 'Legal Entity Name',
        'registration_authority' => 'Registration Authority',
        'registration_number' => 'Registration / CIN Number',
        'registered_address' => 'Registered Address',
        'country' => 'Country of Incorporation',
        'entity_type' => 'Entity Type',
    ];

    $declarationFields = [
        'signature_name' => 'Digital Signature (Full Name)',
    ];

    $formatFileSize = function (?string $path): string {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return '—';
        }
        $bytes = Storage::disk('public')->size($path);
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }

        return number_format($bytes / 1024, 1).' KB';
    };

    $paymentVerified = $subscription && $subscription->payment_status === 'paid';
    $plan = $subscription?->pricingPlan;
@endphp

<div class="lei-ca-review">
    <div class="lei-ca-review-head">
        <h3>Application & Payment Review</h3>
        <p>Verify the applicant’s submitted registration data and payment before signing the certificate.</p>
    </div>

    <div class="lei-ca-card lei-ca-review-card">
        <h4>Application Overview</h4>
        <dl class="lei-ca-dl">
            <div><dt>Application Ref</dt><dd class="mono">{{ $application->application_code }}</dd></div>
            <div>
                <dt>Status</dt>
                <dd>
                    <span class="lei-app-status lei-app-status--{{ $application->status_tone }}">
                        <span class="dot"></span>{{ $application->status_label }}
                    </span>
                </dd>
            </div>
            <div><dt>Workflow</dt><dd>{{ ucfirst($application->workflow_type ?? 'registration') }}</dd></div>
            <div><dt>Submitted</dt><dd>{{ $application->submitted_on?->format('M j, Y') ?? '—' }}</dd></div>
            <div><dt>LEI Code</dt><dd class="mono">{{ $application->lei_number ?? '—' }}</dd></div>
            <div><dt>Valid Until</dt><dd>{{ $application->expiry_date?->format('M j, Y') ?? '—' }}</dd></div>
        </dl>
    </div>

    @if ($applicant)
        <div class="lei-ca-card lei-ca-review-card">
            <h4>Applicant Contact</h4>
            <dl class="lei-ca-dl">
                <div><dt>Full Name</dt><dd>{{ $applicant->name }}</dd></div>
                <div><dt>Email</dt><dd>{{ $applicant->email }}</dd></div>
                <div><dt>Phone</dt><dd>{{ $applicant->phone ?: '—' }}</dd></div>
                <div><dt>Organization</dt><dd>{{ $applicant->organization_name ?: $application->entity_name }}</dd></div>
                <div><dt>Account LEI</dt><dd class="mono">{{ $applicant->lei_number ?? '—' }}</dd></div>
            </dl>
        </div>
    @endif

    <div class="lei-ca-card lei-ca-review-card">
        <h4>Step 1 — Entity Details</h4>
        <dl class="lei-ca-dl">
            @foreach ($entityFields as $key => $label)
                @php
                    $value = $key === 'entity_name'
                        ? ($application->entity_name ?: ($draft[$key] ?? null))
                        : ($draft[$key] ?? ($key === 'country' ? $application->country : null));
                @endphp
                <div @if (in_array($key, ['registered_address'], true)) style="grid-column:1/-1;" @endif>
                    <dt>{{ $label }}</dt>
                    <dd>{{ $value ?: '—' }}</dd>
                </div>
            @endforeach
        </dl>
    </div>

    <div class="lei-ca-card lei-ca-review-card">
        <h4>Step 2 — Uploaded Documents</h4>
        @php $hasDocuments = collect($documentFields)->filter(fn ($label, $key) => ! empty($draft[$key]))->isNotEmpty(); @endphp
        @if ($hasDocuments)
            <ul class="lei-ca-doc-list">
                @foreach ($documentFields as $key => $label)
                    @continue(empty($draft[$key]))
                    @php $path = $draft[$key]; @endphp
                    <li class="lei-ca-doc-item">
                        <div class="lei-ca-doc-icon"><i class="fa-regular fa-file-pdf" aria-hidden="true"></i></div>
                        <div class="lei-ca-doc-meta">
                            <strong>{{ $label }}</strong>
                            <span>{{ basename($path) }} · {{ $formatFileSize($path) }}</span>
                            @if ($key === 'proof_of_authority' && ! empty($draft['proof_of_authority_type']))
                                <span class="lei-ca-doc-sub">{{ $proofTypes[$draft['proof_of_authority_type']] ?? $draft['proof_of_authority_type'] }}</span>
                            @endif
                        </div>
                        <a href="{{ asset('storage/'.$path) }}" target="_blank" rel="noopener" class="lei-ca-doc-link">
                            <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> View
                        </a>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="lei-ca-hint">No documents were uploaded with this application.</p>
        @endif
    </div>

    <div class="lei-ca-card lei-ca-review-card">
        <h4>Step 3 — Declaration & Authorization</h4>
        <dl class="lei-ca-dl">
            @foreach ($declarationFields as $key => $label)
                <div><dt>{{ $label }}</dt><dd>{{ $draft[$key] ?? '—' }}</dd></div>
            @endforeach
            <div><dt>Authority confirmed</dt><dd>{{ ! empty($draft['authority_confirmed']) ? 'Yes' : 'No' }}</dd></div>
            <div><dt>Accuracy confirmed</dt><dd>{{ ! empty($draft['accuracy_confirmed']) ? 'Yes' : 'No' }}</dd></div>
            <div><dt>Terms accepted</dt><dd>{{ ! empty($draft['terms_confirmed']) ? 'Yes' : 'No' }}</dd></div>
        </dl>
    </div>

    <div class="lei-ca-card lei-ca-review-card lei-ca-review-card--payment">
        <div class="lei-ca-payment-head">
            <h4>Payment Verification</h4>
            @if ($subscription)
                <span class="lei-ca-pay-badge lei-ca-pay-badge--{{ $paymentVerified ? 'ok' : 'warn' }}">
                    <i class="fa-solid fa-{{ $paymentVerified ? 'circle-check' : 'clock' }}" aria-hidden="true"></i>
                    {{ $paymentVerified ? 'Payment verified' : 'Payment pending' }}
                </span>
            @else
                <span class="lei-ca-pay-badge lei-ca-pay-badge--muted">No subscription linked</span>
            @endif
        </div>

        @if ($subscription)
            <dl class="lei-ca-dl">
                <div><dt>Reference</dt><dd class="mono">{{ $subscription->reference }}</dd></div>
                <div><dt>Plan</dt><dd>{{ $subscription->plan_name }}</dd></div>
                <div><dt>Type</dt><dd>{{ ucfirst($subscription->plan_section ?? 'registration') }}</dd></div>
                @if ($plan)
                    <div><dt>Per year</dt><dd>{{ $plan->formattedYearlyPrice() }}</dd></div>
                @endif
                <div><dt>Total paid</dt><dd><strong>{{ $subscription->formattedAmount() }}</strong></dd></div>
                <div><dt>Duration</dt><dd>{{ $subscription->duration_years }} {{ Str::plural('year', (int) $subscription->duration_years) }}</dd></div>
                <div>
                    <dt>Payment status</dt>
                    <dd>
                        <span class="lei-ca-pay-pill lei-ca-pay-pill--{{ $subscription->payment_status === 'paid' ? 'ok' : 'warn' }}">
                            {{ $subscription->paymentStatusLabel() }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt>Subscription</dt>
                    <dd>
                        <span class="lei-ca-pay-pill lei-ca-pay-pill--{{ $subscription->status === 'active' ? 'ok' : 'muted' }}">
                            {{ $subscription->statusLabel() }}
                        </span>
                    </dd>
                </div>
                <div><dt>Valid from</dt><dd>{{ $subscription->starts_at?->format('M j, Y') ?? '—' }}</dd></div>
                <div><dt>Valid until</dt><dd>{{ $subscription->expires_at?->format('M j, Y') ?? '—' }}</dd></div>
                @if ($subscription->ip_address)
                    <div><dt>Payment IP</dt><dd class="mono">{{ $subscription->ip_address }}</dd></div>
                @endif
            </dl>
            @if (! $paymentVerified)
                <p class="lei-ca-hint lei-ca-hint--warn">
                    <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                    Payment is not marked as paid. Confirm with admin before signing if required by your process.
                </p>
            @endif
        @else
            <p class="lei-ca-hint">This application has no linked subscription record.</p>
        @endif
    </div>
</div>

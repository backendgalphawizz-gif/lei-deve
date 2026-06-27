@php
    $isEdit = $subscription->exists;
    $formAction = $isEdit ? route('admin.subscriptions.update', $subscription) : route('admin.subscriptions.store');
@endphp

<form method="POST" action="{{ $formAction }}" class="lei-wm-form">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    @if (! $isEdit)
        <label>Applicant
            <select name="user_id" required data-rules="required">
                <option value="">Select applicant</option>
                @foreach ($applicants as $applicant)
                    <option value="{{ $applicant->id }}" @selected(old('user_id') == $applicant->id)>{{ $applicant->name }} ({{ $applicant->email }})</option>
                @endforeach
            </select>
        </label>
        <label>Pricing Plan
            <select name="pricing_plan_id">
                <option value="">Custom / manual</option>
                @foreach ($pricingPlans as $plan)
                    <option value="{{ $plan->id }}" @selected(old('pricing_plan_id') == $plan->id)>{{ $plan->name }} — {{ $plan->formattedPrice() }}</option>
                @endforeach
            </select>
        </label>
    @else
        <div class="lei-wm-detail-grid" style="margin-bottom:20px;">
            <div class="lei-wm-detail-item"><span>Reference</span><p>{{ $subscription->reference }}</p></div>
            <div class="lei-wm-detail-item"><span>Customer</span><p>{{ $subscription->user?->name }}</p><small style="color:#64748b;">{{ $subscription->user?->email }}</small></div>
            <div class="lei-wm-detail-item"><span>Plan</span><p>{{ $subscription->plan_name }}</p></div>
            <div class="lei-wm-detail-item"><span>Amount</span><p>{{ $subscription->formattedAmount() }}</p></div>
            <div class="lei-wm-detail-item"><span>Start</span><p>{{ $subscription->starts_at?->format('M j, Y') ?? '—' }}</p></div>
            <div class="lei-wm-detail-item"><span>Expires</span><p>{{ $subscription->expires_at?->format('M j, Y') ?? '—' }}</p></div>
        </div>
    @endif

    <label>Subscription Status
        <select name="status" required>
            @foreach ($statuses as $key => $label)
                <option value="{{ $key }}" @selected(old('status', $subscription->status ?: 'active') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </label>
    <label>Payment Status
        <select name="payment_status" required>
            @foreach ($paymentStatuses as $key => $label)
                <option value="{{ $key }}" @selected(old('payment_status', $subscription->payment_status ?: 'paid') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </label>
    @if ($isEdit)
        <div class="lei-wm-form-row">
            <label>Starts At <input type="date" name="starts_at" value="{{ old('starts_at', $subscription->starts_at?->format('Y-m-d')) }}"></label>
            <label>Expires At <input type="date" name="expires_at" value="{{ old('expires_at', $subscription->expires_at?->format('Y-m-d')) }}"></label>
        </div>
    @endif
    <label>Admin Notes<textarea name="admin_notes" rows="4" placeholder="Internal notes...">{{ old('admin_notes', $subscription->admin_notes) }}</textarea></label>
    <div class="lei-wm-form-actions">
        <button type="submit" class="lei-wm-btn-primary">{{ $isEdit ? 'Save Changes' : 'Create Subscription' }}</button>
        <a href="{{ route('admin.subscriptions.index', ['tab' => 'subscriptions']) }}" class="lei-wm-btn-ghost">Cancel</a>
    </div>
</form>

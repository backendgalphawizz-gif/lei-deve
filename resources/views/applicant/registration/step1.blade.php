@extends('applicant.layouts.app')

@section('title', 'New LEI Registration')

@section('content')
@include('applicant.partials.stepper', ['currentStep' => 1, 'routeName' => 'applicant.registration.step'])

@if ($registrationPrefill ?? null)
<div class="lei-portal-info-banner" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:14px 18px;margin-bottom:20px;display:flex;gap:12px;align-items:flex-start;">
    <i class="fa-solid fa-building-circle-check" style="color:#16a34a;margin-top:2px;flex-shrink:0;"></i>
    <div style="font-size:13px;color:#166534;line-height:1.6;">
        <strong>Entity details pre-filled.</strong>
        @if (! empty($registrationPrefill['source_lei']))
            Information was loaded from GLEIF record <code style="font-size:12px;">{{ $registrationPrefill['source_lei'] }}</code>.
            Review and edit as needed — your new LEI code is <strong>{{ auth()->user()->lei_number }}</strong> (not the GLEIF code).
        @else
            Your search details were loaded. Review and edit before continuing — your LEI code is <strong>{{ auth()->user()->lei_number }}</strong>.
        @endif
    </div>
</div>
@endif

<div class="lei-portal-info-banner" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:14px 18px;margin-bottom:20px;display:flex;gap:12px;align-items:flex-start;">
    <i class="fa-solid fa-circle-info" style="color:#3b82f6;margin-top:2px;flex-shrink:0;"></i>
    <div style="font-size:13px;color:#1e40af;line-height:1.6;">
        <strong>Entity Search Tip:</strong> Enter your company's exact legal name and registration number as they appear on your certificate of incorporation.
        Duplicate LEI checks are performed automatically — if an LEI already exists for your entity, you will be notified.
    </div>
</div>

<form method="POST" action="{{ route('applicant.registration.save', ['step' => 1]) }}" class="lei-portal-card">
    @csrf
    <h2>Company Information</h2>

    @if ($errors->any())
        <div class="lei-portal-alert lei-portal-alert--error" role="alert" style="margin-bottom:20px;">
            <i class="fa-solid fa-circle-exclamation"></i>
            <ul style="margin:0;padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="lei-portal-form-grid">
        <div class="lei-portal-field full">
            <label for="entity_name">Legal Entity Name <span class="lei-field-required">*</span></label>
            <input id="entity_name" name="entity_name"
                   value="{{ old('entity_name', $draft['entity_name'] ?? $application->entity_name) }}"
                   placeholder="e.g. Acme Private Limited"
                   class="{{ $errors->has('entity_name') ? 'is-invalid' : '' }}"
                   required>
            <small class="lei-portal-field-hint">Enter the full legal name exactly as registered with your authority.</small>
            @error('entity_name')<p class="lei-portal-field-error">{{ $message }}</p>@enderror
        </div>
        <div class="lei-portal-field">
            <label for="registration_authority">Registration Authority <span class="lei-field-required">*</span></label>
            <input id="registration_authority" name="registration_authority"
                   value="{{ old('registration_authority', $draft['registration_authority'] ?? '') }}"
                   placeholder="e.g. Ministry of Corporate Affairs (MCA21)"
                   class="{{ $errors->has('registration_authority') ? 'is-invalid' : '' }}"
                   required>
            <small class="lei-portal-field-hint">The government body where your entity is registered.</small>
            @error('registration_authority')<p class="lei-portal-field-error">{{ $message }}</p>@enderror
        </div>
        <div class="lei-portal-field">
            <label for="registration_number">Registration / CIN Number <span class="lei-field-required">*</span></label>
            <input id="registration_number" name="registration_number"
                   value="{{ old('registration_number', $draft['registration_number'] ?? '') }}"
                   placeholder="e.g. U74999MH2010PTC123456"
                   class="{{ $errors->has('registration_number') ? 'is-invalid' : '' }}"
                   required>
            <small class="lei-portal-field-hint">Company Identification Number (CIN), registration number, or equivalent.</small>
            @error('registration_number')<p class="lei-portal-field-error">{{ $message }}</p>@enderror
        </div>
        <div class="lei-portal-field full">
            <label for="registered_address">Registered Address <span class="lei-field-required">*</span></label>
            <input id="registered_address" name="registered_address"
                   value="{{ old('registered_address', $draft['registered_address'] ?? '') }}"
                   placeholder="Full registered address including PIN/ZIP code"
                   class="{{ $errors->has('registered_address') ? 'is-invalid' : '' }}"
                   required>
            @error('registered_address')<p class="lei-portal-field-error">{{ $message }}</p>@enderror
        </div>
        <div class="lei-portal-field">
            <label for="country">Country of Incorporation <span class="lei-field-required">*</span></label>
            <input id="country" name="country"
                   value="{{ old('country', $draft['country'] ?? $application->country) }}"
                   placeholder="e.g. India"
                   class="{{ $errors->has('country') ? 'is-invalid' : '' }}"
                   required>
            @error('country')<p class="lei-portal-field-error">{{ $message }}</p>@enderror
        </div>
        <div class="lei-portal-field">
            <label for="entity_type">Entity Type <span class="lei-field-required">*</span></label>
            <select id="entity_type" name="entity_type"
                    class="{{ $errors->has('entity_type') ? 'is-invalid' : '' }}"
                    required>
                @foreach (['Limited Liability Company', 'Public Limited Company', 'Partnership', 'Trust', 'Sole Proprietorship', 'Government Entity', 'Non-Profit Organisation', 'Other'] as $type)
                    <option value="{{ $type }}" @selected(old('entity_type', $draft['entity_type'] ?? 'Limited Liability Company') === $type)>{{ $type }}</option>
                @endforeach
            </select>
            @error('entity_type')<p class="lei-portal-field-error">{{ $message }}</p>@enderror
        </div>
    </div>
    <div class="lei-portal-actions">
        <a href="{{ route('applicant.dashboard') }}" class="lei-btn-link">Cancel</a>
        <div class="right">
            <span id="lei-autosave-status" style="font-size:12px;color:#64748b;align-self:center;" aria-live="polite"></span>
            <button type="submit" name="draft" value="1" class="lei-btn-secondary" data-loading="Saving…">
                <i class="fa-regular fa-floppy-disk"></i> Save Draft
            </button>
            <button type="submit" class="lei-btn-primary" data-loading="Saving…">Next Step <i class="fa-solid fa-arrow-right"></i></button>
        </div>
    </div>
</form>

@push('scripts')
<script>
(function () {
    var form = document.querySelector('form[action*="step=1"]') || document.querySelector('form');
    if (!form) return;
    var KEY = 'lei_reg_step1_draft';
    var statusEl = document.getElementById('lei-autosave-status');

    /* Restore from localStorage */
    try {
        var saved = JSON.parse(localStorage.getItem(KEY) || 'null');
        if (saved) {
            Object.keys(saved).forEach(function (name) {
                var el = form.querySelector('[name="' + name + '"]');
                if (el && !el.value) el.value = saved[name];
            });
        }
    } catch (e) {}

    /* Auto-save on change */
    var saveTimer;
    form.addEventListener('input', function () {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(function () {
            var data = {};
            new FormData(form).forEach(function (v, k) { if (k !== '_token' && k !== 'draft') data[k] = v; });
            try {
                localStorage.setItem(KEY, JSON.stringify(data));
                if (statusEl) {
                    statusEl.textContent = 'Draft auto-saved';
                    setTimeout(function () { statusEl.textContent = ''; }, 2500);
                }
            } catch (e) {}
        }, 1200);
    });

    /* Clear on submit (non-draft) */
    form.addEventListener('submit', function (e) {
        var clicked = document.activeElement;
        if (clicked && clicked.value !== '1') {
            try { localStorage.removeItem(KEY); } catch (ex) {}
        }
        /* Show loading state */
        if (clicked && clicked.dataset.loading) {
            clicked.disabled = true;
            clicked.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> ' + clicked.dataset.loading;
        }
    });
})();
</script>
@endpush
@endsection

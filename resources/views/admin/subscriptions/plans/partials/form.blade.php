@php
    $isEdit = $plan->exists;
    $formAction = $isEdit ? route('admin.pricing-plans.update', $plan) : route('admin.pricing-plans.store');
@endphp

<form method="POST" action="{{ $formAction }}" class="lei-wm-form">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <label>Section
        <select name="section" required>
            <option value="registration" @selected(old('section', $plan->section) === 'registration')>Registration</option>
            <option value="renewal" @selected(old('section', $plan->section) === 'renewal')>Renewal</option>
        </select>
    </label>
    <label>Label <input type="text" name="label" value="{{ old('label', $plan->label) }}" placeholder="e.g. MOST POPULAR"></label>
    <label>Plan Name <input type="text" name="name" value="{{ old('name', $plan->name) }}" required data-rules="required|maxLen:120"></label>
    <div class="lei-wm-form-row">
        <label>Price <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $plan->price) }}" required data-rules="required|numeric|min:0"></label>
        <label>Duration (years) <input type="number" min="1" max="10" name="duration_years" value="{{ old('duration_years', $plan->duration_years ?? 1) }}" required data-rules="required|integer|min:1|max:10"></label>
    </div>
    <label>Price suffix <input type="text" name="price_suffix" value="{{ old('price_suffix', $plan->price_suffix ?? '/ entity') }}"></label>
    <label>Savings label <input type="text" name="savings_label" value="{{ old('savings_label', $plan->savings_label) }}" placeholder="e.g. SAVE $48 TOTAL"></label>
    <label>Features <small>One feature per line</small>
        <textarea name="features" rows="5" placeholder="Feature one&#10;Feature two">@if ($plan->exists){{ collect($plan->features ?? [])->pluck('text')->implode("\n") }}@else{{ old('features') }}@endif</textarea>
    </label>
    <div class="lei-wm-form-row">
        <label>Button text <input type="text" name="button_text" value="{{ old('button_text', $plan->button_text ?? 'Select Plan') }}"></label>
        <label>Button style
            <select name="button_style">
                <option value="outline" @selected(old('button_style', $plan->button_style) === 'outline')>Outline</option>
                <option value="solid" @selected(old('button_style', $plan->button_style) === 'solid')>Solid</option>
            </select>
        </label>
    </div>
    <label class="lei-wm-check"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $plan->is_featured))> Featured plan</label>
    <label class="lei-wm-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan->is_active ?? true))> Live on website & applicant portal</label>
    <div class="lei-wm-form-actions">
        <button type="submit" class="lei-wm-btn-primary">{{ $isEdit ? 'Save Plan' : 'Create Plan' }}</button>
        <a href="{{ route('admin.subscriptions.index', ['tab' => 'plans']) }}" class="lei-wm-btn-ghost">Cancel</a>
    </div>
</form>

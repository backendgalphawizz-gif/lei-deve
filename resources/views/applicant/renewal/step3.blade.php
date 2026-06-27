@extends('applicant.layouts.app')

@section('title', 'Renewal Documentation')

@section('content')
@include('applicant.partials.stepper', [
    'currentStep' => 3,
    'routeName' => 'applicant.renewal.step',
    'steps' => [1 => 'LEI Search', 2 => 'Renewal Request', 3 => 'Documentation', 4 => 'Payment'],
])

<form method="POST" action="{{ route('applicant.renewal.save', ['step' => 3]) }}" enctype="multipart/form-data" class="lei-portal-split">
    @csrf
    <div class="lei-portal-card">
        <h2>Upload Renewal Documents</h2>
        <label class="lei-portal-upload">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            <div>Drag and drop files here or browse</div>
            <input type="file" name="renewal_certificate" accept=".pdf,.jpg,.jpeg,.png" style="display:none">
        </label>
        <div class="lei-portal-actions">
            <a href="{{ route('applicant.renewal.step', ['step' => 2]) }}" class="lei-btn-secondary">Back to Step 2</a>
            <button type="submit" class="lei-btn-primary">Save & Continue</button>
        </div>
    </div>
    <aside class="lei-portal-summary">
        <h3>Total Payable</h3>
        <div class="lei-portal-summary-total"><span>Amount</span><span>$456.00</span></div>
    </aside>
</form>
@endsection

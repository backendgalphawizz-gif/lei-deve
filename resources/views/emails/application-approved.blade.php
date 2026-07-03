@extends('emails.layouts.base')

@section('subject', 'Your LEI is Active — ' . $leiNumber)

@section('body')
<p class="em-eyebrow">LEI Activated</p>
<h1 class="em-h1">Your LEI has been approved! ✅</h1>
<p class="em-p">Hello {{ $userName }},</p>
<p class="em-p">Your LEI application has been approved. Your unsigned certificate has been generated per <strong>ISO 17442-2:2020</strong> and forwarded to our Certificate Authority for digital signing.</p>

<div class="em-lei-box">
  <div class="em-lei-label">Your LEI Code</div>
  <div class="em-lei-code">{{ $leiNumber }}</div>
  <div class="em-lei-note">Pending CA digital signature · ISO 17442-2 OID {{ $leiOid }}</div>
</div>

<div class="em-dl">
  <div class="em-dl-row">
    <span class="em-dl-dt">Entity Name</span>
    <span class="em-dl-dd">{{ $entityName }}</span>
  </div>
  <div class="em-dl-row">
    <span class="em-dl-dt">Jurisdiction</span>
    <span class="em-dl-dd">{{ $country }}</span>
  </div>
  <div class="em-dl-row">
    <span class="em-dl-dt">Issue Date</span>
    <span class="em-dl-dd">{{ $approvedOn }}</span>
  </div>
  <div class="em-dl-row">
    <span class="em-dl-dt">Valid Until</span>
    <span class="em-dl-dd">{{ $expiryDate }}</span>
  </div>
  <div class="em-dl-row">
    <span class="em-dl-dt">Status</span>
    <span class="em-dl-dd"><span class="em-status-badge em-status-badge--green">Active</span></span>
  </div>
</div>

<p class="em-p"><strong>What happens next?</strong></p>
<p class="em-p">
  ✅ Application approved by registry team<br>
  ⏳ Unsigned certificate generated (ISO 17442-2 structure)<br>
  🔐 Certificate Authority will digitally sign your certificate<br>
  📧 You will receive another email when your signed certificate is ready to download
</p>

<a href="{{ $trackUrl }}" class="em-btn">Track Application Status →</a>

<hr class="em-divider">
<p class="em-p" style="font-size:13px;"><strong>Note:</strong> Your signed LEI certificate will be available for download only after CA digital signing is complete.</p>
@endsection

@extends('emails.layouts.base')

@section('subject', 'Application Received — ' . $applicationCode)

@section('body')
<p class="em-eyebrow">Application Submitted</p>
<h1 class="em-h1">We've received your LEI application</h1>
<p class="em-p">Hello {{ $userName }},</p>
<p class="em-p">Your LEI registration application has been successfully submitted. Our compliance team will review your documents and update you on the status.</p>

<div class="em-dl">
  <div class="em-dl-row">
    <span class="em-dl-dt">Reference No.</span>
    <span class="em-dl-dd" style="font-family:monospace;">{{ $applicationCode }}</span>
  </div>
  <div class="em-dl-row">
    <span class="em-dl-dt">Entity Name</span>
    <span class="em-dl-dd">{{ $entityName }}</span>
  </div>
  <div class="em-dl-row">
    <span class="em-dl-dt">LEI (Pre-assigned)</span>
    <span class="em-dl-dd" style="font-family:monospace;">{{ $leiNumber }}</span>
  </div>
  <div class="em-dl-row">
    <span class="em-dl-dt">Submission Date</span>
    <span class="em-dl-dd">{{ $submittedOn }}</span>
  </div>
  <div class="em-dl-row">
    <span class="em-dl-dt">Status</span>
    <span class="em-dl-dd"><span class="em-status-badge em-status-badge--orange">Under Review</span></span>
  </div>
</div>

<p class="em-p"><strong>What happens next?</strong></p>
<p class="em-p">
  ✅ Documents received and queued for review<br>
  ⏳ Our team will verify your entity details (typically 1–2 business days)<br>
  📧 You will receive an email once your LEI is approved and activated
</p>

<a href="{{ $trackUrl }}" class="em-btn">Track Application Status →</a>

<p class="em-p" style="font-size:13px;color:#94a3b8;">Need help? Reply to this email or raise a support ticket from your portal.</p>
@endsection

@extends('emails.layouts.base')

@section('subject', 'Payment Confirmation — ' . $reference)

@section('body')
<p class="em-eyebrow">Payment Received</p>
<h1 class="em-h1">Payment Confirmed ✅</h1>
<p class="em-p">Hello {{ $userName }},</p>
<p class="em-p">Your payment has been received and your subscription is now active. You may proceed with your LEI application.</p>

<div class="em-dl">
  <div class="em-dl-row">
    <span class="em-dl-dt">Reference</span>
    <span class="em-dl-dd" style="font-family:monospace;">{{ $reference }}</span>
  </div>
  <div class="em-dl-row">
    <span class="em-dl-dt">Plan</span>
    <span class="em-dl-dd">{{ $planName }}</span>
  </div>
  <div class="em-dl-row">
    <span class="em-dl-dt">Amount Paid</span>
    <span class="em-dl-dd">{{ $amount }}</span>
  </div>
  <div class="em-dl-row">
    <span class="em-dl-dt">Payment Date</span>
    <span class="em-dl-dd">{{ $paidOn }}</span>
  </div>
  <div class="em-dl-row">
    <span class="em-dl-dt">Valid Until</span>
    <span class="em-dl-dd">{{ $validUntil }}</span>
  </div>
  <div class="em-dl-row">
    <span class="em-dl-dt">Status</span>
    <span class="em-dl-dd"><span class="em-status-badge em-status-badge--green">Paid</span></span>
  </div>
</div>

<p class="em-p" style="font-size:13px;color:#64748b;">A GST tax invoice has been generated and is available in your portal under Payments → Invoice History.</p>

<a href="{{ $portalUrl }}" class="em-btn em-btn--gold">Continue to Application →</a>
@endsection

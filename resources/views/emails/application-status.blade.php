@extends('emails.layouts.base')

@section('subject', 'Application Update — ' . $applicationCode)

@section('body')
<p class="em-eyebrow">Application Update</p>
<h1 class="em-h1">
  @if ($status === 'clarification') Clarification Required
  @elseif ($status === 'rejected') Application Rejected
  @else Application Status Updated @endif
</h1>
<p class="em-p">Hello {{ $userName }},</p>

@if ($status === 'clarification')
  <p class="em-p">Our review team requires additional information to process your LEI application. Please log in to your portal and submit the requested clarification at your earliest convenience.</p>
  <div class="em-warning">
    <strong>Action Required:</strong> Your application will remain on hold until the clarification is submitted.
  </div>
@elseif ($status === 'rejected')
  <p class="em-p">After careful review, we are unable to approve your LEI application at this time. Please review the feedback below and resubmit with the required corrections.</p>
  <div class="em-warning">
    <strong>Application Rejected</strong> — Please review and resubmit.
  </div>
@else
  <p class="em-p">Your application status has been updated.</p>
@endif

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
    <span class="em-dl-dt">Updated Status</span>
    <span class="em-dl-dd">
      @if ($status === 'clarification')
        <span class="em-status-badge em-status-badge--orange">Clarification Required</span>
      @elseif ($status === 'rejected')
        <span class="em-status-badge em-status-badge--red">Rejected</span>
      @else
        <span class="em-status-badge em-status-badge--orange">{{ ucfirst($status) }}</span>
      @endif
    </span>
  </div>
</div>

<a href="{{ $trackUrl }}" class="em-btn">View Application →</a>
@endsection

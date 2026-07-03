@extends('emails.layouts.base')

@section('subject', 'Welcome to ' . (config('app.name')) . ' — Your LEI Code is Ready')

@section('body')
<p class="em-eyebrow">Registration Confirmed</p>
<h1 class="em-h1">Welcome, {{ $userName }}! 🎉</h1>
<p class="em-p">Your account has been created and verified. Your Legal Entity Identifier (LEI) has been assigned to your organisation.</p>

<div class="em-lei-box">
  <div class="em-lei-label">Your LEI Code</div>
  <div class="em-lei-code">{{ $leiNumber }}</div>
  <div class="em-lei-note">ISO 17442 Compliant · 20-character unique identifier</div>
</div>

<div class="em-dl">
  <div class="em-dl-row">
    <span class="em-dl-dt">Organisation</span>
    <span class="em-dl-dd">{{ $organizationName }}</span>
  </div>
  <div class="em-dl-row">
    <span class="em-dl-dt">Registered Email</span>
    <span class="em-dl-dd">{{ $userEmail }}</span>
  </div>
  <div class="em-dl-row">
    <span class="em-dl-dt">LEI Status</span>
    <span class="em-dl-dd"><span class="em-status-badge em-status-badge--orange">Pending Activation</span></span>
  </div>
</div>

<p class="em-p"><strong>Next Steps:</strong></p>
<p class="em-p">1. Log in to your portal and complete your LEI application (upload documents, fill in entity details).<br>
2. Our team will verify your documents and activate your LEI.<br>
3. Once approved, you will receive an email with your official LEI certificate.</p>

<a href="{{ $portalUrl }}" class="em-btn em-btn--gold">Go to My Portal →</a>

<p class="em-p" style="font-size:13px;color:#94a3b8;">Please save your LEI code: <strong style="font-family:monospace;">{{ $leiNumber }}</strong>. You will need this for financial transactions, regulatory filings, and cross-border payments.</p>
@endsection

@extends('emails.layouts.base')

@section('subject', 'Your LEI Certificate is Ready')

@section('body')
<p class="em-eyebrow">Certificate Issued</p>
<h1 class="em-h1">Your digitally signed LEI certificate is ready</h1>
<p class="em-p">Hello {{ $userName }},</p>
<p class="em-p">Your Legal Entity Identifier certificate has been digitally signed by our Certificate Authority in accordance with <strong>ISO 17442-2:2020</strong> and is now available for download.</p>

<div class="em-lei-box">
  <div class="em-lei-label">Your LEI Code</div>
  <div class="em-lei-code">{{ $leiNumber }}</div>
  <div class="em-lei-note">Certificate Serial: {{ $serialNumber }} · Valid until {{ $validUntil }}</div>
</div>

<div class="em-dl">
  <div class="em-dl-row">
    <span class="em-dl-dt">Entity Name</span>
    <span class="em-dl-dd">{{ $entityName }}</span>
  </div>
  <div class="em-dl-row">
    <span class="em-dl-dt">Signed At</span>
    <span class="em-dl-dd">{{ $signedAt }}</span>
  </div>
  <div class="em-dl-row">
    <span class="em-dl-dt">Standard</span>
    <span class="em-dl-dd">ISO 17442-2:2020 (X.509 + LEI OID)</span>
  </div>
  <div class="em-dl-row">
    <span class="em-dl-dt">Status</span>
    <span class="em-dl-dd"><span class="em-status-badge em-status-badge--green">Digitally Signed</span></span>
  </div>
</div>

<a href="{{ $certificateUrl }}" class="em-btn em-btn--gold">Download Signed Certificate →</a>

<p class="em-p" style="font-size:13px;color:#94a3b8;">This certificate embeds your LEI using OID 1.3.6.1.4.1.52266.1 as specified in ISO 17442-2:2020.</p>
@endsection

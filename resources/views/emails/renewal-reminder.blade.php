@extends('emails.layouts.base')

@section('subject', 'LEI Renewal Reminder — Expires ' . $expiryDate)

@section('body')
<p class="em-eyebrow">Renewal Reminder</p>
<h1 class="em-h1">Your LEI expires in {{ $daysLeft }} days</h1>
<p class="em-p">Hello {{ $userName }},</p>
<p class="em-p">This is a reminder that your Legal Entity Identifier is approaching its expiry date. An expired LEI may prevent your organisation from participating in regulated financial transactions.</p>

<div class="em-lei-box">
  <div class="em-lei-label">Your LEI</div>
  <div class="em-lei-code">{{ $leiNumber }}</div>
  <div class="em-lei-note">Expires on {{ $expiryDate }}</div>
</div>

<div class="em-dl">
  <div class="em-dl-row">
    <span class="em-dl-dt">Entity Name</span>
    <span class="em-dl-dd">{{ $entityName }}</span>
  </div>
  <div class="em-dl-row">
    <span class="em-dl-dt">Expiry Date</span>
    <span class="em-dl-dd" style="color:#c2410c;font-weight:700;">{{ $expiryDate }}</span>
  </div>
  <div class="em-dl-row">
    <span class="em-dl-dt">Days Remaining</span>
    <span class="em-dl-dd" style="color:#c2410c;font-weight:700;">{{ $daysLeft }} days</span>
  </div>
</div>

<p class="em-p">Renew your LEI now to maintain uninterrupted compliance. The renewal process typically takes 1–2 business days.</p>

<a href="{{ $renewUrl }}" class="em-btn em-btn--gold">Renew My LEI Now →</a>

<hr class="em-divider">
<p class="em-p" style="font-size:13px;color:#94a3b8;">If you have already initiated a renewal, please disregard this reminder.</p>
@endsection

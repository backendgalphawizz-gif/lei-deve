<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>LEI Certificate — {{ $application->lei_number }}</title>
<style>
  @page { margin: 36px 46px 34px; }
  div, p, table, td, tr, th, ol, ul, li, h1, h2, h3, h4, img {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }
  body {
    margin: 0;
    padding: 0;
    font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
    font-size: 10.5px;
    color: #111;
    line-height: 1.4;
  }
  .page { width: 100%; position: relative; }
  .header-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
  .header-table td { vertical-align: top; }
  .verify-wrap { width: 118px; }
  .verify-frame {
    width: 108px;
    border: 1px solid #cfcfcf;
    padding: 3px;
    text-align: center;
  }
  .verify-label {
    font-size: 7px;
    letter-spacing: 0.28em;
    color: #b0b0b0;
    font-weight: 700;
    line-height: 1.2;
  }
  .verify-mid { width: 100%; border-collapse: collapse; }
  .verify-mid td { vertical-align: middle; }
  .verify-side {
    width: 10px;
    font-size: 6.5px;
    letter-spacing: 0.08em;
    color: #b0b0b0;
    font-weight: 700;
    line-height: 1.05;
    text-align: center;
  }
  .qr-img { width: 78px; height: 78px; display: block; margin: 0 auto; }
  .qr-fallback {
    width: 78px; height: 78px; margin: 0 auto;
    border: 1px dashed #bbb; color: #777; font-size: 8px;
    text-align: center; padding-top: 28px;
  }
  .seal-cell { text-align: right; width: 130px; }
  .seal-img { width: 112px; height: auto; display: inline-block; }
  .entity-block { margin-top: 6px; margin-bottom: 16px; }
  .entity-name {
    font-size: 19px;
    font-weight: 700;
    color: #0a0a0a;
    text-transform: uppercase;
    letter-spacing: 0.01em;
    line-height: 1.25;
    margin-bottom: 10px;
  }
  .meta-line { font-size: 11px; margin-bottom: 3px; color: #111; }
  .meta-line strong { font-weight: 700; }
  .address-row { margin-top: 8px; }
  .address-table { border-collapse: collapse; }
  .address-table td { vertical-align: middle; font-size: 10.5px; color: #222; }
  .flag-img { width: 16px; height: 11px; display: block; margin-right: 6px; }
  .renew-box {
    border: 1.5px solid #c9a36a;
    background: #fbf6ee;
    padding: 12px 14px 12px;
    margin: 8px 0 16px;
  }
  .renew-table { width: 100%; border-collapse: collapse; }
  .renew-table td { vertical-align: top; }
  .renew-title {
    font-size: 13px;
    font-weight: 700;
    color: #111;
    margin-bottom: 8px;
  }
  .renew-steps {
    margin: 0 0 0 16px;
    padding: 0;
    font-size: 10px;
    color: #222;
    line-height: 1.45;
  }
  .renew-steps li { margin-bottom: 4px; }
  .renew-steps a, .renew-link { color: #1d4ed8; text-decoration: underline; word-break: break-all; }
  .renew-qr-cell { width: 92px; text-align: right; padding-left: 10px; }
  .renew-qr { width: 78px; height: 78px; display: inline-block; }
  .renew-note {
    margin-top: 10px;
    font-size: 9.5px;
    color: #333;
    line-height: 1.5;
    text-align: left;
  }
  .footer-rule {
    border-top: 1px solid #d4d4d4;
    margin: 4px 0 10px;
  }
  .verify-text, .disclaimer {
    font-size: 8.5px;
    color: #444;
    line-height: 1.55;
    text-align: left;
    margin-bottom: 8px;
  }
  .brand-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
  .brand-table td { vertical-align: top; font-size: 8px; color: #555; line-height: 1.45; width: 50%; }
  .brand-left { padding-right: 12px; }
  .brand-right { padding-left: 12px; }
  .brand-name { font-size: 11px; font-weight: 700; color: #111; margin-bottom: 2px; }
  .brand-line { margin-bottom: 1px; }
  .bottom-note {
    margin-top: 10px;
    text-align: right;
    font-size: 7.5px;
    color: #777;
    font-style: italic;
  }
  .pending-banner {
    background: #fff7ed;
    border: 1px solid #fdba74;
    color: #9a3412;
    padding: 8px 10px;
    font-size: 9px;
    margin: 10px 0 0;
  }
  .watermark {
    position: absolute;
    top: 8px;
    left: 0;
    width: 100%;
    text-align: center;
    font-size: 13px;
    font-weight: 700;
    color: rgba(220, 38, 38, 0.55);
    letter-spacing: 0.14em;
    white-space: nowrap;
    z-index: 5;
    border: 1px solid rgba(220, 38, 38, 0.55);
    padding: 3px 0;
  }
  .content { position: relative; z-index: 1; }
  .content.has-watermark { padding-top: 26px; }
</style>
</head>
<body>
@php
  $brandName = $brandName ?? ($settings->company_name ?? 'LEI Registry');
  $legalName = $legalName ?? ($settings->legal_name ?? null);
  $registryAuthority = $registryAuthority ?? ($settings->registry_authority ?? null);
  $supportEmail = $supportEmail ?? ($settings->support_email ?? null);
  $supportPhone = $supportPhone ?? ($settings->support_phone ?? null);
  $officeAddress = $officeAddress ?? null;
  $websiteHost = preg_replace('#^https?://#i', '', (string) ($websiteUrl ?? ''));
@endphp
<div class="page">
  @if (! $signed)
    <div class="watermark">PENDING CA SIGNATURE</div>
  @endif

  <div class="content @if (! $signed) has-watermark @endif">
    <table class="header-table">
      <tr>
        <td class="verify-wrap">
          <div class="verify-frame">
            <div class="verify-label">VERIFY</div>
            <table class="verify-mid">
              <tr>
                <td class="verify-side">V<br>E<br>R<br>I<br>F<br>Y</td>
                <td style="text-align:center; padding: 2px 0;">
                  @if (! empty($qrDataUri))
                    <img src="{{ $qrDataUri }}" alt="Verify LEI" class="qr-img">
                  @else
                    <div class="qr-fallback">Scan to verify</div>
                  @endif
                </td>
                <td class="verify-side">V<br>E<br>R<br>I<br>F<br>Y</td>
              </tr>
            </table>
            <div class="verify-label">VERIFY</div>
          </div>
        </td>
        <td></td>
        <td class="seal-cell">
          @if (! empty($sealDataUri))
            <img src="{{ $sealDataUri }}" alt="LEI Certificate Seal" class="seal-img">
          @endif
        </td>
      </tr>
    </table>

    <div class="entity-block">
      <div class="entity-name">{{ $application->entity_name }}</div>
      <div class="meta-line"><strong>LEI:</strong> {{ $application->lei_number }}</div>
      <div class="meta-line"><strong>LEI registration status:</strong> ISSUED</div>
      <div class="meta-line"><strong>Next Renewal Date:</strong> {{ $renewalDate }}</div>
      @if ($registeredAddress)
        <div class="address-row">
          <table class="address-table">
            <tr>
              @if (! empty($flagDataUri))
                <td style="width:22px;"><img src="{{ $flagDataUri }}" alt="" class="flag-img"></td>
              @endif
              <td>{{ $registeredAddress }}</td>
            </tr>
          </table>
        </div>
      @endif
    </div>

    <div class="renew-box">
      <table class="renew-table">
        <tr>
          <td>
            <div class="renew-title">How to renew an LEI number?</div>
            <ol class="renew-steps">
              <li>Go to <span class="renew-link">{{ $renewUrl }}</span> or scan the QR code.</li>
              <li>The application form will be automatically filled with data from the GLEIF database.</li>
              <li>Check if the legal entity data is up to date and make any changes if needed.</li>
              <li>Choose the renewal period and submit your application and payment.</li>
              <li>We will take care of the LEI renewal. In 90% of the cases the LEI will be renewed within 24 hours.</li>
            </ol>
          </td>
          <td class="renew-qr-cell">
            @if (! empty($renewQrDataUri))
              <img src="{{ $renewQrDataUri }}" alt="Renew LEI" class="renew-qr">
            @elseif (! empty($qrDataUri))
              <img src="{{ $qrDataUri }}" alt="Renew LEI" class="renew-qr">
            @endif
          </td>
        </tr>
      </table>
      <div class="renew-note">
        An LEI code needs to be renewed annually. You can apply for the LEI renewal from&nbsp;<strong>{{ $renewalWindowStart }}</strong> until <strong>{{ $renewalDate }}</strong> to keep the LEI active. A lapsed LEI might impact your financial transactions according to RBI guidelines.
        @if ($supportPhone)
          For help with renewing the LEI code call our toll free number <strong>{{ $supportPhone }}</strong>.
        @endif
      </div>
    </div>

    <div class="footer-rule"></div>

    <p class="verify-text">
      The certificate's information can be verified by scanning the QR code at the header of the certificate
      or by searching for the legal entity at
      <strong>{{ $websiteHost ?: $websiteUrl }}/leicert</strong>.
    </p>

    <p class="disclaimer">
      LEI certificate is generated by {{ $brandName }} ({{ $websiteHost ?: $websiteUrl }}). LEI data is gathered
      from the GLEIF (www.gleif.org) database. {{ $brandName }} is not responsible for the accuracy of the data.
      {{ $brandName }} renders this data from the GLEIF public LEI database. This certificate does not necessarily
      indicate that {{ $brandName }} or its partner LOU manages this LEI.
    </p>

    <table class="brand-table">
      <tr>
        <td class="brand-left">
          <div class="brand-name">{{ $brandName }}®</div>
          @if ($registrarLei ?? null)
            <div class="brand-line">LEI: {{ $registrarLei }}</div>
          @endif
          @if (($ubisecureLei ?? null) || ($nasdaqLei ?? null))
            <div class="brand-line" style="margin-top:4px;">Official Registration Agent of</div>
            @if ($ubisecureLei ?? null)
              <div class="brand-line">Ubisecure Oy (Ubisecure RapidLEI) LEI: {{ $ubisecureLei }},</div>
            @endif
            @if ($nasdaqLei ?? null)
              <div class="brand-line">Nasdaq CSD SE (Nasdaq LEI) LEI: {{ $nasdaqLei }}</div>
            @endif
          @endif
          @if ($cin ?? null)
            <div class="brand-line" style="margin-top:4px;">CIN: {{ $cin }}</div>
          @endif
          @if ($gstin ?? null)
            <div class="brand-line">GSTIN: {{ $gstin }}</div>
          @endif
        </td>
        <td class="brand-right">
          @if ($registeredOfficeAddress ?? null)
            <div class="brand-line"><strong>Registered at:</strong> {{ $registeredOfficeAddress }}</div>
          @endif
          @if ($officeLocationAddress ?? null)
            <div class="brand-line" style="margin-top:4px;"><strong>Office Location:</strong> {{ $officeLocationAddress }}</div>
          @endif
          @if ($supportEmail)
            <div class="brand-line" style="margin-top:4px;">{{ $supportEmail }}</div>
          @endif
          @if ($websiteUrl)
            <div class="brand-line">{{ $websiteUrl }}</div>
          @endif
        </td>
      </tr>
    </table>

    @if (! $signed)
      <div class="pending-banner">
        <strong>Pending Certificate Authority signature.</strong>
        This certificate was generated upon admin approval and is awaiting digital signature by the CA officer.
      </div>
    @endif

    <div class="bottom-note">
      This document is computer generated and does not require a signature.
    </div>
  </div>
</div>
</body>
</html>

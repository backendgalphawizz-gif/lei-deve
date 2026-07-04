<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>LEI Certificate — {{ $application->lei_number }}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 11px;
    color: #1a1a1a;
    line-height: 1.45;
  }
  .page { padding: 26px 34px 30px; position: relative; min-height: 100%; }
  .top-row { width: 100%; margin-bottom: 18px; }
  .top-row-table { width: 100%; border-collapse: collapse; }
  .top-row-table td { vertical-align: top; }
  .qr-cell { width: 92px; padding-right: 14px; }
  .qr-box {
    width: 88px; height: 88px; border: 1px solid #cbd5e1; padding: 4px; background: #fff;
  }
  .qr-box img { width: 80px; height: 80px; display: block; }
  .qr-fallback {
    width: 80px; height: 80px; border: 1px dashed #94a3b8; font-size: 8px; color: #64748b;
    text-align: center; padding: 18px 4px; line-height: 1.2;
  }
  .entity-name {
    font-size: 18px; font-weight: 700; color: #0f172a; line-height: 1.25;
    margin-bottom: 10px; text-transform: uppercase;
  }
  .meta-line { margin-bottom: 5px; font-size: 11px; }
  .meta-line strong { color: #334155; font-weight: 700; }
  .meta-line span { color: #0f172a; }
  .address-line { margin-top: 8px; font-size: 11px; color: #334155; }
  .divider { border-top: 1px solid #dbeafe; margin: 16px 0; }
  .verify-text, .disclaimer {
    font-size: 9.5px; color: #475569; line-height: 1.55; margin-bottom: 10px; text-align: justify;
  }
  .brand-block {
    border: 1px solid #e2e8f0; background: #f8fafc; padding: 12px 14px; margin: 14px 0;
    font-size: 9px; color: #475569; line-height: 1.55;
  }
  .brand-block strong { color: #0f172a; font-size: 11px; }
  .brand-title { font-size: 14px; font-weight: 700; color: #1d4ed8; margin-bottom: 6px; }
  .renew-title {
    font-size: 12px; font-weight: 700; color: #0f172a; margin: 16px 0 8px;
    border-bottom: 2px solid #2563eb; display: inline-block; padding-bottom: 3px;
  }
  .renew-steps { margin: 0 0 10px 16px; font-size: 10px; color: #334155; }
  .renew-steps li { margin-bottom: 5px; }
  .renew-note {
    font-size: 9.5px; color: #475569; line-height: 1.55; background: #fffbeb;
    border: 1px solid #fde68a; padding: 10px 12px; border-radius: 6px;
  }
  .status-issued {
    display: inline-block; background: #dcfce7; color: #166534; font-size: 9px;
    font-weight: 700; padding: 2px 8px; border-radius: 4px; letter-spacing: .04em;
  }
  .pending-banner {
    background: #fff7ed; border: 1px solid #fdba74; color: #9a3412;
    padding: 10px 12px; font-size: 10px; margin: 14px 0; border-radius: 6px;
  }
  .sign-area {
    margin-top: 18px; padding-top: 14px; border-top: 1px solid #e2e8f0;
  }
  .sign-area-table { width: 100%; border-collapse: collapse; }
  .sign-area-table td { vertical-align: bottom; }
  .sign-label { font-size: 10px; color: #64748b; margin-bottom: 6px; }
  .sign-img { max-width: 160px; max-height: 64px; }
  .sign-meta { font-size: 9px; color: #475569; line-height: 1.5; }
  .sign-line {
    border-bottom: 1px solid #94a3b8; width: 180px; height: 48px; margin-bottom: 4px;
    text-align: center; padding-bottom: 4px;
  }
  .footer-note {
    margin-top: 16px; font-size: 9px; color: #64748b; font-style: italic;
    border-top: 1px dashed #cbd5e1; padding-top: 10px;
  }
  .watermark {
    position: absolute; top: 42%; left: 50%; transform: translate(-50%, -50%) rotate(-28deg);
    font-size: 46px; font-weight: 700; color: rgba(220, 38, 38, 0.08); letter-spacing: 0.15em;
    white-space: nowrap; z-index: 0;
  }
  .content { position: relative; z-index: 1; }
</style>
</head>
<body>
<div class="page">
  @if (! $signed)
    <div class="watermark">PENDING CA SIGNATURE</div>
  @endif

  <div class="content">
    <table class="top-row-table">
      <tr>
        <td class="qr-cell">
          <div class="qr-box">
            @if (! empty($qrDataUri))
              <img src="{{ $qrDataUri }}" alt="Verify LEI">
            @else
              <div class="qr-fallback">Scan to verify LEI</div>
            @endif
          </div>
        </td>
        <td>
          <div class="entity-name">{{ $application->entity_name }}</div>
          <div class="meta-line"><strong>LEI:</strong> <span>{{ $application->lei_number }}</span></div>
          <div class="meta-line"><strong>LEI registration status:</strong> <span class="status-issued">ISSUED</span></div>
          <div class="meta-line"><strong>Next Renewal Date:</strong> <span>{{ $renewalDate }}</span></div>
          @if ($registeredAddress)
            <div class="address-line">{{ $registeredAddress }}</div>
          @endif
        </td>
      </tr>
    </table>

    <div class="divider"></div>

    <p class="verify-text">
      The certificate's information can be verified by scanning the QR code at the header of the certificate
      or by searching for the legal entity at <strong>{{ $verifyUrl }}</strong>.
    </p>

    <p class="disclaimer">
      LEI certificate is generated by {{ $settings->company_name ?? 'LEI Registry' }} ({{ $websiteUrl }}).
      LEI data is maintained in accordance with ISO 17442 and GLEIF standards. This certificate confirms assignment
      of the LEI code to the named legal entity. {{ $settings->company_name ?? 'LEI Registry' }} renders this data
      from the GLEIF public LEI database and is not responsible for the accuracy of third-party source data.
    </p>

    <div class="brand-block">
      <div class="brand-title">{{ $settings->company_name ?? 'LEI Registry' }}®</div>
      @if ($settings->legal_name)
        <strong>{{ $settings->legal_name }}</strong><br>
      @endif
      @if ($settings->registry_authority)
        {{ $settings->registry_authority }}<br>
      @endif
      @if ($settings->support_email)
        {{ $settings->support_email }}
      @endif
      @if ($settings->support_phone)
        · {{ $settings->support_phone }}
      @endif
      @if ($settings->website_url)
        · {{ $settings->website_url }}
      @endif
      <br>
      @php
        $officeParts = array_filter([
          $settings->address_line,
          $settings->city,
          $settings->state,
          $settings->postal_code,
          $settings->country,
        ]);
      @endphp
      @if ($officeParts)
        Office: {{ implode(', ', $officeParts) }}<br>
      @endif
    </div>

    <div class="renew-title">How to renew an LEI number?</div>
    <ol class="renew-steps">
      <li>Go to <strong>{{ $renewUrl }}</strong> or scan the QR code above.</li>
      <li>The application form will be automatically filled with data from our registry records.</li>
      <li>Check if the legal entity data is up to date and make any changes if needed.</li>
      <li>Choose the renewal period and submit your application and payment.</li>
      <li>We will take care of the LEI renewal. In most cases the LEI will be renewed within 24 hours.</li>
    </ol>

    <div class="renew-note">
      An LEI code needs to be renewed annually. You can apply for LEI renewal from
      <strong>{{ $renewalWindowStart }}</strong> until <strong>{{ $renewalDate }}</strong> to keep the LEI active.
      A lapsed LEI might impact your financial transactions according to applicable regulatory guidelines.
      @if ($settings->support_phone)
        For help renewing your LEI, contact us at <strong>{{ $settings->support_phone }}</strong>.
      @endif
    </div>

    @if (! $signed)
      <div class="pending-banner">
        <strong>Pending Certificate Authority signature.</strong>
        This certificate was generated upon admin approval and is awaiting digital signature by the CA officer.
        The final signed certificate will be available for download once signing is complete.
      </div>
      <div class="footer-note">
        This certificate is computer-generated and is awaiting digital signature by the Certificate Authority.
      </div>
    @else
      <div class="sign-area">
        <table class="sign-area-table">
          <tr>
            <td style="width:55%;">
              <div class="sign-label">Authorized Certificate Authority Signature</div>
              <div class="sign-meta">
                Digitally signed by <strong>{{ $caUser?->name ?? 'Certificate Authority' }}</strong><br>
                Date: <strong>{{ $certificate->signed_at?->format('M j, Y H:i T') ?? now()->format('M j, Y H:i T') }}</strong><br>
                Certificate ID: {{ $certificate->serial_number }}
              </div>
            </td>
            <td style="width:45%; text-align:right;">
              @if (! empty($caSignatureDataUri))
                <img src="{{ $caSignatureDataUri }}" alt="CA Signature" class="sign-img">
              @else
                <div class="sign-line"></div>
                <div class="sign-meta">CA Officer</div>
              @endif
            </td>
          </tr>
        </table>
      </div>
      <div class="footer-note">
        This certificate is computer-generated and digitally signed by the Certificate Authority.
      </div>
    @endif
  </div>
</div>
</body>
</html>

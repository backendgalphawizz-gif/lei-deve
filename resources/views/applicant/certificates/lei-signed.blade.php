<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>LEI Certificate — {{ $application->lei_number }}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #1e293b; }
  .page { padding: 28px 36px; }
  .header { background: #0b162c; color: #fff; padding: 18px 22px; margin: -28px -36px 20px; border-bottom: 4px solid #c9a227; }
  .header h1 { font-size: 16px; margin-bottom: 4px; }
  .header p { font-size: 10px; color: rgba(255,255,255,.65); }
  .badge-signed { display: inline-block; background: #dcfce7; color: #15803d; font-size: 9px; font-weight: 700; padding: 4px 10px; border-radius: 4px; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 14px; }
  .iso-ref { font-size: 9px; color: #64748b; margin-bottom: 16px; }
  .section-title { font-size: 10px; font-weight: 700; color: #0b162c; text-transform: uppercase; letter-spacing: .08em; border-bottom: 2px solid #c9a227; padding-bottom: 4px; margin: 16px 0 10px; }
  .cert-data { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; font-family: 'DejaVu Sans Mono', monospace; font-size: 10px; line-height: 1.8; }
  .cert-data .label { color: #64748b; display: inline-block; min-width: 200px; }
  .cert-data .value { color: #0f172a; font-weight: 600; }
  .oid-block { background: #f0fdf4; border-left: 3px solid #16a34a; padding: 10px 14px; margin: 12px 0; font-family: monospace; font-size: 10px; }
  .lei-highlight { font-size: 20px; font-weight: 700; color: #065f46; letter-spacing: .1em; text-align: center; padding: 16px; background: linear-gradient(135deg,#f0fdf4,#ecfdf5); border: 2px solid #a7f3d0; border-radius: 8px; margin: 14px 0; }
  .sign-block { background: #0b162c; color: #fff; border-radius: 10px; padding: 16px 18px; margin-top: 16px; }
  .sign-block h3 { font-size: 11px; color: #c9a227; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 8px; }
  .sign-block p { font-size: 10px; line-height: 1.6; color: rgba(255,255,255,.85); }
  .sign-block-inner { display: table; width: 100%; }
  .sign-block-text { display: table-cell; vertical-align: middle; width: 55%; }
  .sign-block-image { display: table-cell; vertical-align: middle; text-align: right; width: 45%; }
  .sign-image { max-width: 180px; max-height: 70px; background: #fff; padding: 6px 10px; border-radius: 4px; }
  .sign-hash { font-family: monospace; font-size: 8px; color: #c9a227; word-break: break-all; margin-top: 8px; }
  .footer { margin-top: 20px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; display: flex; justify-content: space-between; }
  .gleif { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 10px 14px; font-size: 10px; color: #0369a1; margin: 12px 0; }
</style>
</head>
<body>
<div class="page">
  <div class="header">
    <h1>ISO 17442-2:2020 — Digitally Signed X.509 Certificate with Embedded LEI</h1>
    <p>{{ $settings->company_name ?? 'LEI Registry' }} · GLEIF Accredited Local Operating Unit</p>
  </div>

  <span class="badge-signed">✓ Digitally Signed by Certificate Authority</span>
  <p class="iso-ref">ISO 17442-2:2020 · ITU-T X.509 / ISO/IEC 9594-8 Compliant</p>

  <div class="lei-highlight">{{ $application->lei_number }}</div>

  <div class="gleif">
    This certificate embeds the LEI in accordance with ISO 17442-2 using registered OID {{ $certificate->lei_oid }}.
    Verify at <strong>gleif.org</strong> or through your LOU registry portal.
  </div>

  <p class="section-title">Certificate Data</p>
  <div class="cert-data">
    <div><span class="label">Version:</span> <span class="value">3 (0x2)</span></div>
    <div><span class="label">Serial Number:</span> <span class="value">{{ $certificate->serial_number }}</span></div>
    <div><span class="label">Signature Algorithm:</span> <span class="value">{{ $certificate->signature_algorithm }}</span></div>
    <div><span class="label">Issuer:</span> <span class="value">{{ $certificate->issuer_dn }}</span></div>
    <div><span class="label">Validity — Not Before:</span> <span class="value">{{ $certificate->valid_from?->format('M j, Y H:i:s') }} GMT</span></div>
    <div><span class="label">Validity — Not After:</span> <span class="value">{{ $certificate->valid_until?->format('M j, Y H:i:s') }} GMT</span></div>
    <div><span class="label">Subject:</span> <span class="value">{{ $certificate->subject_dn }}</span></div>
    <div><span class="label">Public Key Algorithm:</span> <span class="value">rsaEncryption (2048 bit)</span></div>
  </div>

  <p class="section-title">X509v3 LEI &amp; Role Extensions</p>
  <div class="oid-block">
    <div><strong>{{ $certificate->lei_oid }}:</strong> {{ $application->lei_number }}</div>
    @if ($certificate->certificate_role)
      <div style="margin-top:6px;"><strong>{{ $certificate->role_oid }}:</strong> {{ $certificate->certificate_role }}</div>
    @endif
  </div>

  <p class="section-title">Legal Entity</p>
  <div class="cert-data">
    <div><span class="label">Legal Entity Name:</span> <span class="value">{{ $application->entity_name }}</span></div>
    <div><span class="label">Registration Number:</span> <span class="value">{{ $application->draft_data['registration_number'] ?? '—' }}</span></div>
    <div><span class="label">Country:</span> <span class="value">{{ $application->country }}</span></div>
    <div><span class="label">Application Reference:</span> <span class="value">{{ $application->application_code }}</span></div>
  </div>

  <div class="sign-block">
    <h3>Digital Signature — Certificate Authority</h3>
    <div class="sign-block-inner">
      <div class="sign-block-text">
        <p>
          Signed by: <strong>{{ $caUser?->name ?? 'Certificate Authority' }}</strong><br>
          Signed at: <strong>{{ $certificate->signed_at?->format('M j, Y H:i:s T') ?? now()->format('M j, Y H:i:s T') }}</strong><br>
          Algorithm: {{ $certificate->signature_algorithm }}
        </p>
        @if ($signatureHash ?? $certificate->signature_hash)
          <div class="sign-hash">SHA-256: {{ $signatureHash ?? $certificate->signature_hash }}</div>
        @endif
      </div>
      @if (! empty($caSignatureDataUri))
        <div class="sign-block-image">
          <img src="{{ $caSignatureDataUri }}" alt="CA digital signature" class="sign-image">
        </div>
      @endif
    </div>
  </div>

  <div class="footer">
    <span>Certificate ID: {{ $certificate->serial_number }}</span>
    <span>ISO 17442-2:2020 · {{ config('app.url') }}</span>
  </div>
</div>
</body>
</html>

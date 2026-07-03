<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Unsigned LEI Certificate — {{ $application->lei_number }}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #1e293b; }
  .page { padding: 28px 36px; position: relative; }
  .watermark {
    position: absolute; top: 40%; left: 50%; transform: translate(-50%, -50%) rotate(-35deg);
    font-size: 52px; font-weight: 700; color: rgba(220, 38, 38, 0.12); letter-spacing: 0.2em;
    white-space: nowrap; z-index: 0;
  }
  .content { position: relative; z-index: 1; }
  .header { background: #0b162c; color: #fff; padding: 18px 22px; margin: -28px -36px 20px; }
  .header h1 { font-size: 16px; margin-bottom: 4px; }
  .header p { font-size: 10px; color: rgba(255,255,255,.65); }
  .badge { display: inline-block; background: #fef3c7; color: #92400e; font-size: 9px; font-weight: 700; padding: 4px 10px; border-radius: 4px; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 14px; }
  .iso-ref { font-size: 9px; color: #64748b; margin-bottom: 16px; }
  .section-title { font-size: 10px; font-weight: 700; color: #0b162c; text-transform: uppercase; letter-spacing: .08em; border-bottom: 2px solid #c9a227; padding-bottom: 4px; margin: 16px 0 10px; }
  .cert-data { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; font-family: 'DejaVu Sans Mono', monospace; font-size: 10px; line-height: 1.8; }
  .cert-data .label { color: #64748b; display: inline-block; min-width: 200px; }
  .cert-data .value { color: #0f172a; font-weight: 600; }
  .oid-block { background: #eff6ff; border-left: 3px solid #3b82f6; padding: 10px 14px; margin: 12px 0; font-family: monospace; font-size: 10px; }
  .lei-highlight { font-size: 18px; font-weight: 700; color: #065f46; letter-spacing: .1em; text-align: center; padding: 14px; background: #f0fdf4; border: 1px solid #a7f3d0; border-radius: 8px; margin: 14px 0; }
  .footer { margin-top: 24px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; }
  .notice { background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; padding: 10px 14px; font-size: 10px; color: #9a3412; margin-top: 14px; }
</style>
</head>
<body>
<div class="page">
  <div class="watermark">UNSIGNED — DRAFT</div>
  <div class="content">
    <div class="header">
      <h1>ISO 17442-2:2020 — X.509 Public Key Certificate with Embedded LEI</h1>
      <p>{{ $settings->company_name ?? 'LEI Registry' }} · Unsigned Certificate — Pending CA Digital Signature</p>
    </div>

    <span class="badge">Status: Unsigned — Awaiting Certificate Authority</span>
    <p class="iso-ref">Per ISO 17442-2:2020 — LEI embedded via OID {{ $certificate->lei_oid }} · Role via OID {{ $certificate->role_oid }}</p>

    <div class="lei-highlight">{{ $application->lei_number }}</div>

    <p class="section-title">Certificate Data (Annex A Structure)</p>
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

    <p class="section-title">X509v3 Extensions (ISO 17442-2)</p>
    <div class="oid-block">
      <div><strong>{{ $certificate->lei_oid }}:</strong> {{ $application->lei_number }}</div>
      @if ($certificate->certificate_role)
        <div style="margin-top:6px;"><strong>{{ $certificate->role_oid }}:</strong> {{ $certificate->certificate_role }}</div>
      @endif
    </div>

    <p class="section-title">Entity Reference Data</p>
    <div class="cert-data">
      <div><span class="label">Legal Entity Name:</span> <span class="value">{{ $application->entity_name }}</span></div>
      <div><span class="label">Registration Number:</span> <span class="value">{{ $application->draft_data['registration_number'] ?? '—' }}</span></div>
      <div><span class="label">Registration Authority:</span> <span class="value">{{ $application->draft_data['registration_authority'] ?? '—' }}</span></div>
      <div><span class="label">Country:</span> <span class="value">{{ $application->country }}</span></div>
      <div><span class="label">Entity Type:</span> <span class="value">{{ $application->draft_data['entity_type'] ?? '—' }}</span></div>
      <div><span class="label">Application Reference:</span> <span class="value">{{ $application->application_code }}</span></div>
    </div>

    <div class="notice">
      <strong>Unsigned Certificate.</strong> This document has been generated following admin approval and is pending digital signature by the Certificate Authority (CA). It is not valid for regulatory use until signed.
    </div>

    <div class="footer">
      Generated: {{ now()->format('M j, Y H:i:s T') }} · Certificate ID: {{ $certificate->serial_number }} · ISO 17442-2:2020 Compliant Structure
    </div>
  </div>
</div>
</body>
</html>

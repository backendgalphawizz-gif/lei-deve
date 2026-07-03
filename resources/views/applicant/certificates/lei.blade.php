<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>LEI Certificate — {{ $application->lei_number }}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    font-family: 'DejaVu Sans', 'Helvetica Neue', Arial, sans-serif;
    background: #fff;
    color: #1e293b;
    font-size: 13px;
  }
  .cert-page {
    width: 794px;
    min-height: 1123px;
    padding: 0;
    position: relative;
    background: #fff;
  }
  /* Header band */
  .cert-header {
    background: #0b162c;
    padding: 28px 48px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .cert-brand-name {
    font-size: 22px;
    font-weight: 700;
    color: #fff;
    letter-spacing: -.02em;
  }
  .cert-brand-sub {
    font-size: 11px;
    color: rgba(255,255,255,.55);
    margin-top: 3px;
  }
  .cert-header-badge {
    background: rgba(201,162,39,.15);
    border: 1px solid rgba(201,162,39,.4);
    border-radius: 8px;
    padding: 8px 16px;
    text-align: center;
  }
  .cert-header-badge strong {
    display: block;
    font-size: 11px;
    font-weight: 700;
    color: #c9a227;
    text-transform: uppercase;
    letter-spacing: .08em;
  }
  .cert-header-badge span {
    font-size: 10px;
    color: rgba(255,255,255,.55);
  }
  /* Gold divider */
  .cert-gold-band {
    height: 5px;
    background: linear-gradient(90deg, #c9a227 0%, #f0d060 50%, #c9a227 100%);
  }
  /* Body */
  .cert-body {
    padding: 40px 48px;
  }
  .cert-eyebrow {
    font-size: 11px;
    font-weight: 700;
    color: #c9a227;
    text-transform: uppercase;
    letter-spacing: .1em;
    margin-bottom: 6px;
  }
  .cert-title {
    font-size: 28px;
    font-weight: 700;
    color: #0b162c;
    line-height: 1.2;
    margin-bottom: 8px;
  }
  .cert-subtitle {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 32px;
    line-height: 1.5;
  }
  /* LEI box */
  .cert-lei-box {
    background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
    border: 2px solid #a7f3d0;
    border-radius: 14px;
    padding: 24px 32px;
    text-align: center;
    margin-bottom: 32px;
  }
  .cert-lei-label {
    font-size: 10px;
    font-weight: 700;
    color: #047857;
    text-transform: uppercase;
    letter-spacing: .12em;
    margin-bottom: 10px;
  }
  .cert-lei-code {
    font-family: 'DejaVu Sans Mono', 'Courier New', monospace;
    font-size: 28px;
    font-weight: 700;
    color: #065f46;
    letter-spacing: .12em;
    word-break: break-all;
  }
  .cert-lei-iso {
    font-size: 10px;
    color: #047857;
    margin-top: 8px;
  }
  /* Entity details */
  .cert-section-title {
    font-size: 11px;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: .08em;
    margin-bottom: 12px;
    padding-bottom: 6px;
    border-bottom: 1px solid #e2e8f0;
  }
  .cert-grid {
    display: table;
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 28px;
  }
  .cert-row {
    display: table-row;
  }
  .cert-dt {
    display: table-cell;
    width: 38%;
    padding: 9px 12px 9px 0;
    font-size: 12px;
    color: #64748b;
    font-weight: 600;
    vertical-align: top;
    border-bottom: 1px solid #f1f5f9;
  }
  .cert-dd {
    display: table-cell;
    padding: 9px 0;
    font-size: 12px;
    color: #0f172a;
    font-weight: 600;
    vertical-align: top;
    border-bottom: 1px solid #f1f5f9;
  }
  .cert-dd-mono {
    font-family: 'DejaVu Sans Mono', 'Courier New', monospace;
    font-size: 11px;
    letter-spacing: .04em;
  }
  .cert-badge-active {
    display: inline-block;
    background: #dcfce7;
    color: #15803d;
    font-size: 10px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: .06em;
  }
  /* Two-column grid */
  .cert-two-col {
    display: table;
    width: 100%;
    border-collapse: separate;
    border-spacing: 16px 0;
    margin-left: -16px;
  }
  .cert-col {
    display: table-cell;
    width: 50%;
    vertical-align: top;
  }
  /* Footer */
  .cert-footer {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: #f8fafc;
    border-top: 3px solid #0b162c;
    padding: 16px 48px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .cert-footer-left {
    font-size: 10px;
    color: #64748b;
    line-height: 1.6;
  }
  .cert-footer-right {
    text-align: right;
    font-size: 10px;
    color: #94a3b8;
  }
  .cert-qr-placeholder {
    width: 56px;
    height: 56px;
    background: #e2e8f0;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 8px;
    color: #94a3b8;
    text-align: center;
    border: 1px solid #cbd5e1;
  }
  .cert-gleif-note {
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 11px;
    color: #0369a1;
    margin-bottom: 28px;
    line-height: 1.5;
  }
  .cert-generated {
    font-size: 10px;
    color: #94a3b8;
    margin-top: 6px;
  }
</style>
</head>
<body>
<div class="cert-page">

  <div class="cert-header">
    <div>
      <div class="cert-brand-name">{{ $businessSettings->company_name ?? 'LEI Registry' }}</div>
      <div class="cert-brand-sub">Legal Entity Identifier Registry · GLEIF Accredited LOU</div>
    </div>
    <div class="cert-header-badge">
      <strong>ISO 17442</strong>
      <span>Compliant Certificate</span>
    </div>
  </div>

  <div class="cert-gold-band"></div>

  <div class="cert-body">
    <p class="cert-eyebrow">Official Certificate</p>
    <h1 class="cert-title">Legal Entity Identifier Certificate</h1>
    <p class="cert-subtitle">
      This certificate confirms that the entity named below has been assigned a globally unique
      Legal Entity Identifier (LEI) in accordance with ISO 17442 and GLEIF standards.
    </p>

    <div class="cert-lei-box">
      <div class="cert-lei-label">Legal Entity Identifier (LEI)</div>
      <div class="cert-lei-code">{{ $application->lei_number }}</div>
      <div class="cert-lei-iso">ISO 17442 Compliant · Registered in GLEIF Global LEI Index</div>
    </div>

    <div class="cert-gleif-note">
      This LEI is registered with the Global Legal Entity Identifier Foundation (GLEIF) and is recognised
      worldwide for regulatory reporting, financial transactions, and KYC/AML compliance purposes.
    </div>

    <p class="cert-section-title">Entity Information</p>
    <div class="cert-two-col">
      <div class="cert-col">
        <div class="cert-grid">
          <div class="cert-row">
            <div class="cert-dt">Legal Entity Name</div>
            <div class="cert-dd">{{ $application->entity_name }}</div>
          </div>
          <div class="cert-row">
            <div class="cert-dt">Country</div>
            <div class="cert-dd">{{ $application->country }}</div>
          </div>
          <div class="cert-row">
            <div class="cert-dt">Entity Type</div>
            <div class="cert-dd">{{ $application->entity_type ?? '—' }}</div>
          </div>
          <div class="cert-row">
            <div class="cert-dt">Registration No.</div>
            <div class="cert-dd cert-dd-mono">{{ $application->registration_number ?? '—' }}</div>
          </div>
        </div>
      </div>
      <div class="cert-col">
        <div class="cert-grid">
          <div class="cert-row">
            <div class="cert-dt">Issue Date</div>
            <div class="cert-dd">{{ $application->submitted_on?->format('M j, Y') ?? now()->format('M j, Y') }}</div>
          </div>
          <div class="cert-row">
            <div class="cert-dt">Expiry Date</div>
            <div class="cert-dd">{{ $application->expiry_date?->format('M j, Y') ?? '—' }}</div>
          </div>
          <div class="cert-row">
            <div class="cert-dt">Status</div>
            <div class="cert-dd"><span class="cert-badge-active">Active</span></div>
          </div>
          <div class="cert-row">
            <div class="cert-dt">Registry Authority</div>
            <div class="cert-dd">{{ $application->registration_authority ?? ($businessSettings->registry_authority ?? '—') }}</div>
          </div>
        </div>
      </div>
    </div>

    <p class="cert-section-title">Registration Details</p>
    <div class="cert-grid">
      <div class="cert-row">
        <div class="cert-dt">Application Reference</div>
        <div class="cert-dd cert-dd-mono">{{ $application->application_code }}</div>
      </div>
      <div class="cert-row">
        <div class="cert-dt">LEI Number</div>
        <div class="cert-dd cert-dd-mono">{{ $application->lei_number }}</div>
      </div>
      <div class="cert-row">
        <div class="cert-dt">Issuing Authority</div>
        <div class="cert-dd">{{ $businessSettings->company_name ?? 'LEI Registry' }}</div>
      </div>
      <div class="cert-row">
        <div class="cert-dt">Certificate Generated</div>
        <div class="cert-dd">{{ now()->format('M j, Y · g:i A T') }}</div>
      </div>
    </div>

  </div>

  <div class="cert-footer">
    <div class="cert-footer-left">
      <strong>{{ $businessSettings->company_name ?? 'LEI Registry' }}</strong><br>
      GLEIF Accredited Local Operating Unit (LOU)<br>
      {{ config('app.url') }}
    </div>
    <div class="cert-footer-right">
      <div>This is an officially issued LEI Certificate.</div>
      <div>Verify at <strong>gleif.org</strong> or <strong>{{ config('app.url') }}</strong></div>
      <div style="margin-top:4px;font-size:9px;">Certificate ID: {{ strtoupper(substr(md5($application->lei_number . $application->id), 0, 12)) }}</div>
    </div>
  </div>

</div>
</body>
</html>

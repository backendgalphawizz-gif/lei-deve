<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>GST Invoice — {{ $subscription->reference }}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 12px;
    color: #1e293b;
    background: #fff;
  }
  .inv-page { width: 794px; min-height: 1123px; padding: 0; position: relative; }
  .inv-header { background: #0b162c; padding: 24px 40px; display: flex; justify-content: space-between; align-items: center; }
  .inv-brand { color: #fff; }
  .inv-brand-name { font-size: 20px; font-weight: 700; letter-spacing: -.01em; }
  .inv-brand-sub { font-size: 10px; color: rgba(255,255,255,.55); margin-top: 2px; }
  .inv-header-right { text-align: right; }
  .inv-invoice-label { font-size: 10px; font-weight: 700; color: #c9a227; text-transform: uppercase; letter-spacing: .08em; }
  .inv-invoice-num { font-size: 18px; font-weight: 700; color: #fff; font-family: 'DejaVu Sans Mono', monospace; margin-top: 2px; }
  .inv-gold-band { height: 4px; background: linear-gradient(90deg, #c9a227 0%, #f0d060 50%, #c9a227 100%); }
  .inv-body { padding: 32px 40px 80px; }
  .inv-meta-grid { display: table; width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 28px; }
  .inv-meta-col { display: table-cell; width: 50%; vertical-align: top; }
  .inv-meta-col + .inv-meta-col { padding-left: 24px; }
  .inv-meta-label { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 6px; }
  .inv-meta-val { font-size: 13px; font-weight: 600; color: #0f172a; line-height: 1.6; }
  .inv-meta-val-sub { font-size: 11px; color: #64748b; margin-top: 2px; }
  .inv-divider { border: none; border-top: 1px solid #e2e8f0; margin: 20px 0; }
  .inv-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
  .inv-table th { background: #0b162c; color: #fff; padding: 10px 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; text-align: left; }
  .inv-table th:last-child { text-align: right; }
  .inv-table td { padding: 12px 12px; border-bottom: 1px solid #f1f5f9; font-size: 12px; }
  .inv-table td:last-child { text-align: right; font-weight: 600; }
  .inv-table tr:last-child td { border-bottom: none; }
  .inv-totals { float: right; width: 280px; }
  .inv-total-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 12px; border-bottom: 1px solid #f1f5f9; }
  .inv-total-row:last-child { border-bottom: none; font-size: 15px; font-weight: 700; color: #0b162c; padding-top: 10px; }
  .inv-total-row dt { color: #64748b; }
  .inv-total-row dd { font-weight: 600; }
  .inv-badge { display: inline-block; background: #dcfce7; color: #15803d; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 20px; text-transform: uppercase; letter-spacing: .04em; }
  .inv-note { font-size: 10px; color: #94a3b8; margin-top: 8px; clear: both; border-top: 1px solid #e2e8f0; padding-top: 16px; line-height: 1.7; }
  .inv-footer { position: absolute; bottom: 0; left: 0; right: 0; background: #f8fafc; border-top: 2px solid #0b162c; padding: 14px 40px; display: flex; justify-content: space-between; font-size: 10px; color: #94a3b8; }
</style>
</head>
<body>
<div class="inv-page">

  <div class="inv-header">
    <div class="inv-brand">
      <div class="inv-brand-name">{{ $businessSettings->company_name ?? 'LEI Registry' }}</div>
      <div class="inv-brand-sub">GLEIF Accredited LOU · GST Tax Invoice</div>
    </div>
    <div class="inv-header-right">
      <div class="inv-invoice-label">Tax Invoice</div>
      <div class="inv-invoice-num">{{ $subscription->reference }}</div>
    </div>
  </div>
  <div class="inv-gold-band"></div>

  <div class="inv-body">

    <div class="inv-meta-grid">
      <div class="inv-meta-col">
        <div class="inv-meta-label">Bill To</div>
        <div class="inv-meta-val">{{ $user->name }}</div>
        <div class="inv-meta-val-sub">{{ $user->organization_name ?: '—' }}</div>
        <div class="inv-meta-val-sub">{{ $user->email }}</div>
      </div>
      <div class="inv-meta-col">
        <div class="inv-meta-label">Invoice Details</div>
        <div class="inv-meta-val-sub">Date: <strong>{{ $subscription->created_at?->format('M j, Y') ?? now()->format('M j, Y') }}</strong></div>
        <div class="inv-meta-val-sub">Reference: <strong style="font-family:monospace;">{{ $subscription->reference }}</strong></div>
        <div class="inv-meta-val-sub">Payment Status: <span class="inv-badge">{{ strtoupper($subscription->payment_status) }}</span></div>
      </div>
    </div>

    <hr class="inv-divider">

    <table class="inv-table">
      <thead>
        <tr>
          <th>Description</th>
          <th>Type</th>
          <th>Duration</th>
          <th>Base Amount</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>{{ $subscription->plan_name }}</td>
          <td>{{ ucfirst($subscription->subscription_type ?? 'registration') }}</td>
          <td>{{ $subscription->duration_years ?? 1 }} Year(s)</td>
          <td>{{ $currency }}{{ number_format($baseAmount, 2) }}</td>
        </tr>
      </tbody>
    </table>

    <div style="clear:both;height:16px;"></div>
    <div class="inv-totals">
      <div class="inv-total-row"><dt>Subtotal</dt><dd>{{ $currency }}{{ number_format($baseAmount, 2) }}</dd></div>
      <div class="inv-total-row"><dt>GST (18%)</dt><dd>{{ $currency }}{{ number_format($gstAmount, 2) }}</dd></div>
      <div class="inv-total-row"><dt>Total Payable</dt><dd>{{ $currency }}{{ number_format($totalAmount, 2) }}</dd></div>
    </div>

    <div class="inv-note" style="margin-top:80px;">
      This is a system-generated GST Tax Invoice. No signature is required.<br>
      GSTIN: {{ $businessSettings->gstin ?? 'Applied For' }} · {{ $businessSettings->company_name ?? config('app.name') }}<br>
      For queries, contact us at {{ $businessSettings->support_email ?? config('mail.from.address') }}
    </div>

  </div>

  <div class="inv-footer">
    <span>{{ $businessSettings->company_name ?? 'LEI Registry' }} · GLEIF Accredited</span>
    <span>{{ config('app.url') }}</span>
    <span>Generated: {{ now()->format('M j, Y') }}</span>
  </div>

</div>
</body>
</html>

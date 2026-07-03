<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>@yield('subject', 'LEI Registry Notification')</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { background: #f1f5f9; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color: #334155; }
  .em-wrap { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 16px rgba(0,0,0,.08); }
  .em-header { background: #0b162c; padding: 28px 36px; }
  .em-header-brand { display: flex; align-items: center; gap: 12px; }
  .em-header-icon { width: 40px; height: 40px; background: #c9a227; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
  .em-header-name { color: #fff; font-size: 18px; font-weight: 700; letter-spacing: -.02em; }
  .em-header-sub { color: rgba(255,255,255,.55); font-size: 12px; }
  .em-body { padding: 36px; }
  .em-eyebrow { font-size: 11px; font-weight: 700; color: #c9a227; text-transform: uppercase; letter-spacing: .1em; margin-bottom: 8px; }
  .em-h1 { font-size: 22px; font-weight: 700; color: #0b162c; line-height: 1.3; margin-bottom: 16px; }
  .em-p { font-size: 15px; color: #475569; line-height: 1.7; margin-bottom: 14px; }
  .em-lei-box { background: linear-gradient(135deg,#f0fdf4,#ecfdf5); border: 1px solid #a7f3d0; border-radius: 12px; padding: 20px 24px; margin: 22px 0; text-align: center; }
  .em-lei-label { font-size: 11px; font-weight: 700; color: #047857; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 8px; }
  .em-lei-code { font-family: 'Courier New', monospace; font-size: 24px; font-weight: 700; color: #065f46; letter-spacing: .06em; word-break: break-all; }
  .em-lei-note { font-size: 12px; color: #047857; margin-top: 8px; }
  .em-btn { display: inline-block; background: #0b162c; color: #fff !important; font-weight: 700; font-size: 14px; padding: 12px 28px; border-radius: 10px; text-decoration: none; margin: 16px 0; }
  .em-btn--gold { background: #c9a227; color: #0b162c !important; }
  .em-otp-box { background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 20px 24px; margin: 22px 0; text-align: center; }
  .em-otp-code { font-family: 'Courier New', monospace; font-size: 36px; font-weight: 700; color: #92400e; letter-spacing: .4em; }
  .em-otp-expiry { font-size: 12px; color: #b45309; margin-top: 8px; }
  .em-dl { border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; margin: 20px 0; }
  .em-dl-row { display: flex; padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
  .em-dl-row:last-child { border-bottom: none; }
  .em-dl-dt { color: #64748b; min-width: 140px; font-weight: 500; }
  .em-dl-dd { color: #0f172a; font-weight: 600; }
  .em-status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
  .em-status-badge--green { background: #dcfce7; color: #15803d; }
  .em-status-badge--red { background: #fee2e2; color: #b91c1c; }
  .em-status-badge--orange { background: #ffedd5; color: #c2410c; }
  .em-divider { border: none; border-top: 1px solid #f1f5f9; margin: 24px 0; }
  .em-warning { background: #fff5f5; border: 1px solid #fca5a5; border-radius: 10px; padding: 14px 18px; font-size: 13px; color: #b91c1c; margin: 18px 0; }
  .em-footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 36px; text-align: center; font-size: 12px; color: #94a3b8; line-height: 1.6; }
  .em-footer a { color: #64748b; text-decoration: none; margin: 0 8px; }
  .em-trust { display: flex; justify-content: center; gap: 16px; margin-top: 10px; flex-wrap: wrap; }
  .em-trust span { font-size: 11px; color: #94a3b8; }
</style>
</head>
<body>
<div class="em-wrap">
  <div class="em-header">
    <div class="em-header-brand">
      <div class="em-header-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0b162c" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      </div>
      <div>
        <div class="em-header-name">{{ $businessName ?? config('app.name') }}</div>
        <div class="em-header-sub">Legal Entity Identifier Registry</div>
      </div>
    </div>
  </div>
  <div class="em-body">
    @yield('body')
  </div>
  <div class="em-footer">
    <p>This is an automated message from <strong>{{ $businessName ?? config('app.name') }}</strong>.</p>
    <p>Please do not reply directly to this email.</p>
    <div class="em-trust">
      <span>🔒 SSL Secured</span>
      <span>🛡️ ISO 27001</span>
      <span>✅ GLEIF Accredited</span>
    </div>
    <p style="margin-top:12px;">
      <a href="{{ config('app.url') }}/pages/privacy-policy">Privacy Policy</a> ·
      <a href="{{ config('app.url') }}/pages/terms-of-service">Terms of Service</a> ·
      <a href="{{ config('app.url') }}">Visit Portal</a>
    </p>
    <p style="margin-top:8px;font-size:11px;">© {{ date('Y') }} {{ $businessName ?? config('app.name') }}. All rights reserved.</p>
  </div>
</div>
</body>
</html>

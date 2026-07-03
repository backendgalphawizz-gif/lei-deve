<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $businessSettings->portal_title }} | {{ $businessSettings->company_name }}</title>
    @if ($businessSettings->faviconUrl())
        <link rel="icon" href="{{ $businessSettings->faviconUrl() }}">
    @endif
    <style>:root { {{ $businessSettings->cssVars() }} }</style>
    <link rel="stylesheet" href="{{ asset('css/lei-admin.css') }}?v=4">
    <link rel="stylesheet" href="{{ asset('css/lei-admin-actions.css') }}?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="lei-login-page">
    <header class="lei-login-header">
        <div class="lei-login-logo">
            <img src="{{ $businessSettings->logoUrl() }}" alt="{{ $businessSettings->company_name }}">
        </div>
        <h1>{{ $businessSettings->portal_title ?? 'Super Admin Portal' }}</h1>
        <p>{{ $businessSettings->tagline ?? 'Registry Services Infrastructure' }}</p>
        @if (!empty($businessSettings) && $businessSettings->show_maintenance_banner && $businessSettings->maintenance_message)
            <div class="lei-login-maint-banner">{{ $businessSettings->maintenance_message }}</div>
        @endif
    </header>

    <div class="lei-login-card">
        @if ($errors->any())
            <div class="lei-alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf

            <label class="lei-field-label" for="system_id">Admin Identifier</label>
            <div class="lei-input-wrap">
                <svg class="lei-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                <input type="text" id="system_id" name="system_id" value="{{ old('system_id') }}"
                       placeholder="admin@gmail.com" required autofocus autocomplete="username" data-rules="required|maxLen:64">
            </div>

            <label class="lei-field-label" for="password">Secure Token</label>
            <div class="lei-input-wrap">
                <svg class="lei-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input type="password" id="password" name="password" placeholder="••••••••••" required data-rules="required|minLen:1">
                <button type="button" class="lei-toggle-pwd" id="togglePassword" aria-label="Toggle password">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
            </div>

            <!-- <div class="lei-login-options">
                <label class="lei-remember">
                    <input type="checkbox" name="remember" value="1">
                    Remember device
                </label>
                <a href="#" class="lei-recovery-link">Recovery Options</a>
            </div> -->

            <button type="submit" class="lei-btn-primary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <path d="M9 12l2 2 4-4"/>
                </svg>
                Secure Login
            </button>
        </form>

        @if (app()->environment('local'))
            <div class="lei-login-dev-hint" style="margin-top:20px;padding:14px;background:#f0f7ff;border:1px solid #c5daf5;border-radius:8px;font-size:12px;color:#334155;line-height:1.6;">
                <strong style="display:block;margin-bottom:6px;color:#1a5fad;">Dev login accounts</strong>
                Super Admin: <code>admin@gmail.com</code> / <code>12345678</code><br>
                CA Officer: <code>ca@registry-ops.int</code> / <code>12345678</code>
            </div>
        @endif
    </div>

    @if (session('error'))
        <script>window.__leiFlashMessages = @json(['error' => session('error')]);</script>
    @endif
    <div id="leiToastStack" class="lei-toast-stack" aria-live="polite"></div>
    <script src="{{ asset('js/lei-admin-validation.js') }}?v=4"></script>
    <script src="{{ asset('js/lei-admin-toast.js') }}?v=1"></script>
    <script>
        document.getElementById('togglePassword')?.addEventListener('click', function () {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
        });
    </script>
</body>
</html>

@extends('admin.layouts.app')

@section('title', 'Business Settings')
@section('body_class', 'lei-page-business-settings')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-business-settings.css') }}?v=2">
@endpush

@section('breadcrumbs')
    @include('admin.partials.breadcrumbs', ['current' => 'Business Settings'])
@endsection

@section('content')
<div class="lei-bs-page">
    <div class="lei-bs-hero">
        <div class="lei-bs-hero-text">
            <h2>Business Settings</h2>
            <p>Logo, portal title, colors, search bar, and contact — changes apply instantly across the admin panel.</p>
        </div>
        <div class="lei-bs-hero-meta">
            <span class="lei-bs-pill">{{ $settings->company_name }}</span>
            <span class="lei-bs-pill lei-bs-pill--muted">{{ $settings->registry_authority }}</span>
        </div>
    </div>

    @if ($errors->any())
        <div class="lei-bs-errors">
            <strong>Please fix the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.business-settings.update') }}" enctype="multipart/form-data" class="lei-bs-form" id="leiBsForm">
        @csrf
        @method('PUT')

        <div class="lei-bs-layout">
            <div class="lei-bs-main">
                <section class="lei-bs-card">
                    <h3>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                        Dashboard Page
                    </h3>
                    <div class="lei-bs-grid lei-bs-grid--2">
                        <label class="lei-bs-field">
                            <span>Dashboard Title</span>
                            <input type="text" name="dashboard_title" value="{{ old('dashboard_title', $settings->dashboard_title) }}">
                        </label>
                        <label class="lei-bs-field">
                            <span>Period Button Label</span>
                            <input type="text" name="dashboard_period_label" value="{{ old('dashboard_period_label', $settings->dashboard_period_label) }}">
                        </label>
                        <label class="lei-bs-field lei-bs-field--full">
                            <span>Dashboard Subtitle</span>
                            <input type="text" name="dashboard_subtitle" value="{{ old('dashboard_subtitle', $settings->dashboard_subtitle) }}">
                        </label>
                    </div>
                </section>

                <section class="lei-bs-card lei-bs-card--header">
                    <h3>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Top Header Bar
                    </h3>
                    <div class="lei-bs-grid lei-bs-grid--2">
                        <label class="lei-bs-field">
                            <span>Welcome Prefix</span>
                            <input type="text" name="welcome_prefix" value="{{ old('welcome_prefix', $settings->welcome_prefix) }}" placeholder="Welcome," data-bs-preview="prefix">
                        </label>
                        <label class="lei-bs-field">
                            <span>Header Subtitle</span>
                            <input type="text" name="header_subtitle" value="{{ old('header_subtitle', $settings->header_subtitle) }}" placeholder="GLEIF Accredited LOU" data-bs-preview="subtitle">
                            <small class="lei-bs-field-hint">Empty = Registry Authority / Tagline</small>
                        </label>
                        <label class="lei-bs-field">
                            <span>Search Placeholder</span>
                            <input type="text" name="search_placeholder" value="{{ old('search_placeholder', $settings->search_placeholder) }}" data-bs-preview="search">
                        </label>
                        <label class="lei-bs-field">
                            <span>Notification Badge Count</span>
                            <input type="number" name="header_notification_count" value="{{ old('header_notification_count', $settings->header_notification_count) }}" min="0" max="99" data-rules="integer|min:0|max:99" data-bs-preview="notif">
                        </label>
                        <label class="lei-bs-field">
                            <span>Header Logo Source</span>
                            <select name="header_logo_source">
                                <option value="sidebar" @selected(old('header_logo_source', $settings->header_logo_source) === 'sidebar')>Sidebar Icon</option>
                                <option value="main" @selected(old('header_logo_source', $settings->header_logo_source) === 'main')>Main Logo</option>
                            </select>
                        </label>
                        <label class="lei-bs-field lei-bs-check-field">
                            <span class="lei-bs-check">
                                <input type="checkbox" name="header_show_logo" value="1" @checked(old('header_show_logo', $settings->header_show_logo)) data-bs-preview="showlogo">
                                Show logo in header (left)
                            </span>
                            <span class="lei-bs-check">
                                <input type="checkbox" name="header_show_notifications" value="1" @checked(old('header_show_notifications', $settings->header_show_notifications)) data-bs-preview="shownotif">
                                Show notification bell
                            </span>
                        </label>
                    </div>
                </section>

                <section class="lei-bs-card">
                    <h3>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                        Brand Identity
                    </h3>
                    <div class="lei-bs-grid lei-bs-grid--2">
                        <label class="lei-bs-field">
                            <span>Company Name <em>*</em></span>
                            <input type="text" name="company_name" value="{{ old('company_name', $settings->company_name) }}" required data-rules="required|maxLen:150" data-bs-preview="company">
                        </label>
                        <label class="lei-bs-field">
                            <span>Legal Entity Name</span>
                            <input type="text" name="legal_name" value="{{ old('legal_name', $settings->legal_name) }}">
                        </label>
                        <label class="lei-bs-field">
                            <span>Portal Title (Sidebar &amp; Tab)</span>
                            <input type="text" name="portal_title" value="{{ old('portal_title', $settings->portal_title) }}" data-bs-preview="portal">
                        </label>
                        <label class="lei-bs-field">
                            <span>Sidebar Tagline</span>
                            <input type="text" name="tagline" value="{{ old('tagline', $settings->tagline) }}" data-bs-preview="tagline">
                        </label>
                        <label class="lei-bs-field">
                            <span>Breadcrumb Root Label</span>
                            <input type="text" name="breadcrumb_root" value="{{ old('breadcrumb_root', $settings->breadcrumb_root) }}" data-bs-preview="breadcrumb">
                        </label>
                        <label class="lei-bs-field lei-bs-field--full">
                            <span>Registry Authority</span>
                            <input type="text" name="registry_authority" value="{{ old('registry_authority', $settings->registry_authority) }}">
                        </label>
                        <label class="lei-bs-field lei-bs-field--full">
                            <span>Meta Description</span>
                            <textarea name="meta_description" rows="2">{{ old('meta_description', $settings->meta_description) }}</textarea>
                        </label>
                    </div>
                </section>

                <section class="lei-bs-card">
                    <h3>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        Logos & Icons
                    </h3>
                    <div class="lei-bs-upload-grid">
                        <div class="lei-bs-upload">
                            <span class="lei-bs-upload-label">Main Logo</span>
                            <div class="lei-bs-upload-preview" id="leiBsLogoPreview">
                                <img src="{{ $settings->logoUrl() }}" alt="Logo">
                            </div>
                            <input type="file" name="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml" data-bs-file-preview="leiBsLogoPreview">
                            @if ($settings->logo_path)
                                <label class="lei-bs-remove"><input type="checkbox" name="remove_logo" value="1"> Remove custom logo</label>
                            @endif
                            <small>PNG, JPG, WebP or SVG · max 2MB</small>
                        </div>
                        <div class="lei-bs-upload">
                            <span class="lei-bs-upload-label">Sidebar Icon</span>
                            <div class="lei-bs-upload-preview lei-bs-upload-preview--icon" id="leiBsSidebarPreview">
                                <img src="{{ $settings->sidebarIconUrl() }}" alt="Icon">
                            </div>
                            <input type="file" name="sidebar_icon" accept="image/png,image/jpeg,image/webp,image/svg+xml" data-bs-file-preview="leiBsSidebarPreview">
                            @if ($settings->sidebar_icon_path)
                                <label class="lei-bs-remove"><input type="checkbox" name="remove_sidebar_icon" value="1"> Remove icon</label>
                            @endif
                            <small>Square icon · max 1MB</small>
                        </div>
                        <div class="lei-bs-upload">
                            <span class="lei-bs-upload-label">Favicon</span>
                            <div class="lei-bs-upload-preview lei-bs-upload-preview--favicon" id="leiBsFaviconPreview">
                                @if ($settings->faviconUrl())
                                    <img src="{{ $settings->faviconUrl() }}" alt="Favicon">
                                @else
                                    <span class="lei-bs-favicon-placeholder">32×32</span>
                                @endif
                            </div>
                            <input type="file" name="favicon" accept="image/png,image/jpeg,image/x-icon,image/webp" data-bs-file-preview="leiBsFaviconPreview">
                            @if ($settings->favicon_path)
                                <label class="lei-bs-remove"><input type="checkbox" name="remove_favicon" value="1"> Remove favicon</label>
                            @endif
                            <small>ICO or PNG · max 512KB</small>
                        </div>
                    </div>
                </section>

                <section class="lei-bs-card">
                    <h3>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4"/></svg>
                        Theme Colors
                    </h3>
                    <div class="lei-bs-color-grid">
                        <label class="lei-bs-color">
                            <span>Primary</span>
                            <input type="color" name="primary_color" value="{{ old('primary_color', $settings->primary_color) }}" data-bs-color="primary">
                            <input type="text" class="lei-bs-color-text" value="{{ old('primary_color', $settings->primary_color) }}" readonly>
                        </label>
                        <label class="lei-bs-color">
                            <span>Accent / Gold</span>
                            <input type="color" name="accent_color" value="{{ old('accent_color', $settings->accent_color) }}" data-bs-color="accent">
                            <input type="text" class="lei-bs-color-text" value="{{ old('accent_color', $settings->accent_color) }}" readonly>
                        </label>
                        <label class="lei-bs-color">
                            <span>Sidebar</span>
                            <input type="color" name="sidebar_color" value="{{ old('sidebar_color', $settings->sidebar_color) }}" data-bs-color="sidebar">
                            <input type="text" class="lei-bs-color-text" value="{{ old('sidebar_color', $settings->sidebar_color) }}" readonly>
                        </label>
                    </div>
                </section>

                <section class="lei-bs-card">
                    <h3>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        Contact & Address
                    </h3>
                    <div class="lei-bs-grid lei-bs-grid--2">
                        <label class="lei-bs-field">
                            <span>Support Email</span>
                            <input type="email" name="support_email" value="{{ old('support_email', $settings->support_email) }}" data-rules="email|maxLen:150">
                        </label>
                        <label class="lei-bs-field">
                            <span>Support Phone</span>
                            <input type="tel" name="support_phone" value="{{ old('support_phone', $settings->support_phone) }}" placeholder="10-digit mobile number" data-type="phone" data-rules="phone">
                        </label>
                        <label class="lei-bs-field lei-bs-field--full">
                            <span>Street Address</span>
                            <input type="text" name="address_line" value="{{ old('address_line', $settings->address_line) }}">
                        </label>
                        <label class="lei-bs-field">
                            <span>City</span>
                            <input type="text" name="city" value="{{ old('city', $settings->city) }}">
                        </label>
                        <label class="lei-bs-field">
                            <span>State</span>
                            <input type="text" name="state" value="{{ old('state', $settings->state) }}">
                        </label>
                        <label class="lei-bs-field">
                            <span>Country</span>
                            <input type="text" name="country" value="{{ old('country', $settings->country) }}">
                        </label>
                        <label class="lei-bs-field">
                            <span>Postal Code</span>
                            <input type="text" name="postal_code" value="{{ old('postal_code', $settings->postal_code) }}">
                        </label>
                    </div>
                </section>

                <section class="lei-bs-card">
                    <h3>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        Regional & Currency
                    </h3>
                    <div class="lei-bs-grid lei-bs-grid--2">
                        <label class="lei-bs-field">
                            <span>Timezone</span>
                            <select name="timezone">
                                @foreach ($timezones as $key => $label)
                                    <option value="{{ $key }}" @selected(old('timezone', $settings->timezone) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="lei-bs-field">
                            <span>Locale</span>
                            <select name="locale">
                                @foreach ($locales as $key => $label)
                                    <option value="{{ $key }}" @selected(old('locale', $settings->locale) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="lei-bs-field">
                            <span>Date Format</span>
                            <select name="date_format">
                                @foreach ($dateFormats as $key => $label)
                                    <option value="{{ $key }}" @selected(old('date_format', $settings->date_format) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="lei-bs-field">
                            <span>Currency Code</span>
                            <input type="text" name="currency_code" value="{{ old('currency_code', $settings->currency_code) }}">
                        </label>
                        <label class="lei-bs-field">
                            <span>Currency Symbol</span>
                            <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $settings->currency_symbol) }}">
                        </label>
                        <label class="lei-bs-field">
                            <span>Renewal window (days)</span>
                            <input type="number" min="0" max="365" name="renewal_window_days" value="{{ old('renewal_window_days', $settings->renewal_window_days ?? 90) }}">
                            <small style="color:#64748b;">Days before LEI expiry when renewal plans become available. Use 0 for after expiry only.</small>
                        </label>
                    </div>
                </section>

                <section class="lei-bs-card">
                    <h3>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        Web & Social
                    </h3>
                    <div class="lei-bs-grid lei-bs-grid--2">
                        <label class="lei-bs-field lei-bs-field--full">
                            <span>Website URL</span>
                            <input type="url" name="website_url" value="{{ old('website_url', $settings->website_url) }}" data-rules="url|maxLen:255">
                        </label>
                        <label class="lei-bs-field">
                            <span>LinkedIn</span>
                            <input type="url" name="linkedin_url" value="{{ old('linkedin_url', $settings->linkedin_url) }}" data-rules="url|maxLen:255">
                        </label>
                        <label class="lei-bs-field">
                            <span>Twitter / X</span>
                            <input type="url" name="twitter_url" value="{{ old('twitter_url', $settings->twitter_url) }}" data-rules="url|maxLen:255">
                        </label>
                        <label class="lei-bs-field lei-bs-field--full">
                            <span>Copyright Footer Text</span>
                            <input type="text" name="copyright_text" value="{{ old('copyright_text', $settings->copyright_text) }}">
                        </label>
                    </div>
                </section>

                <section class="lei-bs-card">
                    <h3>Maintenance Banner</h3>
                    <label class="lei-bs-check">
                        <input type="checkbox" name="show_maintenance_banner" value="1" @checked(old('show_maintenance_banner', $settings->show_maintenance_banner))>
                        <span>Show maintenance notice on login page</span>
                    </label>
                    <label class="lei-bs-field lei-bs-field--full">
                        <span>Banner Message</span>
                        <input type="text" name="maintenance_message" value="{{ old('maintenance_message', $settings->maintenance_message) }}" placeholder="Scheduled maintenance on...">
                    </label>
                </section>

                <div class="lei-bs-form-footer">
                    <button type="submit" class="lei-bs-btn-save">Save Business Settings</button>
                </div>
            </div>

            <aside class="lei-bs-preview-panel">
                <h4>Live Preview</h4>
                <div class="lei-bs-preview-sidebar" id="leiBsPreviewSidebar" style="background-color: {{ $settings->sidebar_color }}">
                    <div class="lei-bs-preview-brand">
                        <img src="{{ $settings->sidebarIconUrl() }}" alt="" id="leiBsPreviewIcon">
                        <div>
                            <strong id="leiBsPreviewCompany">{{ $settings->company_name }}</strong>
                            <span id="leiBsPreviewTagline">{{ $settings->tagline }}</span>
                        </div>
                    </div>
                    <div class="lei-bs-preview-nav-item" style="background: #ffffff; color: {{ $settings->sidebar_color }}">Dashboard</div>
                </div>
                <div class="lei-bs-preview-login">
                    <img src="{{ $settings->logoUrl() }}" alt="" id="leiBsPreviewLogo">
                    <strong id="leiBsPreviewPortal">{{ $settings->portal_title }}</strong>
                    <span>{{ $settings->registry_authority }}</span>
                </div>
                <div class="lei-bs-preview-topbar" id="leiBsPreviewTopbar">
                    <div class="lei-bs-preview-topbar-row">
                        <div class="lei-bs-preview-topbar-left">
                            <p class="lei-bs-preview-welcome">
                                <span id="leiBsPreviewPrefix">{{ $settings->welcome_prefix }}</span>
                                <strong id="leiBsPreviewName" style="color: {{ $settings->primary_color }}">Admin</strong>
                            </p>
                            <p class="lei-bs-preview-sub" id="leiBsPreviewSubtitle">{{ $settings->headerSubtitleText() }}</p>
                        </div>
                        <input type="text" readonly id="leiBsPreviewSearch" class="lei-bs-preview-search" placeholder="{{ $settings->search_placeholder }}">
                        <span class="lei-bs-preview-bell" id="leiBsPreviewBell" @if(!$settings->header_show_notifications) hidden @endif>🔔</span>
                    </div>
                </div>
                <div class="lei-bs-preview-crumb">
                    <span id="leiBsPreviewBreadcrumb">{{ $settings->breadcrumb_root }}</span>
                    <span> / Business Settings</span>
                </div>
                <p class="lei-bs-preview-hint">Save to apply sidebar, header, login, and breadcrumbs across the portal.</p>
            </aside>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/lei-business-settings.js') }}?v=2"></script>
@endpush

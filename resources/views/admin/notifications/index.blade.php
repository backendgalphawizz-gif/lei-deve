@extends('admin.layouts.app')

@section('title', 'Notification Management')
@section('body_class', 'lei-page-nm')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-notification-mgmt.css') }}?v=3">
@endpush

@section('content')
<div class="lei-nm-page"
     data-channel-url="{{ route('admin.notifications.channel') }}"
     data-template-url="{{ route('admin.notifications.templates.store') }}"
     data-draft-url="{{ route('admin.notifications.broadcast.draft') }}"
     data-broadcast-url="{{ route('admin.notifications.broadcast.execute') }}"
     data-otp-url="{{ route('admin.notifications.otp') }}"
     data-trigger-url="{{ rtrim(config('app.url'), '/') }}/admin/notifications/triggers/__ID__/toggle">

    <div id="leiNmToast" class="lei-nm-toast" hidden></div>

    @if (!$config)
        <div class="lei-nm-empty">Run <code>php artisan db:seed --class=NotificationManagementSeeder</code></div>
    @else

    <div class="lei-nm-page-head">
        <div>
            <h1>Notification Management</h1>
            <p>Control and monitor administrative communication systems.</p>
        </div>
        <button type="button" class="lei-nm-btn-broadcast" id="leiNmSystemBroadcast">+ System Broadcast</button>
    </div>

    <div class="lei-nm-stats-row" id="leiNmStatsRow">
        @foreach ($statCards as $stat)
            <div class="lei-nm-stat-card" data-stat-key="{{ $stat->stat_key }}">
                <div class="lei-nm-stat-icon lei-nm-stat-icon--{{ $stat->icon_tone }}">
                    @if ($stat->icon_tone === 'blue')
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    @elseif ($stat->icon_tone === 'orange')
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    @elseif ($stat->icon_tone === 'purple')
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    @else
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    @endif
                </div>
                <div class="lei-nm-stat-body">
                    <span>{{ $stat->label }}</span>
                    <strong>{{ $stat->value }}</strong>
                </div>
            </div>
        @endforeach
    </div>

    <div class="lei-nm-workspace">
        <div class="lei-nm-main-col">
            <div class="lei-nm-card">
                <div class="lei-nm-card-head">
                    <div class="lei-nm-card-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                        <h2>Template Manager</h2>
                    </div>
                    <div class="lei-nm-channel-toggle">
                        <button type="button" class="lei-nm-channel-btn {{ $activeChannel === 'email' ? 'lei-nm-channel-btn--active' : '' }}" data-channel="email">Email</button>
                        <button type="button" class="lei-nm-channel-btn {{ $activeChannel === 'sms' ? 'lei-nm-channel-btn--active' : '' }}" data-channel="sms">SMS</button>
                    </div>
                </div>
                <div class="lei-nm-template-table">
                    <div class="lei-nm-tpl-row lei-nm-tpl-row--head">
                        <span>Template Name</span>
                        <span>Category</span>
                        <span>Status</span>
                        <span>Last Updated</span>
                        <span>Actions</span>
                    </div>
                    @foreach ($templates as $tpl)
                        <div class="lei-nm-tpl-row">
                            <span class="lei-nm-tpl-name">
                                <strong>{{ $tpl->name }}</strong>
                                <small>{{ $tpl->subtitle }}</small>
                            </span>
                            <span><span class="lei-nm-cat-pill">{{ $tpl->category }}</span></span>
                            <span><span class="lei-nm-status lei-nm-status--{{ $tpl->status }}"><i></i>{{ strtoupper($tpl->status) }}</span></span>
                            <span class="lei-nm-date">{{ $tpl->last_updated_label }}</span>
                            <span>
                                <button type="button" class="lei-nm-btn-edit" aria-label="Edit">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                            </span>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="lei-nm-btn-create" id="leiNmCreateTemplate">+ Create New Template</button>
            </div>

            <div class="lei-nm-card" id="leiNmBroadcastCard">
                <div class="lei-nm-card-head">
                    <div class="lei-nm-card-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4.9 19.1C1 12.5 1 12.5 4.9 5.9"/><path d="M19.1 5.9c4 6.6 4 6.6 0 13.2"/><line x1="12" y1="9" x2="12" y2="15"/><line x1="9" y1="12" x2="15" y2="12"/></svg>
                        <h2>Broadcast Center</h2>
                    </div>
                </div>
                <form id="leiNmBroadcastForm" class="lei-nm-broadcast-form">
                    <div class="lei-nm-broadcast-row">
                        <label>
                            <span>Channel</span>
                            <select name="broadcast_channel">
                                <option {{ $config->broadcast_channel === 'System-wide In-app' ? 'selected' : '' }}>System-wide In-app</option>
                                <option>Email</option>
                                <option>SMS</option>
                            </select>
                        </label>
                        <label>
                            <span>Audience</span>
                            <select name="broadcast_audience">
                                <option {{ $config->broadcast_audience === 'All Users' ? 'selected' : '' }}>All Users</option>
                                <option>Admins Only</option>
                                <option>Vendors</option>
                            </select>
                        </label>
                    </div>
                    <label class="lei-nm-msg-label">
                        <span>Message Content</span>
                        <textarea name="broadcast_message" rows="4" placeholder="Enter high-priority announcement text...">{{ $config->broadcast_message }}</textarea>
                    </label>
                    <p class="lei-nm-broadcast-hint">Scheduled for immediate release unless timestamped.</p>
                    <div class="lei-nm-broadcast-actions">
                        <button type="button" class="lei-nm-btn-outline" id="leiNmSaveDraft">Save as Draft</button>
                        <button type="button" class="lei-nm-btn-gold" id="leiNmExecuteBroadcast">Execute Broadcast</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="lei-nm-side-col">
            <div class="lei-nm-card lei-nm-card--compact">
                <h3>Active Triggers</h3>
                <div class="lei-nm-trigger-list">
                    @foreach ($triggers as $trigger)
                        <div class="lei-nm-trigger-item">
                            <span>{{ $trigger->name }}</span>
                            <label class="lei-nm-switch">
                                <input type="checkbox" class="js-nm-trigger" data-id="{{ $trigger->id }}" {{ $trigger->is_enabled ? 'checked' : '' }}>
                                <span class="lei-nm-switch-slider"></span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="lei-nm-card lei-nm-card--compact">
                <h3 class="lei-nm-code-title">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                    Placeholder Library
                </h3>
                <div class="lei-nm-placeholder-grid">
                    @foreach ($placeholders as $ph)
                        @php $phToken = '{{' . $ph->placeholder_key . '}}'; @endphp
                        <code class="lei-nm-ph-chip" data-copy="{{ $phToken }}">{{ $phToken }}</code>
                    @endforeach
                </div>
            </div>

            <div class="lei-nm-card lei-nm-card--compact">
                <h3 class="lei-nm-shield-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    OTP Configuration
                </h3>
                <form id="leiNmOtpForm" class="lei-nm-otp-form">
                    <div class="lei-nm-otp-fields">
                        <label class="lei-nm-otp-field">
                            <span class="lei-nm-otp-label">Code Length</span>
                            <div class="lei-nm-stepper">
                                <button type="button" class="js-otp-minus" data-field="otp_length">−</button>
                                <input type="number" name="otp_length" value="{{ $config->otp_length }}" min="4" max="8">
                                <button type="button" class="js-otp-plus" data-field="otp_length">+</button>
                            </div>
                        </label>
                        <label class="lei-nm-otp-field">
                            <span class="lei-nm-otp-label">Expiry (min)</span>
                            <input type="number" name="otp_expiry_min" value="{{ $config->otp_expiry_min }}" min="1" max="30">
                        </label>
                        <label class="lei-nm-otp-field">
                            <span class="lei-nm-otp-label">Retry Limit</span>
                            <input type="number" name="otp_retry_limit" value="{{ $config->otp_retry_limit }}" min="1" max="10">
                        </label>
                    </div>
                    <button type="submit" class="lei-nm-btn-outline lei-nm-btn-outline--full">UPDATE SECURITY POLICY</button>
                </form>
            </div>

            <div class="lei-nm-card lei-nm-card--compact">
                <div class="lei-nm-delivery-head">
                    <h3>Delivery Status</h3>
                    <span class="lei-nm-live"><i></i> LIVE</span>
                </div>
                <div class="lei-nm-delivery-list" id="leiNmDeliveryList">
                    @foreach ($deliveryLogs as $log)
                        <div class="lei-nm-delivery-item lei-nm-delivery-item--{{ $log->status }}">
                            <div class="lei-nm-delivery-icon">
                                @if ($log->status === 'delivered')
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                @elseif ($log->status === 'pending')
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                @else
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                @endif
                            </div>
                            <div>
                                <strong>{{ ucfirst($log->delivery_type) }} {{ ucfirst($log->status) }}</strong>
                                <span class="lei-nm-delivery-time">{{ $log->time_label }}</span>
                                <p>{{ $log->recipient }} · {{ $log->template_label }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @endif
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/lei-notification-mgmt.js') }}?v=1"></script>
@endpush

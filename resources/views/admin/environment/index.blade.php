@extends('admin.layouts.app')

@section('title', 'Environment Management')
@section('body_class', 'lei-page-environment')

@section('content')
<div class="lei-env-page"
     data-deploy-url="{{ route('admin.environment.deploy') }}"
     data-command-url="{{ route('admin.environment.command') }}"
     data-release-url="{{ rtrim(config('app.url'), '/') }}/admin/environment/releases/__ID__/action"
     data-export-url="{{ route('admin.environment.export') }}">

    <div id="leiEnvToast" class="lei-env-toast" hidden></div>

    <div class="lei-env-head">
        <div class="lei-env-head-text">
            <h1>Environment &amp; Deployment</h1>
            <p>Authoritative control node for systemic registry deployment and SLA monitoring.</p>
        </div>
        <div class="lei-env-head-actions">
            <a href="{{ route('admin.environment.export') }}" class="lei-env-btn-outline">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                History
            </a>
            <button type="button" class="lei-env-btn-primary" id="leiTriggerDeploy">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/></svg>
                Trigger Deployment
            </button>
        </div>
    </div>

    <div class="lei-env-cards-row">
        @foreach ($environments as $env)
            <div class="lei-env-status-card">
                <div class="lei-env-status-top">
                    <span class="lei-env-status-label">{{ $env->label }}</span>
                    <span class="lei-env-status-uptime lei-env-dot--{{ $env->status_tone }}">
                        <span class="lei-env-dot"></span>{{ $env->uptime_display }}
                    </span>
                </div>
                <div class="lei-env-status-version">{{ $env->version }}</div>
                <div class="lei-env-status-meta">{{ $env->deployed_meta }}</div>
                <div class="lei-env-status-footer lei-env-footer--{{ $env->footer_tone }}">
                    {{ $env->footer_label }} <strong>{{ $env->footer_value }}</strong>
                </div>
            </div>
        @endforeach
    </div>

    <div class="lei-env-workspace">
        <div class="lei-env-main-col">
            @if ($pipeline)
            <div class="lei-env-pipeline-card" id="leiPipelineCard">
                <div class="lei-env-pipeline-head">
                    <div class="lei-env-pipeline-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                        Active Pipeline: {{ $pipeline->build_number }}
                    </div>
                    <span class="lei-env-pipeline-target">Target: {{ $pipeline->target_environment }}</span>
                </div>
                <div class="lei-env-pipeline-steps">
                    @foreach ($pipeline->steps as $step)
                        <div class="lei-env-step lei-env-step--{{ $step['status'] }}">
                            <span class="lei-env-step-circle"></span>
                            <span class="lei-env-step-name">{{ $step['name'] }}</span>
                            <span class="lei-env-step-detail">{{ $step['detail'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="lei-env-pipeline-progress-wrap">
                    <div class="lei-env-pipeline-progress-label" id="leiProgressLabel">{{ $pipeline->progress_label }}</div>
                    <div class="lei-env-pipeline-bar">
                        <span class="lei-env-pipeline-fill" id="leiProgressFill" style="width: {{ $pipeline->progress_percent }}%"></span>
                    </div>
                    <span class="lei-env-pipeline-pct" id="leiProgressPct">{{ $pipeline->progress_percent }}%</span>
                </div>
            </div>
            @endif

            <div class="lei-env-history-card">
                <div class="lei-env-history-head">
                    <h2>Deployment History</h2>
                    <div class="lei-env-history-tools">
                        <button type="button" class="lei-env-icon-btn" title="Filter" data-prevent>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        </button>
                        <a href="{{ route('admin.environment.export') }}" class="lei-env-icon-btn" title="Download">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        </a>
                    </div>
                </div>
                <div class="lei-env-table-head">
                    <div class="lei-env-th">ENVIRONMENT</div>
                    <div class="lei-env-th">VERSION</div>
                    <div class="lei-env-th lei-env-th--admin">ADMINISTRATOR</div>
                    <div class="lei-env-th">TIMESTAMP</div>
                    <div class="lei-env-th lei-env-th--status">STATUS</div>
                </div>
                <div class="lei-env-table-body">
                    @foreach ($deployments as $dep)
                        <div class="lei-env-row">
                            <div class="lei-env-td">
                                <span class="lei-env-env-badge lei-env-env-badge--{{ $dep->environment_tone }}">{{ strtoupper($dep->environment) }}</span>
                            </div>
                            <div class="lei-env-td">{{ $dep->version }}</div>
                            <div class="lei-env-td lei-env-td--admin">
                                <span class="lei-env-avatar">{{ strtoupper(substr($dep->administrator, 0, 1)) }}</span>
                                {{ $dep->administrator }}
                                @if ($dep->auth_id)
                                    <span class="lei-env-auth-id">(Auth ID: {{ $dep->auth_id }})</span>
                                @endif
                            </div>
                            <div class="lei-env-td">{{ $dep->deployed_at->format('M d, H:i:s') }}</div>
                            <div class="lei-env-td">
                                <span class="lei-env-status-pill lei-env-status-pill--{{ $dep->status }}">
                                    <span class="lei-env-dot"></span>{{ $dep->status_label }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <aside class="lei-env-side-col">
            <div class="lei-env-console-card">
                <h2>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    Command Console
                </h2>
                <button type="button" class="lei-env-cmd-btn" data-cmd="force_sync">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                    Force Sync PROD
                </button>
                <button type="button" class="lei-env-cmd-btn lei-env-cmd-btn--warn" data-cmd="rollback">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                    Manual Rollback
                </button>
                <button type="button" class="lei-env-cmd-btn lei-env-cmd-btn--danger" data-cmd="lockout">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    EMERGENCY LOCKOUT
                </button>
            </div>

            <div class="lei-env-releases-card">
                <h2>Pending Releases</h2>
                <div class="lei-env-release-list" id="leiReleaseList">
                    @foreach ($releases as $release)
                        <div class="lei-env-release-item" data-release-id="{{ $release->id }}">
                            <div class="lei-env-release-top">
                                <strong>{{ $release->title }}</strong>
                                <span class="lei-env-release-badge lei-env-release-badge--{{ $release->badge }}">{{ strtoupper($release->badge) }}</span>
                            </div>
                            <p>{{ $release->description }}</p>
                            <div class="lei-env-release-actions">
                                @if ($release->badge === 'critical')
                                    <button type="button" class="lei-env-btn-approve" data-release-action="approve">APPROVE</button>
                                    <button type="button" class="lei-env-btn-schedule" data-release-action="schedule">SCHEDULE</button>
                                @else
                                    <button type="button" class="lei-env-btn-schedule lei-env-btn-schedule--solo" data-release-action="review">REVIEW NOTES</button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="lei-env-artifacts-card">
                <h2>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                    Artifact Registry
                </h2>
                <ul class="lei-env-artifact-list">
                    @foreach ($artifacts as $artifact)
                        <li>
                            <div class="lei-env-artifact-info">
                                <strong>{{ $artifact->filename }}</strong>
                                <span>{{ $artifact->version_label }} | {{ $artifact->size_display }}</span>
                            </div>
                            <button type="button" class="lei-env-menu-btn" aria-label="Options">⋯</button>
                        </li>
                    @endforeach
                </ul>
                <a href="#" class="lei-env-artifacts-link" data-prevent>VIEW ALL ARTIFACTS</a>
            </div>
        </aside>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-environment.css') }}?v=1">
@endpush

@push('scripts')
<script src="{{ asset('js/lei-environment.js') }}?v=1"></script>
@endpush

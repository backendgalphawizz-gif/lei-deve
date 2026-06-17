@extends('admin.layouts.app')

@section('title', 'Master Data')
@section('body_class', 'lei-page-master-data')

@section('content')
<div class="lei-md-page"
     data-validation-url="{{ route('admin.master-data.validation') }}"
     data-dropdown-url="{{ route('admin.master-data.dropdown') }}"
     data-country-url="{{ route('admin.master-data.countries.store') }}"
     data-export-url="{{ route('admin.master-data.export') }}">

    <div id="leiMdToast" class="lei-md-toast" hidden></div>

    <nav class="lei-md-tabs">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('admin.master-data.index', ['tab' => $key]) }}"
               class="lei-md-tab {{ $activeTab === $key ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </nav>

    @if ($activeTab === 'country')
    <div class="lei-md-workspace">
        <div class="lei-md-main">
            <div class="lei-md-head">
                <div>
                    <h1>Country Registry Master</h1>
                    <p>Manage global geographical data and ISO-standard compliant regional codes.</p>
                </div>
                <div class="lei-md-head-actions">
                    <a href="{{ route('admin.master-data.export') }}" class="lei-md-btn-outline">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export
                    </a>
                    <button type="button" class="lei-md-btn-primary" id="leiMdAddCountry">+ Add New Country</button>
                </div>
            </div>

            <div class="lei-md-table-card">
                <div class="lei-md-table-toolbar">
                    <div class="lei-md-badges">
                        <span class="lei-md-badge-dark">REGISTRY RECORDS</span>
                        <span class="lei-md-badge-light">TOTAL: {{ $totalCountries }}</span>
                    </div>
                    <form method="GET" action="{{ route('admin.master-data.index') }}" class="lei-md-per-page">
                        <input type="hidden" name="tab" value="country">
                        <label>Items per page:</label>
                        <select name="per_page" onchange="this.form.submit()">
                            @foreach ([5, 10, 15, 25, 50] as $n)
                                <option value="{{ $n }}" @selected(request('per_page', 15) == $n)>{{ $n }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="lei-md-table-head">
                    <div class="lei-md-th lei-md-th--name">COUNTRY NAME</div>
                    <div class="lei-md-th">ISO ALPHA-2</div>
                    <div class="lei-md-th">REGION</div>
                    <div class="lei-md-th">STATUS</div>
                    <div class="lei-md-th">DIALING CODE</div>
                    <div class="lei-md-th lei-md-th--action">ACTIONS</div>
                </div>
                <div class="lei-md-table-body">
                    @forelse ($countries as $country)
                        <div class="lei-md-row">
                            <div class="lei-md-td lei-md-td--name"><strong>{{ $country->name }}</strong></div>
                            <div class="lei-md-td">{{ $country->iso_alpha2 }}</div>
                            <div class="lei-md-td">{{ $country->region }}</div>
                            <div class="lei-md-td">
                                <span class="lei-md-status lei-md-status--{{ $country->status }}">{{ $country->status_label }}</span>
                            </div>
                            <div class="lei-md-td">{{ $country->dialing_code }}</div>
                            <div class="lei-md-td lei-md-td--action">
                                <button type="button" class="lei-md-action-btn" aria-label="Actions">⋯</button>
                            </div>
                        </div>
                    @empty
                        <div class="lei-md-empty">No countries found. Run <code>php artisan db:seed --class=MasterDataSeeder</code>.</div>
                    @endforelse
                </div>
                @if ($countries && $countries->hasPages())
                <div class="lei-md-table-footer">
                    <span>Showing {{ $countries->firstItem() }} to {{ $countries->lastItem() }} of {{ $countries->total() }} records</span>
                    <div class="lei-md-pager">
                        @if ($countries->onFirstPage())
                            <span class="disabled">Previous</span>
                        @else
                            <a href="{{ $countries->previousPageUrl() }}">Previous</a>
                        @endif
                        @for ($p = max(1, $countries->currentPage() - 1); $p <= min($countries->lastPage(), $countries->currentPage() + 2); $p++)
                            @if ($p == $countries->currentPage())
                                <span class="active">{{ $p }}</span>
                            @else
                                <a href="{{ $countries->url($p) }}">{{ $p }}</a>
                            @endif
                        @endfor
                        @if ($countries->currentPage() < $countries->lastPage() - 2)
                            <span class="dots">...</span>
                            <a href="{{ $countries->url($countries->lastPage()) }}">{{ $countries->lastPage() }}</a>
                        @endif
                        @if ($countries->hasMorePages())
                            <a href="{{ $countries->nextPageUrl() }}">Next</a>
                        @else
                            <span class="disabled">Next</span>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        <aside class="lei-md-side">
            <div class="lei-md-panel lei-md-panel--validation">
                <div class="lei-md-panel-head">
                    <span class="lei-md-panel-icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    </span>
                    <h2>Validation Mapping</h2>
                </div>
                <p class="lei-md-panel-desc">Configure data integrity rules for the &apos;Country&apos; entity across modules.</p>
                <div class="lei-md-checklist" id="leiMdValidation">
                    <label class="lei-md-check-card">
                        <input type="checkbox" name="kyc_verification" {{ ($validation['kyc_verification'] ?? false) ? 'checked' : '' }}>
                        <span class="lei-md-check-card-body">
                            <strong>KYC Verification</strong>
                            <small>MANDATORY CHECK FOR ONBOARDING</small>
                        </span>
                    </label>
                    <label class="lei-md-check-card">
                        <input type="checkbox" name="tax_residency_proof" {{ ($validation['tax_residency_proof'] ?? false) ? 'checked' : '' }}>
                        <span class="lei-md-check-card-body">
                            <strong>Tax Residency Proof</strong>
                            <small>REQUIRED FOR TAX MASTER LINK</small>
                        </span>
                    </label>
                    <label class="lei-md-check-card">
                        <input type="checkbox" name="swift_bic_validation" {{ ($validation['swift_bic_validation'] ?? false) ? 'checked' : '' }}>
                        <span class="lei-md-check-card-body">
                            <strong>Swift/BIC Validation</strong>
                            <small>PAYMENT GATEWAY HANDOFF</small>
                        </span>
                    </label>
                </div>
                <button type="button" class="lei-md-btn-mapping" id="leiMdUpdateMapping">Update Mapping</button>
            </div>

            <div class="lei-md-panel lei-md-panel--dropdown">
                <div class="lei-md-panel-head">
                    <span class="lei-md-panel-icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                    </span>
                    <h2>Dropdown Settings</h2>
                </div>
                <div class="lei-md-field">
                    <label>DISPLAY FORMAT</label>
                    <select id="leiMdDisplayFormat">
                        <option value="name_iso" @selected(($dropdown['display_format'] ?? '') === 'name_iso')>Country Name (ISO)</option>
                        <option value="name_only" @selected(($dropdown['display_format'] ?? '') === 'name_only')>Country Name Only</option>
                        <option value="iso_only" @selected(($dropdown['display_format'] ?? '') === 'iso_only')>ISO Code Only</option>
                    </select>
                </div>
                <div class="lei-md-field">
                    <label>SORT ORDER</label>
                    <select id="leiMdSortOrder">
                        <option value="alpha_asc" @selected(($dropdown['sort_order'] ?? '') === 'alpha_asc')>Alphabetical (A-Z)</option>
                        <option value="alpha_desc" @selected(($dropdown['sort_order'] ?? '') === 'alpha_desc')>Alphabetical (Z-A)</option>
                        <option value="region" @selected(($dropdown['sort_order'] ?? '') === 'region')>By Region</option>
                    </select>
                </div>
                <div class="lei-md-toggle-card">
                    <div class="lei-md-toggle-row">
                        <div>
                            <strong>Allow Custom Entries</strong>
                            <small>Enabling this allows users to suggest new countries during data entry.</small>
                        </div>
                        <label class="lei-md-switch">
                            <input type="checkbox" id="leiMdAllowCustom" {{ ($dropdown['allow_custom_entries'] ?? false) ? 'checked' : '' }}>
                            <span class="lei-md-switch-track"></span>
                        </label>
                    </div>
                </div>
                <button type="button" class="lei-md-btn-mapping" id="leiMdSaveDropdown">Save Settings</button>
            </div>

            <div class="lei-md-note">
                <strong>Administrative Note</strong>
                <p>Ensure ISO codes strictly follow ISO 3166-1 standards to prevent synchronization errors with the global payment engine.</p>
            </div>
        </aside>
    </div>
    @else
    <div class="lei-md-placeholder">
        <h2>{{ $tabs[$activeTab] }}</h2>
        <p>This master data module is scheduled for the next registry release.</p>
        <a href="{{ route('admin.master-data.index', ['tab' => 'country']) }}" class="lei-md-btn-primary">Go to Country Master</a>
    </div>
    @endif
</div>

<div class="lei-md-modal" id="leiMdCountryModal" hidden>
    <div class="lei-md-modal-backdrop" data-close-md></div>
    <div class="lei-md-modal-box">
        <h3>Add New Country</h3>
        <form id="leiMdCountryForm" class="lei-md-form">
            <div class="lei-md-field">
                <label>Country Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="lei-md-field">
                <label>ISO Alpha-2</label>
                <input type="text" name="iso_alpha2" maxlength="2" required>
            </div>
            <div class="lei-md-field">
                <label>Region</label>
                <input type="text" name="region" required>
            </div>
            <div class="lei-md-field">
                <label>Status</label>
                <select name="status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="lei-md-field">
                <label>Dialing Code</label>
                <input type="text" name="dialing_code" placeholder="+1" required>
            </div>
            <div class="lei-md-modal-actions">
                <button type="button" class="lei-md-btn-outline" data-close-md>Cancel</button>
                <button type="submit" class="lei-md-btn-primary">Save Country</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-master-data.css') }}?v=2">
@endpush

@push('scripts')
<script src="{{ asset('js/lei-master-data.js') }}?v=1"></script>
@endpush

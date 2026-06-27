@extends('admin.layouts.app')

@section('title', 'Subscription Management')
@section('body_class', 'lei-page-website-admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/lei-website-admin.css') }}?v=3">
@endpush

@section('breadcrumbs')
    @include('admin.partials.breadcrumbs', ['current' => 'Subscription Management'])
@endsection

@section('content')
<div class="lei-wm-page">
    <div class="lei-wm-head">
        <div>
            <h2>Subscription Management</h2>
            <p>Manage pricing plans on the public website and applicant portal. Customer subscriptions sync to the portal automatically.</p>
        </div>
        <div class="lei-wm-head-actions">
            <a href="{{ route('pricing') }}" target="_blank" class="lei-wm-btn-ghost">View Pricing Page</a>
            @if ($tab === 'plans')
                <a href="{{ route('admin.pricing-plans.create') }}" class="lei-wm-btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Plan
                </a>
            @else
                <a href="{{ route('admin.subscriptions.create') }}" class="lei-wm-btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Subscription
                </a>
            @endif
        </div>
    </div>

    <div class="lei-wm-stats">
        <div class="lei-wm-stat"><span>Active Plans</span><strong>{{ $stats['plans'] }}</strong></div>
        <div class="lei-wm-stat"><span>Total Subscriptions</span><strong>{{ $stats['total'] }}</strong></div>
        <div class="lei-wm-stat lei-wm-stat--ok"><span>Active</span><strong>{{ $stats['active'] }}</strong></div>
        <div class="lei-wm-stat"><span>Paid Revenue</span><strong>{{ \App\Support\CurrencyFormatter::format((float) $stats['revenue'], 0) }}</strong></div>
    </div>

    <div class="lei-wm-tabs">
        <a href="{{ route('admin.subscriptions.index', ['tab' => 'plans']) }}"
           class="lei-wm-tab {{ $tab === 'plans' ? 'is-active' : '' }}">Pricing Plans</a>
        <a href="{{ route('admin.subscriptions.index', ['tab' => 'subscriptions']) }}"
           class="lei-wm-tab {{ $tab === 'subscriptions' ? 'is-active' : '' }}">Customer Subscriptions</a>
    </div>

    @if ($tab === 'plans')
        <div class="lei-wm-table-card">
            <div class="lei-wm-table lei-wm-table--plans">
                <div class="lei-wm-table-row lei-wm-table-row--head">
                    <div class="lei-wm-td">Plan</div>
                    <div class="lei-wm-td">Section</div>
                    <div class="lei-wm-td">Price</div>
                    <div class="lei-wm-td">Duration</div>
                    <div class="lei-wm-td">Status</div>
                    <div class="lei-wm-td">Actions</div>
                </div>
                @forelse ($pricingPlans as $row)
                    <div class="lei-wm-table-row">
                        <div class="lei-wm-td">
                            <strong>{{ $row->name }}</strong>
                            @if ($row->label)<small>{{ $row->label }}</small>@endif
                        </div>
                        <div class="lei-wm-td">{{ ucfirst($row->section) }}</div>
                        <div class="lei-wm-td">{{ $row->formattedPrice() }}</div>
                        <div class="lei-wm-td">{{ $row->duration_years }} yr</div>
                        <div class="lei-wm-td">
                            <span class="lei-wm-badge {{ $row->is_active ? 'lei-wm-badge--active' : 'lei-wm-badge--pending' }}">
                                {{ $row->is_active ? 'Live' : 'Hidden' }}
                            </span>
                        </div>
                        <div class="lei-wm-td lei-wm-td--actions">
                            <a href="{{ route('admin.pricing-plans.edit', $row) }}" class="lei-wm-action">Edit</a>
                            <form method="POST" action="{{ route('admin.pricing-plans.destroy', $row) }}" onsubmit="return confirm('Delete this pricing plan?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="lei-wm-action lei-wm-action--delete">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="lei-wm-empty">No pricing plans yet. <a href="{{ route('admin.pricing-plans.create') }}">Add your first plan</a></div>
                @endforelse
            </div>
            @if ($pricingPlans->hasPages())
                <div class="lei-wm-table-footer">
                    <span>Showing {{ $pricingPlans->firstItem() }}–{{ $pricingPlans->lastItem() }} of {{ $pricingPlans->total() }}</span>
                    <div class="lei-wm-pagination">{{ $pricingPlans->links() }}</div>
                </div>
            @endif
        </div>
    @else
        <div class="lei-wm-toolbar-card">
            <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="lei-wm-toolbar">
                <input type="hidden" name="tab" value="subscriptions">
                <div class="lei-wm-search">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search reference, plan, customer...">
                </div>
                <label>Status
                    <select name="status" onchange="this.form.submit()">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $key => $label)
                            <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Payment
                    <select name="payment" onchange="this.form.submit()">
                        <option value="">All payments</option>
                        @foreach ($paymentStatuses as $key => $label)
                            <option value="{{ $key }}" @selected(request('payment') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <button type="submit" class="lei-wm-btn-secondary">Search</button>
            </form>
        </div>

        <div class="lei-wm-table-card">
            <div class="lei-wm-table lei-wm-table--subs">
                <div class="lei-wm-table-row lei-wm-table-row--head">
                    <div class="lei-wm-td">Reference</div>
                    <div class="lei-wm-td">Customer</div>
                    <div class="lei-wm-td">Plan</div>
                    <div class="lei-wm-td">Status</div>
                    <div class="lei-wm-td">Payment</div>
                    <div class="lei-wm-td">Amount</div>
                    <div class="lei-wm-td">Actions</div>
                </div>
                @forelse ($subscriptions as $row)
                    <div class="lei-wm-table-row">
                        <div class="lei-wm-td"><strong>{{ $row->reference }}</strong></div>
                        <div class="lei-wm-td">
                            <strong>{{ $row->user?->name }}</strong>
                            <small>{{ $row->user?->email }}</small>
                        </div>
                        <div class="lei-wm-td">{{ $row->plan_name }}</div>
                        <div class="lei-wm-td"><span class="lei-wm-badge lei-wm-badge--{{ $row->status }}">{{ $row->statusLabel() }}</span></div>
                        <div class="lei-wm-td"><span class="lei-wm-badge lei-wm-badge--{{ $row->payment_status }}">{{ $row->paymentStatusLabel() }}</span></div>
                        <div class="lei-wm-td">{{ $row->formattedAmount() }}</div>
                        <div class="lei-wm-td lei-wm-td--actions">
                            <a href="{{ route('admin.subscriptions.edit', $row) }}" class="lei-wm-action">Edit</a>
                        </div>
                    </div>
                @empty
                    <div class="lei-wm-empty">No customer subscriptions yet. <a href="{{ route('admin.subscriptions.create') }}">Add a subscription</a> or wait for public purchases.</div>
                @endforelse
            </div>
            @if ($subscriptions->hasPages())
                <div class="lei-wm-table-footer">
                    <span>Showing {{ $subscriptions->firstItem() }}–{{ $subscriptions->lastItem() }} of {{ $subscriptions->total() }}</span>
                    <div class="lei-wm-pagination">{{ $subscriptions->links() }}</div>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection

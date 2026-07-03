@extends('public.layouts.app')

@section('title', $record['lei_number'].' — '.$record['entity_name'])
@section('body_class', 'lei-page-registry-detail')

@section('content')
<section class="lei-reg-hero lei-reg-hero--detail">
    <div class="lei-pub-container">
        <a href="{{ route('registry.search', ['q' => $record['entity_name'], 'type' => 'company']) }}" class="lei-reg-back">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to search
        </a>
        <span class="lei-pub-eyebrow">LEI RECORD · GLEIF GLOBAL INDEX</span>
        <h1>{{ $record['entity_name'] }}</h1>
        <div class="lei-reg-lei-badge">{{ $record['lei_number'] }}</div>
        <span class="lei-reg-status lei-reg-status--{{ $record['status_tone'] }}" style="margin-top:12px;">{{ $record['status_label'] }}</span>
    </div>
</section>

<section class="lei-pub-section lei-pub-muted" style="padding-top:0;">
    <div class="lei-pub-container">
        @include('public.registry.partials.gleif-action-banner', ['record' => $record])
    </div>
</section>

<section class="lei-pub-section">
    <div class="lei-pub-container lei-reg-detail-grid">
        <div class="lei-reg-detail-card">
            <h2>Entity Information</h2>
            <dl class="lei-reg-dl">
                <div><dt>Legal Entity Name</dt><dd>{{ $record['entity_name'] }}</dd></div>
                <div><dt>LEI Code</dt><dd class="lei-reg-mono">{{ $record['lei_number'] }}</dd></div>
                <div><dt>Registration / CIN No.</dt><dd class="lei-reg-mono">{{ $record['registration_number'] ?? '—' }}</dd></div>
                <div><dt>Entity Type</dt><dd>{{ $record['entity_type'] ?? '—' }}</dd></div>
                <div><dt>Country</dt><dd>{{ $record['country'] }}</dd></div>
                @if ($record['registered_address'] ?? null)
                    <div><dt>Registered Address</dt><dd>{{ $record['registered_address'] }}</dd></div>
                @endif
            </dl>
        </div>
        <div class="lei-reg-detail-card">
            <h2>GLEIF Registration Status</h2>
            <dl class="lei-reg-dl">
                <div><dt>Data Source</dt><dd><span class="lei-reg-source lei-reg-source--gleif">GLEIF Global Index</span></dd></div>
                <div><dt>LEI Status</dt><dd><span class="lei-reg-status lei-reg-status--{{ $record['status_tone'] }}">{{ $record['status_label'] }}</span></dd></div>
                <div><dt>Entity Status</dt><dd>{{ $record['gleif_entity_status'] ?? '—' }}</dd></div>
                <div><dt>Registration Status</dt><dd>{{ $record['gleif_registration_status'] ?? '—' }}</dd></div>
                <div><dt>Next Renewal</dt><dd>{{ $record['expiry_label'] ?? '—' }}</dd></div>
                <div><dt>Initial Registration</dt><dd>{{ $record['initial_registration_date']?->format('F j, Y') ?? '—' }}</dd></div>
                @if ($record['managing_lou'] ?? null)
                    <div><dt>Managing LOU</dt><dd class="lei-reg-mono">{{ $record['managing_lou'] }}</dd></div>
                @endif
            </dl>
        </div>
    </div>
    <div class="lei-pub-container" style="margin-top:24px;">
        @include('public.registry.partials.search-form', ['query' => '', 'type' => 'all', 'compact' => true])
    </div>
</section>
@endsection

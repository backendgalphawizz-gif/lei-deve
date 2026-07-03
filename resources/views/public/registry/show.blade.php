@extends('public.layouts.app')

@php
    $record = $record ?? $registry->normalizeLocalRecord($application);
    $meta = $record;
    $regNo = $record['registration_number'] ?? $registry->registrationNumber($application);
    $entityType = $record['entity_type'] ?? $registry->entityType($application);
    $address = $record['registered_address'] ?? $registry->registeredAddress($application);
@endphp

@section('title', $record['lei_number'].' — '.$record['entity_name'])
@section('body_class', 'lei-page-registry-detail')

@section('content')
<section class="lei-reg-hero lei-reg-hero--detail">
    <div class="lei-pub-container">
        <a href="{{ route('registry.search', ['q' => $record['entity_name'], 'type' => 'company']) }}" class="lei-reg-back">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to search
        </a>
        <span class="lei-pub-eyebrow">LEI RECORD · OUR REGISTRY</span>
        <h1>{{ $record['entity_name'] }}</h1>
        <div class="lei-reg-lei-badge">{{ $record['lei_number'] }}</div>
        <span class="lei-reg-status lei-reg-status--{{ $meta['status_tone'] }}" style="margin-top:12px;">{{ $meta['status_label'] }}</span>
    </div>
</section>

<section class="lei-pub-section lei-pub-muted" style="padding-top:0;">
    <div class="lei-pub-container">
        @include('public.registry.partials.action-banner', ['application' => $application, 'record' => $record])
    </div>
</section>

<section class="lei-pub-section">
    <div class="lei-pub-container lei-reg-detail-grid">
        <div class="lei-reg-detail-card">
            <h2>Entity Information</h2>
            <dl class="lei-reg-dl">
                <div><dt>Legal Entity Name</dt><dd>{{ $record['entity_name'] }}</dd></div>
                <div><dt>LEI Code</dt><dd class="lei-reg-mono">{{ $record['lei_number'] }}</dd></div>
                <div><dt>Registration / CIN No.</dt><dd class="lei-reg-mono">{{ $regNo ?? '—' }}</dd></div>
                <div><dt>Entity Type</dt><dd>{{ $entityType ?? '—' }}</dd></div>
                <div><dt>Country</dt><dd>{{ $record['country'] }}</dd></div>
                @if ($address)
                    <div><dt>Registered Address</dt><dd>{{ $address }}</dd></div>
                @endif
            </dl>
        </div>
        <div class="lei-reg-detail-card">
            <h2>Registration Status</h2>
            <dl class="lei-reg-dl">
                <div><dt>Data Source</dt><dd><span class="lei-reg-source lei-reg-source--local">Our Registry</span></dd></div>
                <div><dt>LEI Status</dt><dd><span class="lei-reg-status lei-reg-status--{{ $meta['status_tone'] }}">{{ $meta['status_label'] }}</span></dd></div>
                <div><dt>Registration Status</dt><dd><span class="lei-reg-status lei-reg-status--active">PUBLISHED</span></dd></div>
                <div><dt>Valid Until</dt><dd>{{ $application->expiry_date?->format('F j, Y') ?? '—' }}</dd></div>
                <div><dt>Application Ref</dt><dd class="lei-reg-mono">{{ $application->application_code }}</dd></div>
                <div><dt>Issued On</dt><dd>{{ $application->submitted_on?->format('F j, Y') ?? '—' }}</dd></div>
                @if ($application->certificate?->isSigned())
                    <div><dt>Certificate</dt><dd>Digitally signed (ISO 17442-2)</dd></div>
                @endif
            </dl>
        </div>
    </div>
    <div class="lei-pub-container" style="margin-top:24px;">
        @include('public.registry.partials.search-form', ['query' => '', 'type' => 'all', 'compact' => true])
    </div>
</section>
@endsection

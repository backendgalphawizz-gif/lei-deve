@extends('public.layouts.app')

@section('title', 'Home')

@section('content')
@php
    $hero = $sections->get('hero')?->content ?? [];
    $services = $sections->get('services')?->content ?? [];
    $workflow = $sections->get('workflow')?->content ?? [];
    $features = $sections->get('features')?->content ?? [];
@endphp

<section class="lei-pub-hero">
    <div class="lei-pub-container lei-pub-hero-grid">
        <div>
            <span class="lei-pub-eyebrow">{{ $sections->get('hero')?->subtitle ?? 'THE GLOBAL STANDARD' }}</span>
            <h1>{{ $sections->get('hero')?->title ?? 'What is an LEI?' }}</h1>
            <p class="lei-pub-lead">{{ $hero['description'] ?? '' }}</p>
            <div class="lei-pub-mini-cards">
                @foreach ($hero['cards'] ?? [] as $card)
                    <div class="lei-pub-mini-card">
                        <i class="fa-solid fa-{{ $card['icon'] === 'globe' ? 'globe' : 'shield-halved' }}"></i>
                        <h3>{{ $card['title'] }}</h3>
                        <p>{{ $card['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="lei-pub-quote-card">
            <h3>{{ $hero['quote_card']['title'] ?? 'Financial Transparency' }}</h3>
            <blockquote>"{{ $hero['quote_card']['quote'] ?? '' }}"</blockquote>
            <div class="lei-pub-quote-meta">
                <span class="lei-pub-quote-icon"><i class="fa-solid fa-shield"></i></span>
                <div>
                    <strong>{{ $hero['quote_card']['attribution'] ?? '' }}</strong>
                    <small>{{ $hero['quote_card']['role'] ?? '' }}</small>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="lei-pub-section">
    <div class="lei-pub-container">
        <div class="lei-pub-section-head">
            <h2>{{ $sections->get('services')?->title ?? 'Precision Registry Services' }}</h2>
            <p>{{ $sections->get('services')?->subtitle ?? '' }}</p>
        </div>
        <div class="lei-pub-cards-3">
            @foreach ($services['items'] ?? [] as $item)
                <article class="lei-pub-service-card">
                    <div class="lei-pub-service-icon"><i class="fa-solid fa-building-columns"></i></div>
                    <h3>{{ $item['title'] }}</h3>
                    <p>{{ $item['description'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="lei-pub-section lei-pub-navy">
    <div class="lei-pub-container">
        <h2 class="lei-pub-center">{{ $sections->get('workflow')?->title ?? 'Process Workflow' }}</h2>
        <div class="lei-pub-workflow">
            @foreach ($workflow['steps'] ?? [] as $step)
                <div class="lei-pub-workflow-step">
                    <div class="lei-pub-workflow-icon"><i class="fa-solid fa-circle-check"></i></div>
                    <h3>{{ $step['num'] }}. {{ $step['title'] }}</h3>
                    <p>{{ $step['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="lei-pub-section lei-pub-muted">
    <div class="lei-pub-container lei-pub-features-grid">
        <div class="lei-pub-feature-cards">
            @foreach ($features['items'] ?? [] as $item)
                <article class="lei-pub-feature-card">
                    <i class="fa-solid fa-star"></i>
                    <h3>{{ $item['title'] }}</h3>
                    <p>{{ $item['description'] }}</p>
                </article>
            @endforeach
        </div>
        <div>
            <h2>{{ $sections->get('features')?->title ?? 'Engineered for Operational Excellence' }}</h2>
            <p>{{ $sections->get('features')?->subtitle ?? '' }}</p>
            <ul class="lei-pub-checklist">
                @foreach ($features['checklist'] ?? [] as $line)
                    <li><i class="fa-solid fa-check"></i> {{ $line }}</li>
                @endforeach
            </ul>
            <a href="{{ url($features['link_url'] ?? '/pricing') }}" class="lei-pub-link">{{ $features['link_text'] ?? 'Explore Enterprise Features' }} →</a>
        </div>
    </div>
</section>
@endsection

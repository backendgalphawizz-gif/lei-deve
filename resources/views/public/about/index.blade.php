@extends('public.layouts.app')

@section('title', 'About Us')
@section('body_class', 'page-about')

@section('content')
@php
    $hero = $sections->get('hero')?->content ?? [];
    $integrity = $sections->get('integrity')?->content ?? [];
    $cta = $sections->get('cta')?->content ?? [];
@endphp

<section class="lei-pub-about-hero">
    <div class="lei-pub-container">
        <span class="lei-pub-eyebrow gold">{{ $sections->get('hero')?->subtitle }}</span>
        <h1>{{ $sections->get('hero')?->title }}</h1>
        <p>{{ $hero['description'] ?? '' }}</p>
    </div>
</section>

<section class="lei-pub-section">
    <div class="lei-pub-container lei-pub-about-grid">
        <div>
            <h2>{{ $sections->get('integrity')?->title }}</h2>
            @foreach ($integrity['paragraphs'] ?? [] as $p)
                <p>{{ $p }}</p>
            @endforeach
        </div>
        <div class="lei-pub-stats-grid">
            @foreach ($integrity['stats'] ?? [] as $stat)
                <div class="lei-pub-stat-card {{ $stat['style'] ?? 'light' }}">
                    <strong>{{ $stat['value'] }}</strong>
                    <span>{{ $stat['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="lei-pub-section lei-pub-muted">
    <div class="lei-pub-container lei-pub-mission-grid">
        <article>
            <div class="lei-pub-mission-icon navy"><i class="fa-solid fa-rocket"></i></div>
            <h3>{{ $sections->get('mission')?->title }}</h3>
            <p>{{ $sections->get('mission')?->content['description'] ?? '' }}</p>
        </article>
        <article>
            <div class="lei-pub-mission-icon gold"><i class="fa-solid fa-eye"></i></div>
            <h3>{{ $sections->get('vision')?->title }}</h3>
            <p>{{ $sections->get('vision')?->content['description'] ?? '' }}</p>
        </article>
    </div>
</section>

<section class="lei-pub-section">
    <div class="lei-pub-container lei-pub-cards-3">
        @foreach (($sections->get('governance')?->content['items'] ?? []) as $item)
            <article class="lei-pub-service-card">
                <h3>{{ $item['title'] }}</h3>
                <p>{{ $item['description'] }}</p>
            </article>
        @endforeach
    </div>
</section>

<section class="lei-pub-cta-band">
    <div class="lei-pub-container lei-pub-cta-inner">
        <div>
            <h2>{{ $sections->get('cta')?->title }}</h2>
            <p>{{ $sections->get('cta')?->subtitle }}</p>
        </div>
        <div class="lei-pub-cta-actions">
            <a href="{{ url($cta['primary_url'] ?? '/register') }}" class="lei-pub-btn">{{ $cta['primary_text'] ?? 'Register Now' }}</a>
            <a href="{{ url($cta['secondary_url'] ?? '/contact') }}" class="lei-pub-btn outline">{{ $cta['secondary_text'] ?? 'Contact Specialist' }}</a>
        </div>
    </div>
</section>
@endsection

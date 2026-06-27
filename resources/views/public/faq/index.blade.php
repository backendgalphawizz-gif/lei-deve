@extends('public.layouts.app')

@section('title', 'FAQ')
@section('body_class', 'page-faq')

@section('content')
<section class="lei-pub-faq-hero">
    <div class="lei-pub-container">
        <h1>{{ $sections->get('hero')?->title ?? 'Knowledge Center' }}</h1>
        <p>{{ $sections->get('hero')?->subtitle ?? '' }}</p>
        <form action="{{ route('faq') }}" method="GET" class="lei-pub-search-bar">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search for answers...">
        </form>
    </div>
</section>

<section class="lei-pub-section">
    <div class="lei-pub-container lei-pub-cards-4">
        @foreach ($categories as $category)
            <article class="lei-pub-category-card">
                <div class="lei-pub-category-icon"><i class="fa-solid fa-circle-nodes"></i></div>
                <h3>{{ $category->title }}</h3>
                <p>{{ $category->description }}</p>
            </article>
        @endforeach
    </div>
</section>

<section class="lei-pub-section lei-pub-muted">
    <div class="lei-pub-container lei-pub-faq-layout">
        <div>
            <h2><i class="fa-solid fa-circle-question"></i> Common Questions</h2>
            <div class="lei-pub-accordion">
                @foreach ($commonFaqs as $faq)
                    <details class="lei-pub-accordion-item" {{ $loop->first ? 'open' : '' }}>
                        <summary>{{ $faq->question }}</summary>
                        <p>{{ $faq->answer }}</p>
                    </details>
                @endforeach
            </div>
        </div>
        <aside class="lei-pub-sidebar-card">
            <h3><i class="fa-solid fa-shield-halved"></i> {{ $sections->get('sidebar')?->title }}</h3>
            <span class="lei-pub-badge">{{ $sections->get('sidebar')?->content['badge'] ?? '' }}</span>
            <p>{{ $sections->get('sidebar')?->content['description'] ?? '' }}</p>
        </aside>
    </div>
</section>

@php $support = $sections->get('support')?->content ?? []; @endphp
<section class="lei-pub-section">
    <div class="lei-pub-container">
        <div class="lei-pub-section-head">
            <h2>{{ $sections->get('support')?->title }}</h2>
            <p>{{ $sections->get('support')?->subtitle }}</p>
        </div>
        <div class="lei-pub-cards-3">
            @foreach ($support['items'] ?? [] as $item)
                <article class="lei-pub-support-card">
                    <i class="fa-solid fa-headset"></i>
                    <h3>{{ $item['title'] }}</h3>
                    <p>{{ $item['description'] }}</p>
                    <a href="{{ url($item['url'] ?? '/contact') }}" class="lei-pub-btn {{ !empty($item['primary']) ? '' : 'outline' }}">{{ $item['button'] }}</a>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endsection

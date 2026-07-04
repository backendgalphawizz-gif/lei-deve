@if ($leiBlocks->isNotEmpty())
@php
    $intro = $leiBlocks->firstWhere('block_type', 'intro');
    $categories = $leiBlocks->where('block_type', 'category');
    $reasons = $leiBlocks->firstWhere('block_type', 'reasons');
    $benefits = $leiBlocks->firstWhere('block_type', 'benefits');
    $mandatory = $leiBlocks->firstWhere('block_type', 'mandatory');
@endphp

<section class="lei-pub-section lei-lei-guide" id="who-needs-lei">
    <div class="lei-pub-container">
        @if ($intro)
            <div class="lei-lei-guide-intro">
                <div class="lei-pub-section-head">
                    <h2>{{ $intro->title }}</h2>
                    @if ($intro->body)
                        <p>{{ $intro->body }}</p>
                    @endif
                </div>
                @if ($intro->subtitle && $categories->isNotEmpty())
                    <p class="lei-lei-guide-list-heading">{{ $intro->subtitle }}</p>
                @endif
            </div>
        @endif

        @if ($categories->isNotEmpty())
            <div class="lei-lei-guide-categories">
                @foreach ($categories as $category)
                    <article class="lei-lei-guide-category">
                        <div class="lei-lei-guide-category-head">
                            @if ($category->category_number)
                                <span class="lei-lei-guide-num">{{ $category->category_number }}</span>
                            @endif
                            <h3>{{ $category->title }}</h3>
                        </div>
                        @if ($category->subtitle)
                            <p class="lei-lei-guide-category-sub">{{ $category->subtitle }}</p>
                        @endif
                        @if (! empty($category->items))
                            <ul class="lei-lei-guide-list">
                                @foreach ($category->items as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif

        @if ($reasons)
            <div class="lei-lei-guide-block">
                <h2>{{ $reasons->title }}</h2>
                @if ($reasons->subtitle)
                    <p class="lei-lei-guide-list-heading">{{ $reasons->subtitle }}</p>
                @endif
                @if (! empty($reasons->items))
                    <ul class="lei-lei-guide-list lei-lei-guide-list--columns">
                        @foreach ($reasons->items as $item)
                            <li><i class="fa-solid fa-check" aria-hidden="true"></i> {{ $item }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        @if ($benefits && ! empty($benefits->items))
            <div class="lei-lei-guide-block lei-lei-guide-block--benefits">
                <h2>{{ $benefits->title }}</h2>
                <div class="lei-lei-guide-benefits">
                    @foreach ($benefits->items as $item)
                        <article class="lei-lei-guide-benefit">
                            <div class="lei-lei-guide-benefit-icon"><i class="fa-solid fa-star" aria-hidden="true"></i></div>
                            <h3>{{ $item['title'] ?? '' }}</h3>
                            <p>{{ $item['text'] ?? '' }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($mandatory)
            <div class="lei-lei-guide-notice">
                <h2>{{ $mandatory->title }}</h2>
                @if ($mandatory->body)
                    <p>{{ $mandatory->body }}</p>
                @endif
            </div>
        @endif
    </div>
</section>
@endif

@if ($paginator->hasPages())
    <div class="lei-pagination">
        @if ($paginator->onFirstPage())
            <span class="disabled" aria-hidden="true">‹</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" aria-label="Previous page">‹</a>
        @endif

        @php
            $current = $paginator->currentPage();
            $last = $paginator->lastPage();
            $window = 2;
            $start = max(1, $current - $window);
            $end = min($last, $current + $window);
        @endphp

        @if ($start > 1)
            <a href="{{ $paginator->url(1) }}">1</a>
            @if ($start > 2)
                <span class="ellipsis">…</span>
            @endif
        @endif

        @for ($page = $start; $page <= $end; $page++)
            @if ($page == $current)
                <span class="active" aria-current="page">{{ $page }}</span>
            @else
                <a href="{{ $paginator->url($page) }}">{{ $page }}</a>
            @endif
        @endfor

        @if ($end < $last)
            @if ($end < $last - 1)
                <span class="ellipsis">…</span>
            @endif
            <a href="{{ $paginator->url($last) }}">{{ $last }}</a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" aria-label="Next page">›</a>
        @else
            <span class="disabled" aria-hidden="true">›</span>
        @endif
    </div>
@endif

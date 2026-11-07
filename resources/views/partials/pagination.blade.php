@if($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $start = max(1, min($current - 2, $last - 4));
        $end = min($last, $start + 4);
    @endphp
    <nav class="pagination" aria-label="Navigasi halaman">
        @if($paginator->onFirstPage())
            <span class="disabled" aria-hidden="true"><x-icon name="chevron-left" /></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya"><x-icon name="chevron-left" /></a>
        @endif

        @if($start > 1)
            <a href="{{ $paginator->url(1) }}" aria-label="Halaman 1">1</a>
            @if($start > 2)
                <span class="disabled" aria-hidden="true">…</span>
            @endif
        @endif

        @for($page = $start; $page <= $end; $page++)
            @if($page === $current)
                <span class="current" aria-current="page">{{ $page }}</span>
            @else
                <a href="{{ $paginator->url($page) }}" aria-label="Halaman {{ $page }}">{{ $page }}</a>
            @endif
        @endfor

        @if($end < $last)
            @if($end < $last - 1)
                <span class="disabled" aria-hidden="true">…</span>
            @endif
            <a href="{{ $paginator->url($last) }}" aria-label="Halaman {{ $last }}">{{ $last }}</a>
        @endif

        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya"><x-icon name="chevron-right" /></a>
        @else
            <span class="disabled" aria-hidden="true"><x-icon name="chevron-right" /></span>
        @endif
    </nav>
@endif

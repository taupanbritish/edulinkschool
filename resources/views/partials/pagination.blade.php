@if($paginator->hasPages())
    <nav class="pagination" aria-label="Navigasi halaman">
        @if($paginator->onFirstPage())
            <span class="disabled"><x-icon name="chevron-left" /></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" aria-label="Sebelumnya"><x-icon name="chevron-left" /></a>
        @endif

        <span class="disabled">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>

        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" aria-label="Berikutnya"><x-icon name="chevron-right" /></a>
        @else
            <span class="disabled"><x-icon name="chevron-right" /></span>
        @endif
    </nav>
@endif

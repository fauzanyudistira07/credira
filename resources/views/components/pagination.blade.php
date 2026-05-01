@props([
    'paginator',
    'surfaceClass' => '',
])

@if ($paginator->hasPages())
    <div {{ $attributes->merge(['class' => 'pagination-shell '.$surfaceClass]) }}>
        <p class="pagination-shell__meta">
            Menampilkan {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
        </p>

        <div class="pagination-shell__links">
            @if ($paginator->onFirstPage())
                <span class="pagination-shell__link pagination-shell__link--disabled">Sebelumnya</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="pagination-shell__link">Sebelumnya</a>
            @endif

            <span class="pagination-shell__page">
                Halaman {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="pagination-shell__link">Berikutnya</a>
            @else
                <span class="pagination-shell__link pagination-shell__link--disabled">Berikutnya</span>
            @endif
        </div>
    </div>
@endif

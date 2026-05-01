@props([
    'title' => null,
    'description' => null,
    'resultText' => null,
    'resetHref' => null,
    'activeFilters' => [],
    'surfaceClass' => '',
])

<section {{ $attributes->merge(['class' => 'filter-toolbar '.$surfaceClass]) }}>
    @if ($title || $description || $resultText)
        <div class="filter-toolbar__head">
            <div>
                @if ($title)
                    <h3 class="filter-toolbar__title">{{ $title }}</h3>
                @endif
                @if ($description)
                    <p class="filter-toolbar__copy">{{ $description }}</p>
                @endif
            </div>
            @if ($resultText)
                <div class="filter-toolbar__meta">{{ $resultText }}</div>
            @endif
        </div>
    @endif

    {{ $slot }}

    @if ($activeFilters !== [])
        <div class="mt-4 flex flex-wrap items-center gap-2">
            @foreach ($activeFilters as $filter)
                <span class="filter-chip">
                    <span>{{ $filter }}</span>
                </span>
            @endforeach
            @if ($resetHref)
                <a href="{{ $resetHref }}" class="btn-ghost !rounded-full !px-3 !py-1.5 text-xs">Reset filter</a>
            @endif
        </div>
    @endif
</section>

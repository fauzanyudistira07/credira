@props([
    'title' => null,
    'description' => null,
    'actions' => null,
    'wrapClass' => '',
])

<section {{ $attributes->merge(['class' => 'table-shell']) }}>
    @if ($title || $description || isset($actions))
        <div class="table-shell__head">
            <div>
                @if ($title)
                    <h3 class="table-shell__title">{{ $title }}</h3>
                @endif
                @if ($description)
                    <p class="table-shell__copy">{{ $description }}</p>
                @endif
            </div>
            @if (isset($actions))
                <div class="table-shell__actions">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div class="{{ trim('table-shell__wrap '.$wrapClass) }}">
        {{ $slot }}
    </div>
</section>

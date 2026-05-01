@props([
    'title' => 'Belum ada data',
    'description' => 'Data akan muncul di sini setelah tersedia.',
    'actionLabel' => null,
    'actionHref' => null,
])

<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <div class="empty-icon">
        <svg viewBox="0 0 24 24" class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.7">
            <path d="M8 5.5h8M7 9.5h10M7 13.5h7" stroke-linecap="round"/>
            <path d="M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" stroke-linejoin="round"/>
        </svg>
    </div>
    <h3 class="mt-5 text-xl font-semibold text-slate-900">{{ $title }}</h3>
    <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-slate-500">{{ $description }}</p>
    @if ($actionLabel && $actionHref)
        <a href="{{ $actionHref }}" class="btn-primary mt-6">{{ $actionLabel }}</a>
    @endif
</div>

@props([
    'title' => 'Konfirmasi',
    'description' => null,
    'name' => 'modal',
])

<div x-data="{ open: false }" {{ $attributes }}>
    <div @click="open = true">
        {{ $trigger ?? '' }}
    </div>

    <template x-teleport="body">
        <div x-show="open" x-transition.opacity class="modal-backdrop" @click="open = false"></div>
    </template>

    <template x-teleport="body">
        <div x-show="open" x-transition class="modal-panel">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-xl font-semibold text-slate-900">{{ $title }}</h3>
                    @if ($description)
                        <p class="mt-3 text-sm leading-6 text-slate-600">{{ $description }}</p>
                    @endif
                </div>
                <button type="button" class="btn-ghost" @click="open = false">Tutup</button>
            </div>
            <div class="mt-6">
                {{ $slot }}
            </div>
        </div>
    </template>
</div>

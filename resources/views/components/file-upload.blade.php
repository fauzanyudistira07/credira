@props([
    'name',
    'label',
    'accept' => null,
    'helper' => null,
    'error' => null,
    'existingUrl' => null,
    'existingLabel' => 'File saat ini',
    'previewType' => 'auto',
    'multiple' => false,
    'required' => false,
])

@php
    $inputId = $attributes->get('id') ?: str($name)->replace(['[', ']'], ['-', ''])->trim('-');
    $previewId = 'upload-preview-'.$inputId;
    $acceptString = strtolower((string) $accept);
    $hasImagePreview = $previewType === 'image'
        || ($previewType === 'auto' && (
            str($acceptString)->contains('image')
            || collect(['.jpg', '.jpeg', '.png', '.webp', '.gif', '.bmp', '.svg', '.avif'])
                ->contains(fn (string $extension) => str($acceptString)->contains($extension))
        ));
@endphp

<div {{ $attributes->class('file-upload-box') }}>
    <div class="flex items-start justify-between gap-4">
        <div>
            <label class="field-label" for="{{ $inputId }}">{{ $label }}</label>
            @if ($helper)
                <p class="field-help !mt-0">{{ $helper }}</p>
            @endif
        </div>
        @if ($required)
            <span class="filter-chip !px-2.5 !py-1">Wajib</span>
        @endif
    </div>

    <label for="{{ $inputId }}" class="file-upload-dropzone">
        <span class="file-upload-dropzone__icon">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M12 16V5m0 0-4 4m4-4 4 4" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M4 16.5v1.25A2.25 2.25 0 0 0 6.25 20h11.5A2.25 2.25 0 0 0 20 17.75V16.5" stroke-linecap="round"/>
            </svg>
        </span>
        <span>
            <span class="file-upload-dropzone__title">{{ $multiple ? 'Pilih beberapa file' : 'Pilih file untuk diunggah' }}</span>
            <span class="file-upload-dropzone__copy">{{ $accept ? strtoupper(str_replace(',', ' / ', str_replace('.', '', $accept))) : 'PDF / JPG / PNG' }}</span>
        </span>
    </label>

    <input
        id="{{ $inputId }}"
        type="file"
        name="{{ $name }}"
        class="sr-only"
        @if($accept) accept="{{ $accept }}" @endif
        @if($multiple) multiple @endif
        @if($hasImagePreview) data-file-preview-input data-preview-target="#{{ $previewId }}" @endif
    >

    @if ($existingUrl)
        <div class="file-upload-existing">
            <p class="text-sm font-semibold text-slate-900">{{ $existingLabel }}</p>
            <a href="{{ $existingUrl }}" target="_blank" class="dashboard-text-link">Buka file existing</a>
        </div>
    @endif

    <div id="{{ $previewId }}" class="file-upload-preview">
        @if ($existingUrl && $hasImagePreview)
            <a href="{{ $existingUrl }}" target="_blank" rel="noopener noreferrer" class="block">
                <img src="{{ $existingUrl }}" alt="{{ $label }}" class="h-40 w-full rounded-[1.2rem] object-cover">
            </a>
        @endif
    </div>

    <x-form-error :name="$error ?? $name" />
</div>

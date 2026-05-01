<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Credira' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
    @php
        $logoPath = public_path('assets/logo1.png');
        $logoUrl = asset('assets/logo1.png').(file_exists($logoPath) ? '?v='.filemtime($logoPath) : '');
    @endphp
    <div x-data class="toast-stack">
        <template x-for="toast in $store.toast.items" :key="toast.id">
            <div class="toast-card" :class="toast.type === 'error' ? 'border-rose-200' : 'border-emerald-200'">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-slate-900" x-text="toast.title"></p>
                        <p class="mt-1 text-sm leading-6 text-slate-600" x-text="toast.message"></p>
                    </div>
                    <button type="button" class="btn-ghost !px-2" @click="$store.toast.remove(toast.id)">x</button>
                </div>
            </div>
        </template>
    </div>

    <main class="auth-viewport">
        @include('partials.flash')

        <div class="shell auth-shell-wrap">
            <a href="{{ route('home') }}" class="auth-brand">
                <img src="{{ $logoUrl }}" alt="Credira" class="h-11 w-auto sm:h-12">
                <span>
                    <strong>CREDIRA</strong>
                    <small>Pembiayaan motor premium</small>
                </span>
            </a>

            <div class="auth-stage">
                <section class="auth-form-shell" data-reveal>
                    @yield('content')
                </section>
            </div>
        </div>
    </main>
</body>
</html>

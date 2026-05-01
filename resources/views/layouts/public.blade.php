<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Credira' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="{{ request()->routeIs('home') ? 'home-page' : 'public-page-body' }}"
    x-data="{ publicNavOpen: false, navScrolled: false, activeSection: 'hero' }"
    @scroll.window="navScrolled = window.scrollY > 14"
>
    @php
        $isHome = request()->routeIs('home');
        $logoPath = public_path('assets/logo1.png');
        $logoUrl = asset('assets/logo1.png').(file_exists($logoPath) ? '?v='.filemtime($logoPath) : '');
        $pageLinks = [
            ['label' => 'Beranda', 'route' => 'home'],
            ['label' => 'Motor', 'route' => 'motors.index'],
            ['label' => 'Simulasi', 'route' => 'simulation'],
            ['label' => 'Cara Pengajuan', 'route' => 'how-to-apply'],
            ['label' => 'FAQ', 'route' => 'faq'],
            ['label' => 'Kontak', 'route' => 'contact'],
        ];
        $navLinks = $pageLinks;
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

    <header class="fixed inset-x-0 top-0 z-50 px-2.5 pt-3 sm:px-4 sm:pt-4 lg:px-5">
        <div
            class="mx-auto max-w-[90rem] rounded-[1.7rem] border transition duration-300"
            :class="navScrolled
                ? '{{ $isHome ? 'border-white/12 bg-[#111111]/78 shadow-[0_24px_90px_-55px_rgba(0,0,0,0.85)] backdrop-blur-xl' : 'border-white/10 bg-[rgba(11,17,32,0.82)] shadow-[0_24px_90px_-55px_rgba(0,0,0,0.7)] backdrop-blur-xl' }}'
                : '{{ $isHome ? 'border-white/8 bg-white/6 backdrop-blur-md' : 'border-white/8 bg-[rgba(11,17,32,0.65)] backdrop-blur-md' }}'"
        >
            <div class="shell flex items-center justify-between gap-4 py-3">
                <a href="{{ route('home') }}" class="public-brand">
                    <img src="{{ $logoUrl }}" alt="Credira" class="h-11 w-auto sm:h-12">
                    <div>
                        <p class="text-sm font-semibold tracking-[0.24em] {{ $isHome ? 'text-white' : 'text-white' }}">CREDIRA</p>
                        <p class="mt-1 text-[11px] {{ $isHome ? 'text-white/58' : 'text-slate-400' }}">Pembiayaan motor premium</p>
                    </div>
                </a>

                <button
                    type="button"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-white/8 text-white transition hover:bg-white/12 lg:hidden"
                    @click="publicNavOpen = !publicNavOpen"
                    :aria-expanded="publicNavOpen.toString()"
                    aria-controls="public-nav-mobile"
                >
                    <span class="sr-only">Toggle navigation</span>
                    <svg x-show="!publicNavOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                    <svg x-show="publicNavOpen" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>

                <nav class="hidden lg:block">
                    <ul class="flex items-center gap-2">
                        @foreach ($navLinks as $link)
                            <li>
                                <a
                                    href="{{ route($link['route']) }}"
                                    class="inline-flex items-center rounded-full px-4 py-2.5 text-sm font-medium transition duration-200 {{ request()->routeIs($link['route']) ? 'bg-white text-slate-950 shadow-[0_18px_40px_-24px_rgba(255,255,255,0.25)]' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"
                                >
                                    {{ $link['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>

                <div class="hidden items-center gap-3 lg:flex">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-secondary !min-h-11 !rounded-full !border-white/10 !bg-white !px-5 !py-3 !text-slate-950">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center justify-center rounded-full border border-white/10 bg-white/8 px-5 py-3 text-sm font-semibold text-white transition duration-200 hover:bg-white/12">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex min-h-11 items-center justify-center px-2 py-3 text-sm font-semibold text-[#ff7b33] transition duration-200 hover:text-[#ff9a62]">
                            Daftar
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <div
        x-cloak
        x-show="publicNavOpen"
        x-transition.opacity.duration.250ms
        class="fixed inset-0 z-40 bg-slate-950/55 backdrop-blur-sm lg:hidden"
        @click="publicNavOpen = false"
    ></div>

    <div
        id="public-nav-mobile"
        x-cloak
        x-show="publicNavOpen"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0 -translate-y-3"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="fixed inset-x-4 top-24 z-50 lg:hidden"
    >
        <div class="rounded-[1.8rem] border border-white/10 bg-[#111111]/96 p-4 text-white shadow-[0_30px_90px_-40px_rgba(0,0,0,0.78)] backdrop-blur-xl">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-white/45">Menu Navigasi</p>
                    <p class="mt-1 text-sm text-white/68">Akses cepat ke halaman dan section penting Credira.</p>
                </div>
                <button
                    type="button"
                    class="inline-flex h-10 items-center justify-center rounded-xl border border-white/10 bg-white/8 px-3 text-sm font-semibold text-white"
                    @click="publicNavOpen = false"
                >
                    Tutup
                </button>
            </div>

            <div class="mt-4 grid gap-2">
                @foreach ($navLinks as $link)
                    <a
                        href="{{ route($link['route']) }}"
                        class="rounded-2xl px-4 py-3 text-sm font-medium transition duration-200 {{ request()->routeIs($link['route']) ? 'bg-white text-slate-950' : 'bg-white/6 text-white/76 hover:bg-white/10 hover:text-white' }}"
                        @click="publicNavOpen = false"
                    >
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>

            <div class="mt-4 grid gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-secondary w-full justify-center !min-h-11 !rounded-full !border-white/10 !bg-white !text-slate-950" @click="publicNavOpen = false">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-full border border-white/10 bg-white/8 px-5 py-3 text-sm font-semibold text-white transition duration-200 hover:bg-white/12" @click="publicNavOpen = false">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex min-h-11 w-full items-center justify-center px-5 py-3 text-sm font-semibold text-[#ff7b33] transition duration-200 hover:text-[#ff9a62]" @click="publicNavOpen = false">
                        Daftar
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <main class="page-stage overflow-x-clip">
        @include('partials.flash')
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <footer id="footer" class="overflow-hidden bg-[#0d0d0d] text-white">
        <div class="shell py-14 sm:py-16">
            <div class="grid gap-10 border-b border-white/10 pb-10 lg:grid-cols-[minmax(0,1.2fr)_0.8fr_0.9fr_0.9fr]">
                <div>
                    <a href="{{ route('home') }}" class="public-brand">
                        <img src="{{ $logoUrl }}" alt="Credira" class="h-12 w-auto">
                        <div>
                            <p class="text-sm font-semibold tracking-[0.24em] text-white">CREDIRA</p>
                            <p class="mt-1 text-[11px] text-white/48">Pembiayaan motor premium</p>
                        </div>
                    </a>
                    <p class="mt-5 max-w-md text-sm leading-7 text-white/62">
                        Credira menghadirkan pengalaman pembiayaan motor premium yang lebih modern, transparan, dan nyaman dipakai dari simulasi hingga pengajuan.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3 text-xs font-semibold uppercase tracking-[0.22em] text-white/55">
                        <span class="rounded-full border border-white/10 bg-white/6 px-3 py-2">Premium flow</span>
                        <span class="rounded-full border border-white/10 bg-white/6 px-3 py-2">Transparent rate</span>
                        <span class="rounded-full border border-white/10 bg-white/6 px-3 py-2">Responsive support</span>
                    </div>
                </div>

                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-white/42">Menu Cepat</p>
                    <div class="mt-5 grid gap-3 text-sm text-white/68">
                        <a href="{{ route('motors.index') }}" class="transition hover:text-white">Katalog Motor</a>
                        <a href="{{ route('simulation') }}" class="transition hover:text-white">Simulasi Cicilan</a>
                        <a href="{{ route('how-to-apply') }}" class="transition hover:text-white">Cara Pengajuan</a>
                        <a href="{{ route('faq') }}" class="transition hover:text-white">FAQ</a>
                    </div>
                </div>

                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-white/42">Kontak</p>
                    <div class="mt-5 grid gap-3 text-sm text-white/68">
                        <p>Jl. Pembiayaan Raya No. 12, Jakarta</p>
                        <a href="tel:+62215550199" class="transition hover:text-white">+62 21 555 0199</a>
                        <a href="mailto:hello@credira.test" class="transition hover:text-white">hello@credira.test</a>
                        <a href="{{ route('contact') }}" class="transition hover:text-white">Hubungi tim Credira</a>
                    </div>
                </div>

                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-white/42">Sosial Media</p>
                    <div class="mt-5 grid gap-3 text-sm text-white/68">
                        <a href="{{ route('contact') }}" class="transition hover:text-white">Instagram</a>
                        <a href="{{ route('contact') }}" class="transition hover:text-white">LinkedIn</a>
                        <a href="{{ route('contact') }}" class="transition hover:text-white">TikTok</a>
                        <a href="{{ route('contact') }}" class="transition hover:text-white">YouTube</a>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 pt-6 text-sm text-white/42 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ now()->year }} Credira. Seluruh hak cipta dilindungi.</p>
                <p>Premium financing interface for modern motorcycle buyers.</p>
            </div>
        </div>
    </footer>
</body>
</html>

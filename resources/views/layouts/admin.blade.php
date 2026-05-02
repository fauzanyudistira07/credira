<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Credira Admin' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-shell-body admin-console-body text-slate-100" x-data="{ adminDrawerOpen: false }" :class="adminDrawerOpen ? 'overflow-hidden lg:overflow-visible' : ''">
    @php
        $authUser = auth()->user();
        $initials = collect(preg_split('/\s+/', trim($authUser?->name ?? 'Admin')))
            ->filter()
            ->take(2)
            ->map(fn (string $part) => strtoupper(substr($part, 0, 1)))
            ->implode('');
        $adminMenu = [
            ['label' => 'Dashboard', 'caption' => 'Ringkasan operasional', 'route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'icon' => 'home'],
            ['label' => 'Users', 'caption' => 'Kelola admin, marketing, ceo', 'route' => 'admin.users.index', 'pattern' => 'admin.users.*', 'icon' => 'users'],
            ['label' => 'Motor', 'caption' => 'Master katalog dan stok', 'route' => 'admin.motors.index', 'pattern' => 'admin.motors.*', 'icon' => 'motor'],
            ['label' => 'Pengajuan', 'caption' => 'Review semua pengajuan', 'route' => 'admin.pengajuan.index', 'pattern' => 'admin.pengajuan.*', 'icon' => 'applications'],
            ['label' => 'Pelanggan', 'caption' => 'Monitoring data pelanggan', 'route' => 'admin.pelanggan.index', 'pattern' => 'admin.pelanggan.*', 'icon' => 'customers'],
            ['label' => 'Pembayaran', 'caption' => 'Verifikasi transaksi', 'route' => 'admin.payments.index', 'pattern' => 'admin.payments.*', 'icon' => 'payments'],
            ['label' => 'Pengiriman', 'caption' => 'Konfirmasi unit kirim', 'route' => 'admin.deliveries.index', 'pattern' => 'admin.deliveries.*', 'icon' => 'delivery'],
        ];
        $iconPaths = [
            'home' => 'M3.75 10.5 12 3.75l8.25 6.75v9a.75.75 0 0 1-.75.75h-4.5v-5.25h-6V20.25h-4.5a.75.75 0 0 1-.75-.75z',
            'applications' => 'M6 4.5h8.379a1.5 1.5 0 0 1 1.06.44l2.621 2.62a1.5 1.5 0 0 1 .44 1.061V19.5A1.5 1.5 0 0 1 17 21H6a1.5 1.5 0 0 1-1.5-1.5V6A1.5 1.5 0 0 1 6 4.5zm3 4.5h6m-6 3h6m-6 3h3',
            'users' => 'M16.5 18.75a4.5 4.5 0 0 0-9 0m9 0h3a2.25 2.25 0 0 1 2.25 2.25v.75H2.25V21a2.25 2.25 0 0 1 2.25-2.25h3m9-10.5A3.75 3.75 0 1 1 9 8.25a3.75 3.75 0 0 1 7.5 0zm6.75 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0z',
            'motor' => 'M4.5 16.5h15m-12-6.75h9m-12 0a2.25 2.25 0 1 1 0-4.5m12 4.5a2.25 2.25 0 1 0 0-4.5m-12 11.25a2.25 2.25 0 1 1 0-4.5m12 4.5a2.25 2.25 0 1 0 0-4.5',
            'customers' => 'M15 19.128a9.38 9.38 0 0 0-3-.503 9.38 9.38 0 0 0-3 .503m6 0A3.375 3.375 0 0 0 18.375 15.75V15a3.375 3.375 0 0 0-6.75 0v.75A3.375 3.375 0 0 0 15 19.128zm-3-8.253a3 3 0 1 0 0-6 3 3 0 0 0 0 6z',
            'payments' => 'M3.75 7.5A2.25 2.25 0 0 1 6 5.25h12A2.25 2.25 0 0 1 20.25 7.5v9A2.25 2.25 0 0 1 18 18.75H6a2.25 2.25 0 0 1-2.25-2.25v-9zm0 2.25h16.5M7.5 14.25h3.75',
            'delivery' => 'M3.75 7.5h9v6h-9zM12.75 9.75h3l2.25 2.25v1.5h-5.25zM7.5 16.5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm9 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z',
        ];
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

    <div class="admin-console-shell">
        <aside class="hidden px-5 py-5 lg:block">
                <div class="admin-sidebar sticky top-5 flex min-h-[calc(100vh-2.5rem)] flex-col">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                        <div class="app-avatar !h-12 !w-12 !rounded-[1.2rem]">CR</div>
                        <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.32em] text-[#ff8b42]">Credira Admin</p>
                        <p class="mt-1 text-sm text-slate-400">Motor financing control room</p>
                    </div>
                </a>

                    <div class="admin-profile-card mt-8">
                        <div class="flex items-center gap-3">
                            <div class="app-avatar !h-10 !w-10 !rounded-[1rem]">{{ $initials }}</div>
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-white">{{ $authUser->name }}</p>
                                <p class="truncate text-sm text-slate-400">{{ $authUser->email }}</p>
                            </div>
                        </div>
                        <div class="admin-profile-card__meta">
                            <span>Administrator workspace</span>
                        </div>
                    </div>

                <nav class="mt-8 grid gap-2.5">
                    @foreach ($adminMenu as $item)
                        @php($active = request()->routeIs($item['pattern']))
                        <a href="{{ route($item['route']) }}" class="admin-nav-link {{ $active ? 'admin-nav-link-active' : '' }}">
                            <span class="admin-nav-link__icon">
                                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="{{ $iconPaths[$item['icon']] ?? $iconPaths['home'] }}"></path>
                                </svg>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold">{{ $item['label'] }}</span>
                                <span class="mt-1 block truncate text-xs {{ $active ? 'text-orange-100/80' : 'text-slate-400' }}">{{ $item['caption'] }}</span>
                            </span>
                        </a>
                    @endforeach
                </nav>

                <div class="mt-auto space-y-3 pt-8">
                    <a href="{{ route('home') }}" class="admin-sidebar-link">Lihat Website</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="admin-logout-button w-full">Keluar</button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="admin-console-main min-w-0">
            <header class="admin-header sticky top-0 z-40">
                <div class="shell">
                    <div class="admin-header__inner">
                        <div class="app-header__lead">
                            <button type="button" class="admin-header__icon lg:!hidden" @click="adminDrawerOpen = true" aria-label="Buka menu" :aria-expanded="adminDrawerOpen.toString()">
                                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 7h16M4 12h16M4 17h12" stroke-linecap="round"/>
                                </svg>
                            </button>
                            <div class="min-w-0">
                                <p class="admin-header__eyebrow text-xs font-semibold uppercase tracking-[0.3em] text-orange-200/72">Credira Admin</p>
                                <h1 class="admin-header__title">{{ $heading ?? 'Dashboard Admin' }}</h1>
                                <p class="admin-header__copy">{{ $subheading ?? 'Kelola user, motor, pengajuan, dan pelanggan dari satu workspace premium.' }}</p>
                            </div>
                        </div>

                        <div class="admin-header__actions">
                            <div class="admin-header__meta hidden xl:flex">
                                <span class="admin-header__pulse"></span>
                                <span>Admin workspace</span>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div
                class="fixed inset-0 z-50 bg-slate-950/72 backdrop-blur-sm lg:!hidden"
                x-cloak
                x-show="adminDrawerOpen"
                x-transition.opacity.duration.200ms
                @click="adminDrawerOpen = false"
            ></div>

            <div
                id="admin-sidebar-mobile"
                class="admin-mobile-drawer lg:!hidden"
                x-cloak
                x-show="adminDrawerOpen"
                x-transition:enter="transition ease-out duration-250"
                x-transition:enter-start="-translate-x-full opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="-translate-x-full opacity-0"
            >
                <div class="admin-sidebar">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-[#ff8b42]">Credira Admin</p>
                            <p class="mt-1 text-sm text-slate-400">Quick navigation</p>
                        </div>
                        <button type="button" class="admin-header__icon !h-10 !w-10" @click="adminDrawerOpen = false" aria-label="Tutup menu">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="app-avatar !h-10 !w-10 !rounded-[1rem]">{{ $initials }}</div>
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-white">{{ $authUser->name }}</p>
                            <p class="truncate text-sm text-slate-400">{{ $authUser->email }}</p>
                        </div>
                    </div>
                    <div class="mt-5 grid gap-2">
                        @foreach ($adminMenu as $item)
                            <a href="{{ route($item['route']) }}" class="admin-nav-link {{ request()->routeIs($item['pattern']) ? 'admin-nav-link-active' : '' }}" @click="adminDrawerOpen = false">
                                <span class="admin-nav-link__icon">
                                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="{{ $iconPaths[$item['icon']] ?? $iconPaths['home'] }}"></path>
                                    </svg>
                                </span>
                                <span class="text-sm font-semibold">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                    <div class="mt-4 grid gap-2">
                        <a href="{{ route('home') }}" class="admin-sidebar-link">Lihat Website</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="admin-logout-button w-full">Keluar</button>
                        </form>
                    </div>
                </div>
            </div>

            @include('partials.flash')

            <main class="shell page-stage pb-12 pt-6 lg:pt-8">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Credira App' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-shell-body text-slate-900">
    @php
        $authUser = auth()->user();
        $menu = [
            ['label' => 'Beranda', 'caption' => 'Ringkasan pembiayaan', 'route' => 'user.dashboard', 'pattern' => 'user.dashboard', 'icon' => 'home'],
            ['label' => 'Pengajuan Saya', 'caption' => 'Status kredit motor', 'route' => 'user.applications.index', 'pattern' => 'user.applications.*', 'icon' => 'applications'],
            ['label' => 'My Kredit', 'caption' => 'Ringkasan kontrak aktif', 'route' => 'user.my-credit.index', 'pattern' => 'user.my-credit.*', 'icon' => 'applications'],
            ['label' => 'Angsuran', 'caption' => 'Tagihan dan jatuh tempo', 'route' => 'user.installments.index', 'pattern' => 'user.installments.*', 'icon' => 'installments'],
            ['label' => 'Pembayaran', 'caption' => 'Upload dan verifikasi', 'route' => 'user.payments.index', 'pattern' => 'user.payments.*', 'icon' => 'payments'],
            ['label' => 'Pengiriman', 'caption' => 'Lacak unit motor', 'route' => 'user.deliveries.index', 'pattern' => 'user.deliveries.*', 'icon' => 'delivery'],
            ['label' => 'Notifikasi', 'caption' => 'Update akun terbaru', 'route' => 'user.notifications.index', 'pattern' => 'user.notifications.*', 'icon' => 'notifications'],
            ['label' => 'Profil', 'caption' => 'Data akun dan alamat', 'route' => 'user.profile.index', 'pattern' => 'user.profile.*', 'icon' => 'profile'],
        ];
        $mobileMenu = array_values(array_filter($menu, fn (array $item) => in_array($item['route'], [
            'user.dashboard',
            'user.applications.index',
            'user.my-credit.index',
            'user.installments.index',
            'user.payments.index',
            'user.profile.index',
        ], true)));
        $unreadNotifications = $authUser?->notifications()->where('is_read', false)->count() ?? 0;
        $initials = collect(preg_split('/\s+/', trim($authUser?->name ?? 'User')))
            ->filter()
            ->take(2)
            ->map(fn (string $part) => strtoupper(substr($part, 0, 1)))
            ->implode('');
        $showMobileNav = false;
        $showHeaderAction = ! request()->routeIs(
            'user.applications.create',
            'user.applications.edit',
            'user.applications.documents'
        );
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

    <div class="relative min-h-screen lg:grid lg:grid-cols-[300px_minmax(0,1fr)]">
        <aside class="hidden px-5 py-5 lg:block">
            <div class="app-panel-dark sticky top-5 flex min-h-[calc(100vh-2.5rem)] flex-col">
                <a href="{{ route('user.dashboard') }}" class="flex items-center gap-3">
                    <div class="app-avatar !h-14 !w-14 !rounded-[1.4rem]">CR</div>
                    <div>
                        <p class="text-sm font-semibold tracking-[0.28em] text-white/78">CREDIRA</p>
                        <p class="mt-1 text-xs text-white/54">Credit rider app</p>
                    </div>
                </a>

                <div class="mt-8 rounded-[1.7rem] border border-white/10 bg-white/7 p-4">
                    <div class="flex items-center gap-3">
                        <div class="app-avatar">{{ $initials }}</div>
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-white">{{ $authUser->name }}</p>
                            <p class="truncate text-sm text-white/62">{{ $authUser->email }}</p>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="app-metric-card-dark">
                            <p class="text-[11px] uppercase tracking-[0.22em] text-white/48">Akun</p>
                            <p class="mt-2 text-sm font-semibold text-white">Nasabah aktif</p>
                        </div>
                        <div class="app-metric-card-dark">
                            <p class="text-[11px] uppercase tracking-[0.22em] text-white/48">Alert</p>
                            <p class="mt-2 text-sm font-semibold text-white">{{ $unreadNotifications }} baru</p>
                        </div>
                    </div>
                </div>

                <nav class="mt-8 grid gap-2">
                    @foreach ($menu as $item)
                        @php($active = request()->routeIs($item['pattern']))
                        <a href="{{ route($item['route']) }}" class="app-nav-link {{ $active ? 'app-nav-link-active' : '' }}">
                            <span class="app-nav-icon {{ $active ? '!border-slate-200 !bg-slate-100 !text-slate-900' : '' }}">
                                @switch($item['icon'])
                                    @case('home')
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-4.5v-6h-5v6H5a1 1 0 0 1-1-1v-9.5Z" stroke-linejoin="round"/>
                                        </svg>
                                        @break
                                    @case('applications')
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="M8 4.5h8m-8 5h8m-8 5h5m-8-11h12a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-13a2 2 0 0 1 2-2Z" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        @break
                                    @case('installments')
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="M7 3.5v3m10-3v3M4 8.5h16M6 12.5h4m4 0h4m-12 4h4m4 0h4M5 5.5h14a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-11a2 2 0 0 1 2-2Z" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        @break
                                    @case('payments')
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <rect x="3" y="5" width="18" height="14" rx="3"/>
                                            <path d="M3 10.5h18M7 15h3" stroke-linecap="round"/>
                                        </svg>
                                        @break
                                    @case('delivery')
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="M3 7.5h11v8H3z" stroke-linejoin="round"/>
                                            <path d="M14 10h3.5l2.5 2.5v3H14z" stroke-linejoin="round"/>
                                            <circle cx="8" cy="18" r="2"/>
                                            <circle cx="18" cy="18" r="2"/>
                                        </svg>
                                        @break
                                    @case('notifications')
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="M6.5 9.5a5.5 5.5 0 1 1 11 0v3.3c0 .7.28 1.38.78 1.88l.72.72a1 1 0 0 1-.71 1.7H5.71a1 1 0 0 1-.71-1.7l.72-.72c.5-.5.78-1.18.78-1.88V9.5Z" stroke-linejoin="round"/>
                                            <path d="M9.5 19a2.5 2.5 0 0 0 5 0" stroke-linecap="round"/>
                                        </svg>
                                        @break
                                    @case('profile')
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <circle cx="12" cy="8" r="3.5"/>
                                            <path d="M5 19a7 7 0 0 1 14 0" stroke-linecap="round"/>
                                        </svg>
                                        @break
                                @endswitch
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold">{{ $item['label'] }}</p>
                                <p class="mt-0.5 text-xs {{ $active ? 'text-slate-500' : 'text-white/46' }}">{{ $item['caption'] }}</p>
                            </div>
                            @if ($item['route'] === 'user.notifications.index' && $unreadNotifications > 0)
                                <span class="rounded-full bg-slate-950 px-2 py-1 text-[11px] font-bold text-white">{{ $unreadNotifications }}</span>
                            @endif
                        </a>
                    @endforeach
                </nav>

                <div class="mt-auto rounded-[1.8rem] border border-white/10 bg-white/7 p-5">
                    <p class="text-[11px] uppercase tracking-[0.32em] text-white/48">Aksi cepat</p>
                    <h2 class="mt-3 text-xl font-semibold text-white">Ajukan motor baru atau keluar dari akun.</h2>
                    <p class="mt-3 text-sm leading-7 text-white/62">Semua progress kredit, pembayaran, dan pengiriman tetap tersimpan di database MySQL yang aktif.</p>
                    <a href="{{ route('user.applications.create') }}" class="btn-accent mt-5 w-full">Ajukan Kredit</a>
                    <form method="POST" action="{{ route('logout') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="btn-secondary w-full">Keluar</button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="min-w-0">
            <header class="app-header sticky top-0 z-40">
                <div class="shell">
                    <div class="app-header__inner">
                        <div class="app-header__lead">
                            <button type="button" class="app-header__icon lg:!hidden" data-drawer-toggle="#user-sidebar-mobile" aria-label="Buka menu">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M4 7h16M4 12h16M4 17h12" stroke-linecap="round"/>
                            </svg>
                            </button>
                            <div class="min-w-0">
                                <div class="app-header__meta">
                                    <span>Credira App</span>
                                    <span class="app-header__dot"></span>
                                    <span class="truncate">Pembiayaan motor</span>
                                </div>
                                <h1 class="app-header__title">{{ $heading ?? 'Beranda Nasabah' }}</h1>
                                <p class="app-header__copy">{{ $subheading ?? 'Kelola kredit motor, tagihan, pembayaran, dan pengiriman dalam satu aplikasi web.' }}</p>
                            </div>
                        </div>

                        <div class="app-header__actions">
                            <a href="{{ route('user.notifications.index') }}" class="app-header__icon" aria-label="Notifikasi">
                                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M6.5 9.5a5.5 5.5 0 1 1 11 0v3.3c0 .7.28 1.38.78 1.88l.72.72a1 1 0 0 1-.71 1.7H5.71a1 1 0 0 1-.71-1.7l.72-.72c.5-.5.78-1.18.78-1.88V9.5Z" stroke-linejoin="round"/>
                                    <path d="M9.5 19a2.5 2.5 0 0 0 5 0" stroke-linecap="round"/>
                                </svg>
                                @if ($unreadNotifications > 0)
                                    <span class="app-header__badge">{{ $unreadNotifications }}</span>
                                @endif
                            </a>
                            <a href="{{ route('motors.index') }}" class="btn-secondary hidden !min-h-0 !px-5 !py-3 md:inline-flex">Katalog Motor</a>
                            @if ($showHeaderAction)
                                <a href="{{ route('user.applications.create') }}" class="app-header__cta">
                                    <span class="sm:hidden">Ajukan</span>
                                    <span class="hidden sm:inline">Ajukan Kredit</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </header>

            <div id="user-sidebar-mobile" class="shell hidden py-4 lg:!hidden">
                <div class="app-panel">
                    <div class="flex items-center gap-3">
                        <div class="app-avatar">{{ $initials }}</div>
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-slate-950">{{ $authUser->name }}</p>
                            <p class="truncate text-sm text-slate-500">{{ $authUser->email }}</p>
                        </div>
                    </div>
                    <div class="mt-5 grid gap-2">
                        @foreach ($menu as $item)
                            <a href="{{ route($item['route']) }}" class="rounded-[1.35rem] border px-4 py-3 text-sm font-medium transition {{ request()->routeIs($item['pattern']) ? 'border-slate-900 bg-slate-900 text-white' : 'border-[#ece4db] bg-[#fffaf6] text-slate-700' }}">
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf
                        <button type="submit" class="btn-secondary w-full">Keluar</button>
                    </form>
                </div>
            </div>

            @include('partials.flash')

            <main class="shell page-stage pb-32 pt-6 lg:pb-10 lg:pt-8">
                @yield('content')
            </main>
        </div>
    </div>

    @if ($showMobileNav)
        <nav class="app-mobile-nav lg:!hidden">
            @foreach ($mobileMenu as $item)
                @php($active = request()->routeIs($item['pattern']))
                <a href="{{ route($item['route']) }}" class="app-mobile-nav__item {{ $active ? 'app-mobile-nav__item-active' : '' }}">
                    @switch($item['icon'])
                        @case('home')
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-4.5v-6h-5v6H5a1 1 0 0 1-1-1v-9.5Z" stroke-linejoin="round"/>
                            </svg>
                            @break
                        @case('applications')
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M8 4.5h8m-8 5h8m-8 5h5m-8-11h12a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-13a2 2 0 0 1 2-2Z" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            @break
                        @case('installments')
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M7 3.5v3m10-3v3M4 8.5h16M6 12.5h4m4 0h4m-12 4h4m4 0h4M5 5.5h14a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-11a2 2 0 0 1 2-2Z" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            @break
                        @case('payments')
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="3" y="5" width="18" height="14" rx="3"/>
                                <path d="M3 10.5h18M7 15h3" stroke-linecap="round"/>
                            </svg>
                            @break
                        @case('profile')
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="8" r="3.5"/>
                                <path d="M5 19a7 7 0 0 1 14 0" stroke-linecap="round"/>
                            </svg>
                            @break
                    @endswitch
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
    @endif
</body>
</html>

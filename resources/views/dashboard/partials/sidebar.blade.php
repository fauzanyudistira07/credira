<aside
    class="dashboard-sidebar-wrap"
    :class="sidebarOpen ? 'translate-x-0 opacity-100' : '-translate-x-full opacity-0 xl:translate-x-0 xl:opacity-100'"
>
    @php
        $logoPath = public_path('assets/logo1.png');
        $logoUrl = asset('assets/logo1.png').(file_exists($logoPath) ? '?v='.filemtime($logoPath) : '');
    @endphp
    <div class="dashboard-sidebar">
        <a href="{{ route('dashboard') }}" class="dashboard-brand">
            <img src="{{ $logoUrl }}" alt="Credira" class="h-11 w-auto">
            <div>
                <p class="text-sm font-semibold tracking-[0.24em] text-white">CREDIRA</p>
                <p class="mt-1 text-xs text-white/58">{{ $roleLabel }}</p>
            </div>
        </a>

        <div class="dashboard-user-card">
            <p class="text-xs uppercase tracking-[0.26em] text-white/44">Akun aktif</p>
            <p class="mt-3 text-lg font-semibold text-white">{{ auth()->user()->name }}</p>
            <p class="mt-1 text-sm text-white/62">{{ auth()->user()->email }}</p>
            <div class="mt-4 inline-flex rounded-full border border-white/10 bg-white/8 px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.22em] text-orange-200">
                {{ $roleLabel }}
            </div>
        </div>

        <nav class="mt-8 space-y-2">
            @foreach ($navItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    class="dashboard-nav-link {{ request()->routeIs(...((array) $item['pattern'])) ? 'dashboard-nav-link-active' : '' }}"
                    @click="sidebarOpen = false"
                >
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="mt-auto space-y-3 pt-8">
            <a href="{{ route('profile') }}" class="dashboard-utility-link" @click="sidebarOpen = false">Profil</a>
            <a href="{{ route('home') }}" class="dashboard-utility-link" @click="sidebarOpen = false">Lihat Website</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dashboard-logout-button w-full">Logout</button>
            </form>
        </div>
    </div>
</aside>

<div
    class="fixed inset-0 z-40 bg-slate-950/55 backdrop-blur-sm xl:hidden"
    x-cloak
    x-show="sidebarOpen"
    x-transition.opacity.duration.200ms
    @click="sidebarOpen = false"
></div>

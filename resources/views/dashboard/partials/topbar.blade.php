<header class="dashboard-topbar">
    <div>
        <p class="dashboard-topbar__eyebrow">{{ $roleLabel }} Workspace</p>
        <h1 class="dashboard-topbar__title">{{ $pageTitle ?? 'Dashboard' }}</h1>
        @if (! empty($pageDescription))
            <p class="dashboard-topbar__copy">{{ $pageDescription }}</p>
        @endif
    </div>

    <div class="dashboard-topbar__actions">
        <button type="button" class="dashboard-icon-button xl:hidden" @click="sidebarOpen = true" aria-label="Buka menu">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h16" />
            </svg>
        </button>

        <div class="relative">
            <button type="button" class="dashboard-profile-button" @click="profileOpen = !profileOpen" :aria-expanded="profileOpen.toString()">
                <span class="dashboard-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                <span class="hidden text-left sm:block">
                    <span class="block text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</span>
                    <span class="block text-xs uppercase tracking-[0.22em] text-slate-500">{{ $roleLabel }}</span>
                </span>
            </button>

            <div
                class="dashboard-dropdown"
                x-cloak
                x-show="profileOpen"
                x-transition.opacity.duration.160ms
                @click.outside="profileOpen = false"
            >
                <a href="{{ route('profile') }}" class="dashboard-dropdown__link">Profil</a>
                <a href="{{ route('home') }}" class="dashboard-dropdown__link">Landing Page</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dashboard-dropdown__button">Logout</button>
                </form>
            </div>
        </div>
    </div>
</header>

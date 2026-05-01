<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($title ?? 'Dashboard').' | Credira' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="dashboard-body {{ match(($role ?? auth()->user()?->role)) { 'marketing' => 'dashboard-body-marketing', 'ceo' => 'dashboard-body-ceo', default => '' } }}" x-data="{ sidebarOpen: false, profileOpen: false }">
    @php
        $user = auth()->user();
        $role = $role ?? $user?->role;
        $roleLabel = match ($role) {
            'admin' => 'Administrator',
            'marketing' => 'Marketing',
            'ceo' => 'CEO',
            default => 'User',
        };
        $navItems = match ($role) {
            'admin' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'pattern' => 'admin.dashboard'],
                ['label' => 'User', 'route' => 'admin.users.index', 'pattern' => 'admin.users.*'],
                ['label' => 'Motor', 'route' => 'admin.motors.index', 'pattern' => 'admin.motors.*'],
                ['label' => 'Pengajuan', 'route' => 'admin.pengajuan.index', 'pattern' => 'admin.pengajuan.*'],
            ],
            'marketing' => [
                ['label' => 'Dashboard', 'route' => 'marketing.dashboard', 'pattern' => 'marketing.dashboard'],
                ['label' => 'Motor', 'route' => 'marketing.motors.index', 'pattern' => 'marketing.motors.*'],
                ['label' => 'Simulasi', 'route' => 'marketing.simulasi.index', 'pattern' => 'marketing.simulasi.*'],
                ['label' => 'Pelanggan', 'route' => 'marketing.pelanggan.index', 'pattern' => 'marketing.pelanggan.*'],
                ['label' => 'Pengajuan', 'route' => 'marketing.pengajuan.index', 'pattern' => 'marketing.pengajuan.*'],
            ],
            'ceo' => [
                ['label' => 'Dashboard', 'route' => 'ceo.dashboard', 'pattern' => ['ceo.dashboard']],
                ['label' => 'Laporan Pengajuan', 'route' => 'ceo.reports.index', 'pattern' => ['ceo.reports.*', 'ceo.laporan.*']],
                ['label' => 'Performa Marketing', 'route' => 'ceo.marketing.index', 'pattern' => ['ceo.marketing.*']],
                ['label' => 'Statistik Motor', 'route' => 'ceo.products.index', 'pattern' => ['ceo.products.*']],
                ['label' => 'Monitoring Pelanggan', 'route' => 'ceo.customers.index', 'pattern' => ['ceo.customers.*']],
            ],
            default => [],
        };
    @endphp

    @include('partials.flash')

    <div class="dashboard-shell">
        @include('dashboard.partials.sidebar', ['navItems' => $navItems, 'roleLabel' => $roleLabel])

        <div class="dashboard-main">
            @include('dashboard.partials.topbar', ['roleLabel' => $roleLabel])

            <main class="dashboard-content">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>

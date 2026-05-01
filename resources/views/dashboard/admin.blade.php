@extends('layouts.dashboard', [
    'title' => 'Dashboard Admin',
    'role' => 'admin',
    'pageTitle' => 'Dashboard Admin',
    'pageDescription' => 'Ringkasan cepat untuk memantau user, motor, pelanggan, dan pengajuan.',
])

@section('content')
    <div class="dashboard-grid">
        <section class="dashboard-hero-card">
            <div>
                <p class="dashboard-kicker">Kontrol operasional</p>
                <h2 class="dashboard-hero-title">Semua titik penting Credira dalam satu panel admin.</h2>
                <p class="dashboard-hero-copy">Tahap ini difokuskan pada fondasi akses, jadi dashboard menampilkan metrik inti dan jalur cepat ke area kerja utama.</p>
            </div>
            <div class="dashboard-hero-actions">
                <a href="{{ route('admin.users.index') }}" class="btn-accent">Kelola User</a>
                <a href="{{ route('admin.pengajuan.index') }}" class="btn-secondary">Lihat Pengajuan</a>
            </div>
        </section>

        <section class="dashboard-stats-grid">
            <article class="dashboard-stat-card">
                <p class="dashboard-stat-label">Total Users</p>
                <p class="dashboard-stat-value">{{ number_format($stats['total_users']) }}</p>
            </article>
            <article class="dashboard-stat-card">
                <p class="dashboard-stat-label">Total Motors</p>
                <p class="dashboard-stat-value">{{ number_format($stats['total_motors']) }}</p>
            </article>
            <article class="dashboard-stat-card">
                <p class="dashboard-stat-label">Total Pelanggan</p>
                <p class="dashboard-stat-value">{{ number_format($stats['total_pelanggan']) }}</p>
            </article>
            <article class="dashboard-stat-card">
                <p class="dashboard-stat-label">Total Pengajuan</p>
                <p class="dashboard-stat-value">{{ number_format($stats['total_pengajuan']) }}</p>
            </article>
        </section>

        <section class="dashboard-panel">
            <div class="dashboard-panel__head">
                <div>
                    <p class="dashboard-kicker">Recent Activity</p>
                    <h3 class="dashboard-panel__title">Pengajuan terbaru</h3>
                </div>
                <a href="{{ route('admin.pengajuan.index') }}" class="dashboard-text-link">Buka menu</a>
            </div>
            <div class="space-y-3">
                @forelse ($recentApplications as $application)
                    <div class="dashboard-list-row">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $application->kode_pengajuan ?? 'Pengajuan #'.$application->id }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $application->pelanggan?->display_name ?? 'Pelanggan belum terhubung' }} • {{ $application->motor?->nama_motor ?? 'Motor belum terhubung' }}</p>
                        </div>
                        <span class="dashboard-badge">{{ $application->status_badge }}</span>
                    </div>
                @empty
                    <div class="dashboard-empty-state">
                        Belum ada pengajuan untuk ditampilkan.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="dashboard-panel">
            <div class="dashboard-panel__head">
                <div>
                    <p class="dashboard-kicker">Quick Links</p>
                    <h3 class="dashboard-panel__title">Aksi cepat admin</h3>
                </div>
            </div>
            <div class="dashboard-quick-links">
                <a href="{{ route('admin.users.index') }}" class="dashboard-quick-link">User Management</a>
                <a href="{{ route('admin.motors.index') }}" class="dashboard-quick-link">Motor Catalog</a>
                <a href="{{ route('admin.pengajuan.index') }}" class="dashboard-quick-link">Review Pengajuan</a>
                <a href="{{ route('profile') }}" class="dashboard-quick-link">Profil Akun</a>
            </div>
        </section>
    </div>
@endsection

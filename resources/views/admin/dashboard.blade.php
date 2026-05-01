@extends('layouts.admin', [
    'title' => 'Dashboard Admin',
    'heading' => 'Dashboard Admin',
    'subheading' => 'Ringkasan sistem inti untuk user, motor, pelanggan, dan seluruh pengajuan dari semua marketing.',
])

@section('content')
    <div class="space-y-6 lg:space-y-7">
        <section class="grid gap-6 xl:grid-cols-[1.18fr_0.82fr]" data-reveal>
            <section class="admin-hero-panel">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <span class="admin-eyebrow">Control Room</span>
                        <h2 class="admin-hero-panel__title">Workspace admin Credira untuk menjaga data inti tetap bersih, status pengajuan tetap bergerak, dan operasional tetap terkendali.</h2>
                        <p class="admin-hero-panel__copy">Semua metrik diambil dari schema existing dan diringkas agar admin bisa langsung lanjut ke aksi penting tanpa template generik.</p>
                    </div>
                    <div class="admin-hero-actions">
                        <a href="{{ route('admin.users.create') }}" class="btn-accent">Tambah User</a>
                        <a href="{{ route('admin.motors.create') }}" class="admin-utility-button">Tambah Motor</a>
                        <a href="{{ route('admin.pengajuan.index') }}" class="admin-utility-button">Review Pengajuan</a>
                    </div>
                </div>

                <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="admin-highlight-stat"><p class="admin-highlight-stat__label">Total Users</p><p class="admin-highlight-stat__value">{{ number_format($stats['total_users']) }}</p><p class="admin-highlight-stat__copy">Akun seluruh role di sistem.</p></div>
                    <div class="admin-highlight-stat"><p class="admin-highlight-stat__label">Marketing</p><p class="admin-highlight-stat__value">{{ number_format($stats['total_marketing']) }}</p><p class="admin-highlight-stat__copy">Tim marketing aktif yang memiliki akses dashboard.</p></div>
                    <div class="admin-highlight-stat"><p class="admin-highlight-stat__label">Motors</p><p class="admin-highlight-stat__value">{{ number_format($stats['total_motors']) }}</p><p class="admin-highlight-stat__copy">Master motor yang bisa dipakai untuk penjualan.</p></div>
                    <div class="admin-highlight-stat"><p class="admin-highlight-stat__label">Pelanggan</p><p class="admin-highlight-stat__value">{{ number_format($stats['total_pelanggan']) }}</p><p class="admin-highlight-stat__copy">Data pelanggan yang sudah masuk ke sistem.</p></div>
                </div>
            </section>

            <aside class="admin-focus-panel">
                <p class="admin-eyebrow">Queue Pulse</p>
                <div class="mt-6 space-y-5">
                    <div>
                        <div class="admin-focus-panel__metric"><span>Pending ratio</span><strong>{{ $stats['pending_ratio'] }}%</strong></div>
                        <div class="mt-3 admin-progress-rail"><div class="admin-progress-fill" style="width: {{ $stats['pending_ratio'] }}%"></div></div>
                    </div>
                    <div>
                        <div class="admin-focus-panel__metric"><span>Pengajuan dalam antrean</span><strong>{{ number_format($queueSummary['queue']) }}</strong></div>
                        <div class="mt-3 admin-progress-rail"><div class="admin-progress-fill admin-progress-fill-soft" style="width: {{ min(100, max(12, $queueSummary['queue'] * 8)) }}%"></div></div>
                    </div>
                </div>

                <div class="mt-7 grid gap-3">
                    <div class="admin-focus-card">
                        <p class="text-xs uppercase tracking-[0.28em] text-orange-200/75">Pipeline value</p>
                        <p class="mt-3 text-2xl font-semibold text-white">Rp {{ number_format($queueSummary['value'], 0, ',', '.') }}</p>
                        <p class="mt-2 text-sm leading-7 text-slate-300">Akumulasi total bayar seluruh pengajuan existing.</p>
                    </div>
                    <div class="admin-focus-card">
                        <p class="text-xs uppercase tracking-[0.28em] text-orange-200/75">Average DP</p>
                        <p class="mt-3 text-2xl font-semibold text-white">Rp {{ number_format($queueSummary['average_dp'], 0, ',', '.') }}</p>
                        <p class="mt-2 text-sm leading-7 text-slate-300">Rata-rata DP dari pengajuan yang sudah tercatat.</p>
                    </div>
                </div>
            </aside>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4" data-reveal>
            <article class="admin-secondary-stat"><p class="admin-secondary-stat__label">Total Pengajuan</p><p class="admin-secondary-stat__value">{{ number_format($stats['total_pengajuan']) }}</p><p class="admin-secondary-stat__copy">Seluruh pengajuan dari semua marketing.</p></article>
            <article class="admin-secondary-stat"><p class="admin-secondary-stat__label">Pending</p><p class="admin-secondary-stat__value">{{ number_format($stats['total_pending']) }}</p><p class="admin-secondary-stat__copy">Butuh tindakan awal admin.</p></article>
            <article class="admin-secondary-stat"><p class="admin-secondary-stat__label">Approved</p><p class="admin-secondary-stat__value">{{ number_format($stats['total_approved']) }}</p><p class="admin-secondary-stat__copy">Sudah masuk approval bucket.</p></article>
            <article class="admin-secondary-stat"><p class="admin-secondary-stat__label">Rejected</p><p class="admin-secondary-stat__value">{{ number_format($stats['total_rejected']) }}</p><p class="admin-secondary-stat__copy">Pengajuan yang ditolak atau dibatalkan.</p></article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]" data-reveal>
            <section class="admin-stream-panel">
                <div class="dashboard-panel__head">
                    <div>
                        <p class="admin-eyebrow">Recent Pengajuan</p>
                        <h3 class="mt-3 text-2xl font-semibold text-white">Pengajuan terbaru</h3>
                    </div>
                    <a href="{{ route('admin.pengajuan.index') }}" class="admin-text-link">Lihat semua</a>
                </div>
                <div class="grid gap-3">
                    @forelse ($recentPengajuan as $item)
                        <a href="{{ route('admin.pengajuan.show', $item) }}" class="admin-activity-card">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-semibold text-white">{{ $item->kode_pengajuan }}</p>
                                    <p class="mt-1 text-sm text-slate-300">{{ $item->pelanggan?->display_name ?? '-' }} &middot; {{ $item->motor?->nama_motor ?? '-' }}</p>
                                    <p class="mt-2 text-sm text-slate-400">{{ $item->marketingOwner?->name ?? '-' }} &middot; {{ optional($item->tgl_pengajuan)->format('d M Y') ?: $item->created_at?->format('d M Y') }}</p>
                                </div>
                                <x-status-badge :status="$item->status_pengajuan" class="!border-white/10 !bg-white/8 !text-orange-100" />
                            </div>
                        </a>
                    @empty
                        <p class="admin-copy">Belum ada pengajuan terbaru yang tercatat.</p>
                    @endforelse
                </div>
            </section>

            <section class="admin-stream-panel">
                <div class="dashboard-panel__head">
                    <div>
                        <p class="admin-eyebrow">Status Distribution</p>
                        <h3 class="mt-3 text-2xl font-semibold text-white">Ringkasan visual sederhana</h3>
                    </div>
                </div>
                <div class="space-y-4">
                    @foreach ($statusDistribution as $item)
                        <div class="admin-metric-card">
                            <div class="flex items-center justify-between gap-4">
                                <p class="font-semibold text-white">{{ $item['label'] }}</p>
                                <p class="text-sm font-semibold text-orange-200">{{ number_format($item['value']) }}</p>
                            </div>
                            <div class="mt-3 admin-progress-rail">
                                <div class="admin-progress-fill" style="width: {{ $stats['total_pengajuan'] > 0 ? max(8, round(($item['value'] / $stats['total_pengajuan']) * 100)) : 8 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.96fr_1.04fr]" data-reveal>
            <section class="admin-stream-panel">
                <div class="dashboard-panel__head">
                    <div>
                        <p class="admin-eyebrow">Marketing Activity</p>
                        <h3 class="mt-3 text-2xl font-semibold text-white">Marketing paling aktif</h3>
                    </div>
                </div>
                <div class="grid gap-3">
                    @forelse ($marketingPerformance as $marketing)
                        <div class="admin-activity-card">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-white">{{ $marketing->name }}</p>
                                    <p class="mt-1 text-sm text-slate-400">Owner pengajuan aktif di sistem.</p>
                                </div>
                                <span class="inline-flex items-center rounded-full border border-white/10 bg-white/8 px-4 py-2 text-sm font-semibold text-orange-100">{{ number_format($marketing->total_pengajuan) }} pengajuan</span>
                            </div>
                        </div>
                    @empty
                        <p class="admin-copy">Belum ada aktivitas marketing yang tercatat.</p>
                    @endforelse
                </div>
            </section>

            <section class="admin-stream-panel">
                <div class="dashboard-panel__head">
                    <div>
                        <p class="admin-eyebrow">Admin Notifications</p>
                        <h3 class="mt-3 text-2xl font-semibold text-white">Feed notifikasi sederhana</h3>
                    </div>
                </div>
                <div class="grid gap-3">
                    @forelse ($recentNotifications as $notification)
                        <div class="admin-activity-card">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-white">{{ $notification->title }}</p>
                                    <p class="mt-2 text-sm leading-7 text-slate-300">{{ $notification->message }}</p>
                                </div>
                                <x-status-badge :status="$notification->type" class="!border-white/10 !bg-white/8 !text-orange-100" />
                            </div>
                        </div>
                    @empty
                        <p class="admin-copy">Belum ada notifikasi pada tabel existing.</p>
                    @endforelse
                </div>
            </section>
        </section>
    </div>
@endsection

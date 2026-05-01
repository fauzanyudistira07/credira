@extends('layouts.dashboard', [
    'title' => 'Dashboard Marketing',
    'role' => 'marketing',
    'pageTitle' => 'Dashboard Marketing',
    'pageDescription' => 'Workspace ringkas untuk memantau pelanggan, katalog motor, dan pipeline pengajuan milik Anda.',
])

@section('content')
    <div class="marketing-page">
        <section class="marketing-hero">
            <div class="grid gap-8 xl:grid-cols-[minmax(0,1.08fr)_minmax(320px,0.92fr)] xl:items-end">
                <div>
                    <p class="marketing-hero__eyebrow">Marketing Workspace</p>
                    <h2 class="marketing-hero__title">Kelola prospek, pilih motor, dan ubah lead menjadi pengajuan aktif.</h2>
                    <p class="marketing-hero__copy">Area marketing kini memakai ownership yang tegas, jadi pelanggan, pengajuan, dan pipeline yang tampil hanya milik akun Anda sendiri.</p>
                </div>
                <div class="marketing-soft-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/45">Fokus hari ini</p>
                    <p class="mt-2 text-lg font-semibold text-white">Prospek aktif, katalog motor, dan follow up pengajuan.</p>
                    <p class="mt-2 text-sm leading-6 text-white/65">Masuk ke pelanggan untuk tambah lead baru atau langsung buat aplikasi dari unit yang siap ditawarkan.</p>
                </div>
            </div>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('marketing.pelanggan.index') }}" class="btn-accent">Lihat Pelanggan</a>
                <a href="{{ route('marketing.pengajuan.create') }}" class="btn-outline-light">Buat Pengajuan</a>
            </div>

            <div class="marketing-hero__stats">
                <article class="marketing-hero__stat">
                    <p class="marketing-hero__stat-label">Pelanggan Saya</p>
                    <p class="marketing-hero__stat-value">{{ number_format($stats['total_pelanggan']) }}</p>
                </article>
                <article class="marketing-hero__stat">
                    <p class="marketing-hero__stat-label">Pengajuan Saya</p>
                    <p class="marketing-hero__stat-value">{{ number_format($stats['total_pengajuan']) }}</p>
                </article>
                <article class="marketing-hero__stat">
                    <p class="marketing-hero__stat-label">Pending</p>
                    <p class="marketing-hero__stat-value">{{ number_format($stats['pending_pengajuan']) }}</p>
                </article>
                <article class="marketing-hero__stat">
                    <p class="marketing-hero__stat-label">Review</p>
                    <p class="marketing-hero__stat-value">{{ number_format($stats['review_pengajuan']) }}</p>
                </article>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
            <section class="marketing-surface">
                <div class="dashboard-panel__head">
                    <div>
                        <p class="dashboard-kicker">Pipeline</p>
                        <h3 class="marketing-section-title">Pengajuan terakhir</h3>
                    </div>
                    <a href="{{ route('marketing.pengajuan.index') }}" class="dashboard-text-link">Lihat semua</a>
                </div>
                <div class="space-y-3">
                    @forelse ($recentApplications as $application)
                        <div class="dashboard-list-row">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $application->kode_pengajuan ?? 'Pengajuan #'.$application->id }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $application->pelanggan?->display_name ?? 'Pelanggan belum terhubung' }} - {{ $application->motor?->nama_motor ?? 'Motor belum terhubung' }}</p>
                                <p class="mt-2 text-xs uppercase tracking-[0.2em] text-slate-400">{{ $application->jenisCicilan?->durasi_bulan ?? '-' }} bulan</p>
                            </div>
                            <div class="flex flex-col items-start gap-3 sm:items-end">
                                <x-status-badge :status="$application->status_pengajuan" />
                                <a href="{{ route('marketing.pengajuan.show', $application) }}" class="dashboard-text-link">Buka detail</a>
                            </div>
                        </div>
                    @empty
                        <div class="dashboard-empty-state">
                            Belum ada pengajuan yang terhubung ke akun marketing ini.
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="marketing-surface">
                <div class="dashboard-panel__head">
                    <div>
                        <p class="dashboard-kicker">Quick Actions</p>
                        <h3 class="marketing-section-title">Aksi cepat marketing</h3>
                    </div>
                </div>
                <div class="dashboard-quick-links">
                    <a href="{{ route('marketing.motors.index') }}" class="dashboard-quick-link">Lihat Motor</a>
                    <a href="{{ route('marketing.pelanggan.create') }}" class="dashboard-quick-link">Tambah Pelanggan</a>
                    <a href="{{ route('marketing.pengajuan.create') }}" class="dashboard-quick-link">Buat Pengajuan</a>
                    <a href="{{ route('marketing.pelanggan.index') }}" class="dashboard-quick-link">Kelola Pelanggan</a>
                </div>

                <div class="marketing-inline-note mt-6">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-orange-500">Workflow</p>
                    <div class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
                        <p>1. Pilih motor aktif dari katalog marketing.</p>
                        <p>2. Tambah atau rapikan data pelanggan milik Anda.</p>
                        <p>3. Buat pengajuan kredit dan upload dokumen inti.</p>
                        <p>4. Pantau status dari list dan detail pengajuan.</p>
                    </div>
                </div>
            </section>
        </div>

        <section class="marketing-surface">
            <div class="dashboard-panel__head">
                <div>
                    <p class="dashboard-kicker">Highlights</p>
                    <h3 class="marketing-section-title">Apa yang bisa dilakukan user marketing sekarang</h3>
                </div>
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                <article class="marketing-soft-card">
                    <p class="text-sm font-semibold text-slate-950">Katalog motor aktif</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Lihat unit aktif, filter merk/jenis, lalu langsung masuk ke detail motor untuk mulai pengajuan.</p>
                </article>
                <article class="marketing-soft-card">
                    <p class="text-sm font-semibold text-slate-950">Pelanggan tersegmentasi</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Setiap marketing hanya melihat pelanggan miliknya sendiri, lengkap dengan detail identitas dan riwayat pengajuan.</p>
                </article>
                <article class="marketing-soft-card">
                    <p class="text-sm font-semibold text-slate-950">Flow pengajuan hidup</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Pengajuan menyimpan rincian finansial, dokumen, log status, serta ownership marketing secara konsisten.</p>
                </article>
            </div>
        </section>
    </div>
@endsection

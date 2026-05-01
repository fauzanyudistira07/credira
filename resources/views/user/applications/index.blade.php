@extends('layouts.user', [
    'title' => 'Pengajuan Kredit',
    'heading' => 'Pengajuan Kredit Motor',
    'subheading' => 'Kelola seluruh pengajuan, cek status verifikasi, dan lanjutkan proses pembiayaan dari satu halaman.',
])

@section('content')
    <div x-data="asyncList({ skeletonCount: 4 })" class="grid gap-6">
    <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <div class="app-panel-dark">
            <p class="app-kicker">Pusat Pengajuan</p>
            <h2 class="mt-4 text-3xl font-semibold leading-tight sm:text-4xl">Semua permohonan kredit motor Anda tersusun rapi dan mudah dilanjutkan.</h2>
            <p class="mt-4 max-w-2xl text-sm leading-7 text-white/72">Dari draft, upload dokumen, verifikasi, sampai kontrak aktif, seluruh pengajuan tampil sebagai alur pembiayaan yang jelas dan bisa Anda cek kapan saja.</p>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Total data</p>
                    <p class="mt-3 text-3xl font-semibold text-white">{{ $applications->total() }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Filter aktif</p>
                    <p class="mt-3 text-base font-semibold text-white">{{ $currentStatus ? str($currentStatus)->replace('_', ' ')->title() : 'Semua status' }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Aksi cepat</p>
                    <a href="{{ route('user.applications.create') }}" class="btn-outline-light mt-3 w-full">Ajukan Sekarang</a>
                </div>
            </div>
        </div>

        <div class="app-panel">
            <p class="app-kicker">Filter Pengajuan</p>
            <h2 class="mt-3 text-2xl font-semibold text-slate-950">Temukan status pengajuan yang ingin Anda cek</h2>
            <form method="GET" class="mt-6 grid gap-4" x-ref="form">
                <div>
                    <label class="field-label">Status pengajuan</label>
                    <select name="status" class="field-select" data-chip-label="Status">
                        <option value="">Semua status</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status['value'] }}" @selected($currentStatus === $status['value'])>{{ $status['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="btn-primary">Terapkan Filter</button>
                    <a href="{{ route('user.applications.index') }}" class="btn-secondary">Reset</a>
                </div>
            </form>
            <div class="mt-4 flex flex-wrap gap-2" x-ref="chips"></div>

            <div class="mt-6 rounded-[1.6rem] border border-[#ece4db] bg-[#fffaf6] p-5 text-sm leading-7 text-slate-600">
                Halaman ini dirancang sebagai pusat aktivitas kredit motor user: Anda bisa memeriksa tiap pengajuan, melanjutkan edit draft, membuka dokumen, atau mengecek status approval tanpa berpindah alur.
            </div>
        </div>
    </section>

    <section class="app-panel">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="app-kicker">Daftar Pengajuan</p>
                <h2 class="mt-3 text-2xl font-semibold text-slate-950">Progress kredit motor yang tersimpan di akun Anda</h2>
            </div>
            <a href="{{ route('user.applications.create') }}" class="btn-primary">Buat Pengajuan</a>
        </div>
        <div class="mt-6 grid gap-4" x-ref="results" data-async-results>
            @forelse ($applications as $application)
                <article class="app-list-card">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs uppercase tracking-[0.24em] text-slate-500">{{ $application->kode_pengajuan }}</p>
                            <h3 class="mt-3 text-xl font-semibold text-slate-950">{{ $application->motor->nama_motor }}</h3>
                            <p class="mt-2 text-sm text-slate-600">{{ $application->jenisCicilan->nama_cicilan }} &middot; DP Rp {{ number_format($application->dp, 0, ',', '.') }}</p>
                            <p class="mt-2 text-sm text-slate-500">Dibuat {{ $application->created_at->translatedFormat('d F Y') }}</p>
                        </div>
                        <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center">
                            <x-status-badge :status="$application->status_pengajuan" />
                            <a href="{{ route('user.applications.show', $application) }}" class="btn-ghost">Lihat Detail</a>
                        </div>
                    </div>
                </article>
            @empty
                <x-empty-state
                    title="Belum ada pengajuan"
                    description="Mulai pengajuan pertama untuk melihat progres pembiayaan, dokumen, dan jadwal angsuran di akun Anda."
                    action-label="Ajukan Sekarang"
                    action-href="{{ route('user.applications.create') }}"
                />
            @endforelse
        </div>

        <div class="mt-6" x-ref="pagination" data-async-pagination>{{ $applications->links() }}</div>
    </section>
    </div>
@endsection

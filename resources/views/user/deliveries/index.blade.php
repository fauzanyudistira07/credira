@extends('layouts.user', [
    'title' => 'Pengiriman',
    'heading' => 'Pengiriman Motor',
    'subheading' => 'Lacak semua unit motor yang sedang atau sudah dikirim ke alamat Anda.',
])

@section('content')
    <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <div class="app-panel-dark">
            <p class="app-kicker">Pelacakan Pengiriman</p>
            <h2 class="mt-4 text-3xl font-semibold leading-tight text-white sm:text-4xl">Status pengiriman unit tampil sebagai bagian alami dari aplikasi kredit motor.</h2>
            <p class="mt-4 max-w-2xl text-sm leading-7 text-white/72">Setelah kontrak aktif, informasi invoice, kurir, jadwal antar, dan status penerimaan akan muncul di sini tanpa perlu keluar dari akun user.</p>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Riwayat</p>
                    <p class="mt-3 text-3xl font-semibold text-white">{{ $deliveries->total() }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Konteks</p>
                    <p class="mt-3 text-base font-semibold text-white">Pengiriman kontrak aktif</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Aksi cepat</p>
                    <a href="{{ route('user.applications.index') }}" class="btn-outline-light mt-3 w-full">Lihat Pengajuan</a>
                </div>
            </div>
        </div>

        <div class="app-panel">
            <p class="app-kicker">Tahap Pengiriman</p>
            <div class="mt-6 grid gap-3">
                @foreach ([
                    'menunggu_pengiriman' => 'Unit menunggu jadwal pengiriman',
                    'disiapkan' => 'Unit dan dokumen pengiriman sedang disiapkan',
                    'dikirim' => 'Motor sedang dikirim ke alamat tujuan',
                    'sampai_tujuan' => 'Unit sudah diterima oleh nasabah',
                ] as $label => $text)
                    <div class="app-list-card-muted">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-semibold text-slate-950">{{ str($label)->replace('_', ' ')->title() }}</p>
                            <x-status-badge :status="$label" />
                        </div>
                        <p class="mt-2 text-sm leading-7 text-slate-600">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mt-6 app-panel">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="app-kicker">Daftar Pengiriman</p>
                <h2 class="mt-3 text-2xl font-semibold text-slate-950">Unit motor yang sudah masuk ke proses distribusi</h2>
            </div>
        </div>

        <div class="mt-6 grid gap-4">
            @forelse ($deliveries as $delivery)
                <article class="app-list-card">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs uppercase tracking-[0.24em] text-slate-500">{{ $delivery->invoice }}</p>
                            <h3 class="mt-3 text-xl font-semibold text-slate-950">{{ $delivery->application->motor->nama_motor }}</h3>
                            <p class="mt-2 text-sm text-slate-600">{{ $delivery->nama_kurir ?: 'Kurir belum ditentukan' }} @if($delivery->telpon_kurir) &middot; {{ $delivery->telpon_kurir }} @endif</p>
                        </div>
                        <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center">
                            <x-status-badge :status="$delivery->status_kirim" />
                            <a href="{{ route('user.deliveries.show', $delivery) }}" class="btn-ghost">Detail</a>
                        </div>
                    </div>
                </article>
            @empty
                <x-empty-state
                    title="Belum ada pengiriman"
                    description="Informasi pengiriman akan muncul setelah kontrak pembiayaan masuk tahap distribusi unit motor."
                    action-label="Lihat Pengajuan"
                    action-href="{{ route('user.applications.index') }}"
                />
            @endforelse
        </div>

        <div class="mt-6">{{ $deliveries->links() }}</div>
    </section>
@endsection

@extends('layouts.user', [
    'title' => 'Jadwal Angsuran',
    'heading' => 'Jadwal Angsuran',
    'subheading' => 'Pantau semua tagihan kredit motor, jatuh tempo, dan status pembayaran bulanan Anda.',
])

@section('content')
    <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <div class="app-panel-dark">
            <p class="app-kicker">Pusat Angsuran</p>
            <h2 class="mt-4 text-3xl font-semibold leading-tight text-white sm:text-4xl">Semua tagihan kredit motor tertata sebagai jadwal yang mudah dipantau.</h2>
            <p class="mt-4 max-w-2xl text-sm leading-7 text-white/72">Gunakan halaman ini untuk melihat jatuh tempo bulanan, mendeteksi tagihan telat, dan menuju checkout Midtrans hanya dengan satu langkah.</p>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Total jadwal</p>
                    <p class="mt-3 text-3xl font-semibold text-white">{{ $installments->total() }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Status aktif</p>
                    <p class="mt-3 text-base font-semibold text-white">{{ $currentStatus ? str($currentStatus)->replace('_', ' ')->title() : 'Semua tagihan' }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Aksi cepat</p>
                    <a href="{{ route('user.payments.create') }}" class="btn-outline-light mt-3 w-full">Bayar Sekarang</a>
                </div>
            </div>
        </div>

        <div class="app-panel">
            <p class="app-kicker">Filter Tagihan</p>
            <h2 class="mt-3 text-2xl font-semibold text-slate-950">Pilih status angsuran yang ingin dilihat</h2>
            <form method="GET" class="mt-6 grid gap-4">
                <div>
                    <label class="field-label">Status pembayaran</label>
                    <select name="status" class="field-select" onchange="this.form.submit()">
                        <option value="">Semua status</option>
                        @foreach (['belum_bayar', 'menunggu_verifikasi', 'sudah_bayar', 'telat', 'gagal_verifikasi'] as $status)
                            <option value="{{ $status }}" @selected($currentStatus === $status)>{{ str($status)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>
                </div>
                <a href="{{ route('user.installments.index') }}" class="btn-secondary w-fit">Reset Filter</a>
            </form>

            <div class="mt-6 rounded-[1.6rem] border border-[#ece4db] bg-[#fffaf6] p-5 text-sm leading-7 text-slate-600">
                Dari sini Anda bisa langsung melihat angsuran yang belum dibayar, yang sedang diverifikasi, hingga yang sudah lunas. Detail nominal, denda, dan riwayat transfer ada di halaman detail tiap angsuran.
            </div>
        </div>
    </section>

    <section class="mt-6 app-panel">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="app-kicker">Daftar Angsuran</p>
                <h2 class="mt-3 text-2xl font-semibold text-slate-950">Tagihan bulanan berdasarkan kontrak kredit motor Anda</h2>
            </div>
            <a href="{{ route('user.payments.create') }}" class="btn-primary">Bayar Angsuran</a>
        </div>

        <div class="mt-6 grid gap-4">
            @forelse ($installments as $installment)
                <article class="app-list-card">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs uppercase tracking-[0.24em] text-slate-500">{{ $installment->application->kode_pengajuan }}</p>
                            <h3 class="mt-3 text-xl font-semibold text-slate-950">{{ $installment->application->motor->nama_motor }}</h3>
                            <p class="mt-2 text-sm text-slate-600">Angsuran ke-{{ $installment->angsuran_ke }} &middot; Jatuh tempo {{ $installment->tanggal_jatuh_tempo->translatedFormat('d F Y') }}</p>
                        </div>
                        <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center">
                            <p class="text-lg font-semibold text-slate-950">Rp {{ number_format($installment->total_tagihan, 0, ',', '.') }}</p>
                            <x-status-badge :status="$installment->status_pembayaran" />
                            <a href="{{ route('user.installments.show', $installment) }}" class="btn-ghost">Detail</a>
                        </div>
                    </div>
                </article>
            @empty
                <x-empty-state
                    title="Belum ada jadwal angsuran"
                    description="Setelah kontrak pembiayaan aktif, jadwal angsuran bulanan Anda akan tampil di sini."
                    action-label="Lihat Pengajuan"
                    action-href="{{ route('user.applications.index') }}"
                />
            @endforelse
        </div>

        <div class="mt-6">{{ $installments->links() }}</div>
    </section>
@endsection

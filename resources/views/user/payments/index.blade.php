@extends('layouts.user', [
    'title' => 'Pembayaran',
    'heading' => 'Pembayaran Angsuran',
    'subheading' => 'Riwayat upload bukti transfer dan status verifikasi seluruh pembayaran kredit motor Anda.',
])

@section('content')
    <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <div class="app-panel-dark">
            <p class="app-kicker">Pusat Pembayaran</p>
            <h2 class="mt-4 text-3xl font-semibold leading-tight text-white sm:text-4xl">Upload bukti transfer dan pantau validasi pembayaran dalam satu aplikasi.</h2>
            <p class="mt-4 max-w-2xl text-sm leading-7 text-white/72">Setelah transfer dilakukan, cukup unggah bukti pembayaran di sini. Sistem akan menyimpan riwayat pembayaran dan menampilkan status verifikasinya secara jelas.</p>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Riwayat</p>
                    <p class="mt-3 text-3xl font-semibold text-white">{{ $payments->total() }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Status umum</p>
                    <p class="mt-3 text-base font-semibold text-white">Semua pembayaran</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Aksi cepat</p>
                    <a href="{{ route('user.payments.create') }}" class="btn-outline-light mt-3 w-full">Upload Bukti</a>
                </div>
            </div>
        </div>

        <div class="app-panel">
            <p class="app-kicker">Alur Pembayaran</p>
            <h2 class="mt-3 text-2xl font-semibold text-slate-950">Bagaimana proses pembayaran di Credira</h2>
            <div class="mt-6 grid gap-3">
                <div class="app-list-card-muted">
                    <p class="font-semibold text-slate-950">1. Pilih tagihan aktif</p>
                    <p class="mt-2 text-sm leading-7 text-slate-600">Ambil angsuran yang ingin dibayar langsung dari halaman upload pembayaran.</p>
                </div>
                <div class="app-list-card-muted">
                    <p class="font-semibold text-slate-950">2. Transfer sesuai metode bayar</p>
                    <p class="mt-2 text-sm leading-7 text-slate-600">Gunakan rekening tujuan yang tersedia dan simpan bukti transfer Anda.</p>
                </div>
                <div class="app-list-card-muted">
                    <p class="font-semibold text-slate-950">3. Tunggu verifikasi</p>
                    <p class="mt-2 text-sm leading-7 text-slate-600">Status akan berubah dari pending menjadi valid atau ditolak sesuai hasil pengecekan admin.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-6 app-panel">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="app-kicker">Riwayat Pembayaran</p>
                <h2 class="mt-3 text-2xl font-semibold text-slate-950">Semua bukti transfer yang pernah Anda unggah</h2>
            </div>
            <a href="{{ route('user.payments.create') }}" class="btn-primary">Upload Bukti</a>
        </div>

        <div class="mt-6 grid gap-4">
            @forelse ($payments as $payment)
                <article class="app-list-card">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs uppercase tracking-[0.24em] text-slate-500">{{ $payment->kode_pembayaran }}</p>
                            <h3 class="mt-3 text-xl font-semibold text-slate-950">{{ $payment->installment->application->motor->nama_motor }}</h3>
                            <p class="mt-2 text-sm text-slate-600">Angsuran ke-{{ $payment->installment->angsuran_ke }} &middot; {{ $payment->tanggal_bayar->translatedFormat('d F Y') }}</p>
                        </div>
                        <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center">
                            <p class="text-lg font-semibold text-slate-950">Rp {{ number_format($payment->nominal_bayar, 0, ',', '.') }}</p>
                            <x-status-badge :status="$payment->status_verifikasi" />
                            <a href="{{ route('user.payments.show', $payment) }}" class="btn-ghost">Detail</a>
                        </div>
                    </div>
                </article>
            @empty
                <x-empty-state
                    title="Belum ada riwayat pembayaran"
                    description="Setelah Anda mengunggah bukti transfer angsuran, data pembayaran akan tampil di sini."
                    action-label="Upload Pembayaran"
                    action-href="{{ route('user.payments.create') }}"
                />
            @endforelse
        </div>

        <div class="mt-6">{{ $payments->links() }}</div>
    </section>
@endsection

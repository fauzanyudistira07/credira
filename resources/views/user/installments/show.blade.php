@extends('layouts.user', [
    'title' => 'Detail Angsuran',
    'heading' => 'Detail Angsuran',
    'subheading' => 'Rincian tagihan bulanan, denda, dan riwayat pembayaran untuk kontrak motor Anda.',
])

@section('content')
    <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <div class="app-panel-dark">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="app-kicker">Detail Tagihan</p>
                    <p class="mt-3 text-xs uppercase tracking-[0.28em] text-white/50">{{ $installment->application->kode_pengajuan }}</p>
                    <h2 class="mt-3 text-3xl font-semibold text-white">{{ $installment->application->motor->nama_motor }}</h2>
                    <p class="mt-3 text-sm leading-7 text-white/70">Angsuran ke-{{ $installment->angsuran_ke }} untuk kontrak pembiayaan ini. Anda bisa melihat nominal, denda, dan semua riwayat transaksi Midtrans.</p>
                </div>
                <x-status-badge :status="$installment->status_pembayaran" />
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Jatuh tempo</p>
                    <p class="mt-3 text-lg font-semibold text-white">{{ $installment->tanggal_jatuh_tempo->translatedFormat('d M Y') }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Nominal</p>
                    <p class="mt-3 text-lg font-semibold text-white">Rp {{ number_format($installment->nominal_angsuran, 0, ',', '.') }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Denda</p>
                    <p class="mt-3 text-lg font-semibold text-white">Rp {{ number_format($installment->denda, 0, ',', '.') }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Total</p>
                    <p class="mt-3 text-lg font-semibold text-white">Rp {{ number_format($installment->total_tagihan, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('user.payments.create', ['installment' => $installment->id]) }}" class="btn-accent">Bayar Sekarang</a>
                <a href="{{ route('user.installments.index') }}" class="btn-outline-light">Kembali ke Jadwal</a>
            </div>
        </div>

        <div class="app-panel">
            <p class="app-kicker">Riwayat Pembayaran</p>
            <h2 class="mt-3 text-2xl font-semibold text-slate-950">Bukti transfer yang pernah masuk untuk angsuran ini</h2>

            <div class="mt-6 grid gap-4">
                @forelse ($installment->payments as $payment)
                    <article class="app-list-card-muted">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-[0.24em] text-slate-500">{{ $payment->kode_pembayaran }}</p>
                                <p class="mt-3 font-semibold text-slate-950">Rp {{ number_format($payment->nominal_bayar, 0, ',', '.') }}</p>
                                <p class="mt-2 text-sm text-slate-600">{{ $payment->tanggal_bayar->translatedFormat('d F Y') }} @if($payment->metodeBayar) &middot; {{ $payment->metodeBayar->metode_pembayaran }} @endif</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <x-status-badge :status="$payment->status_verifikasi" />
                                <a href="{{ route('user.payments.show', $payment) }}" class="btn-ghost">Detail</a>
                            </div>
                        </div>
                    </article>
                @empty
                <x-empty-state
                    title="Belum ada pembayaran"
                    description="Buat transaksi Midtrans untuk menyelesaikan pembayaran angsuran ini."
                    action-label="Bayar Angsuran"
                    action-href="{{ route('user.payments.create', ['installment' => $installment->id]) }}"
                />
                @endforelse
            </div>
        </div>
    </section>
@endsection

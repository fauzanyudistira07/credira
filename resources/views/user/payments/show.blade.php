@extends('layouts.user', [
    'title' => 'Detail Pembayaran',
    'heading' => 'Detail Pembayaran',
    'subheading' => 'Pantau status pembayaran Midtrans dan detail transaksi angsuran Anda.',
])

@php
    $proofUrl = $payment->proof_url;
    $isPdfProof = $proofUrl ? \Illuminate\Support\Str::endsWith(strtolower($proofUrl), '.pdf') : false;
@endphp

@section('content')
    <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <div class="app-panel-dark">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="app-kicker">Detail Transaksi</p>
                    <p class="mt-3 text-xs uppercase tracking-[0.28em] text-white/50">{{ $payment->kode_pembayaran }}</p>
                    <h2 class="mt-3 text-3xl font-semibold text-white">{{ $payment->installment->application->motor->nama_motor }}</h2>
                    <p class="mt-3 text-sm leading-7 text-white/70">Pembayaran untuk angsuran ke-{{ $payment->installment->angsuran_ke }}. Status akan diperbarui otomatis berdasarkan notifikasi Midtrans.</p>
                </div>
                <x-status-badge :status="$payment->status_verifikasi" />
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Nominal</p>
                    <p class="mt-3 text-lg font-semibold text-white">Rp {{ number_format($payment->nominal_bayar, 0, ',', '.') }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Tanggal bayar</p>
                    <p class="mt-3 text-lg font-semibold text-white">{{ optional($payment->tanggal_bayar)->translatedFormat('d M Y') ?? '-' }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Metode</p>
                    <p class="mt-3 text-sm font-semibold text-white">{{ $payment->metode_bayar ?? 'Midtrans Snap' }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Order ID</p>
                    <p class="mt-3 text-xs font-semibold text-white">{{ $payment->midtrans_order_id ?? '-' }}</p>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('user.payments.index') }}" class="btn-outline-light">Kembali ke Riwayat</a>
                <a href="{{ route('user.installments.show', $payment->installment) }}" class="btn-outline-light">Lihat Angsuran</a>
                @if ($midtransUrl)
                    <a href="{{ $midtransUrl }}" class="btn-accent" target="_blank" rel="noopener">Bayar di Midtrans</a>
                @endif
                <form method="POST" action="{{ route('user.payments.midtrans.refresh', $payment) }}">
                    @csrf
                    <button type="submit" class="btn-secondary">Refresh Status</button>
                </form>
            </div>
        </div>

        <div class="app-panel">
            <p class="app-kicker">Rincian Pembayaran</p>
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="app-metric-card">
                    <p class="text-sm text-slate-500">Midtrans Transaction ID</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $payment->midtrans_transaction_id ?: '-' }}</p>
                </div>
                <div class="app-metric-card">
                    <p class="text-sm text-slate-500">Payment Type</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $payment->midtrans_payment_type ?: '-' }}</p>
                </div>
                <div class="app-metric-card">
                    <p class="text-sm text-slate-500">Status Code</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $payment->midtrans_status_code ?: '-' }}</p>
                </div>
                <div class="app-metric-card">
                    <p class="text-sm text-slate-500">Status angsuran</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ str($payment->installment->status_pembayaran)->replace('_', ' ')->title() }}</p>
                </div>
            </div>

            <div class="mt-6 rounded-[1.7rem] border border-[#ece4db] bg-[#fffaf6] p-5">
                <p class="font-semibold text-slate-950">Catatan</p>
                <p class="mt-3 text-sm leading-7 text-slate-600">{{ $payment->catatan_verifikasi ?? 'Belum ada catatan tambahan dari sistem.' }}</p>
            </div>
        </div>
    </section>

    @if ($proofUrl)
        <section class="mt-6 app-panel">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="app-kicker">Bukti Pembayaran</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-950">File pembayaran</h2>
                </div>
                <a href="{{ $proofUrl }}" target="_blank" class="btn-secondary">Buka File</a>
            </div>

            <div class="mt-6 overflow-hidden rounded-[1.8rem] border border-[#ece4db] bg-white">
                @if ($isPdfProof)
                    <iframe src="{{ $proofUrl }}" title="Bukti pembayaran" class="h-[32rem] w-full"></iframe>
                @else
                    <img src="{{ $proofUrl }}" alt="Bukti pembayaran" class="h-[32rem] w-full object-cover">
                @endif
            </div>
        </section>
    @endif
@endsection

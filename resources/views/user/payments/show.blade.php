@extends('layouts.user', [
    'title' => 'Detail Pembayaran',
    'heading' => 'Detail Pembayaran',
    'subheading' => 'Lihat bukti transfer, metode bayar, dan hasil verifikasi pembayaran angsuran Anda.',
])

@php
    $proofUrl = $payment->proof_url;
    $isPdfProof = \Illuminate\Support\Str::endsWith(strtolower($proofUrl), '.pdf');
@endphp

@section('content')
    <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <div class="app-panel-dark">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="app-kicker">Detail Transfer</p>
                    <p class="mt-3 text-xs uppercase tracking-[0.28em] text-white/50">{{ $payment->kode_pembayaran }}</p>
                    <h2 class="mt-3 text-3xl font-semibold text-white">{{ $payment->installment->application->motor->nama_motor }}</h2>
                    <p class="mt-3 text-sm leading-7 text-white/70">Pembayaran untuk angsuran ke-{{ $payment->installment->angsuran_ke }}. Gunakan halaman ini untuk melihat ringkasan transfer dan hasil verifikasi admin.</p>
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
                    <p class="mt-3 text-lg font-semibold text-white">{{ $payment->tanggal_bayar->translatedFormat('d M Y') }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Metode</p>
                    <p class="mt-3 text-sm font-semibold text-white">{{ $payment->metodeBayar?->metode_pembayaran ?? $payment->metode_bayar }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Bank pengirim</p>
                    <p class="mt-3 text-sm font-semibold text-white">{{ $payment->nama_bank_pengirim ?: '-' }}</p>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('user.payments.index') }}" class="btn-outline-light">Kembali ke Riwayat</a>
                <a href="{{ route('user.installments.show', $payment->installment) }}" class="btn-outline-light">Lihat Angsuran</a>
                <a href="{{ route('user.payments.receipt', $payment) }}" class="btn-accent">Bukti Pembayaran</a>
            </div>
        </div>

        <div class="app-panel">
            <p class="app-kicker">Rincian Pembayaran</p>
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="app-metric-card">
                    <p class="text-sm text-slate-500">Tempat bayar</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $payment->metodeBayar?->tempat_bayar ?? $payment->tempat_bayar ?? '-' }}</p>
                </div>
                <div class="app-metric-card">
                    <p class="text-sm text-slate-500">Rekening tujuan</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $payment->metodeBayar?->no_rekening ?? $payment->no_rekening_tujuan ?? '-' }}</p>
                </div>
                <div class="app-metric-card">
                    <p class="text-sm text-slate-500">Pemilik rekening</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $payment->nama_pemilik_rekening ?: '-' }}</p>
                </div>
                <div class="app-metric-card">
                    <p class="text-sm text-slate-500">Status angsuran</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ str($payment->installment->status_pembayaran)->replace('_', ' ')->title() }}</p>
                </div>
            </div>

            <div class="mt-6 rounded-[1.7rem] border border-[#ece4db] bg-[#fffaf6] p-5">
                <p class="font-semibold text-slate-950">Catatan verifikasi</p>
                <p class="mt-3 text-sm leading-7 text-slate-600">{{ $payment->catatan_verifikasi ?? 'Belum ada catatan tambahan dari tim verifikasi.' }}</p>
            </div>
        </div>
    </section>

    <section class="mt-6 app-panel">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="app-kicker">Bukti Pembayaran</p>
                <h2 class="mt-3 text-2xl font-semibold text-slate-950">File yang Anda unggah untuk pembayaran ini</h2>
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
@endsection

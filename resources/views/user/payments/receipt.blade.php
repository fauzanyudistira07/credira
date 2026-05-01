@extends('layouts.user', [
    'title' => 'Bukti Pembayaran',
    'heading' => 'Bukti Pembayaran',
    'subheading' => 'Receipt pembayaran angsuran yang bisa ditunjukkan atau dicetak saat dibutuhkan.',
])

@section('content')
    <section class="mx-auto max-w-4xl">
        <div class="app-panel-dark">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="app-kicker">Receipt Pembayaran</p>
                    <p class="mt-3 text-xs uppercase tracking-[0.28em] text-white/50">{{ $payment->kode_pembayaran }}</p>
                    <h2 class="mt-3 text-3xl font-semibold text-white">{{ $payment->installment->application->motor->nama_motor }}</h2>
                    <p class="mt-3 text-sm leading-7 text-white/70">Dokumen ini merangkum pembayaran angsuran, status verifikasi, dan identitas transaksi sebagai bukti pembayaran pelanggan.</p>
                </div>
                <x-status-badge :status="$payment->status_verifikasi" />
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <button type="button" onclick="window.print()" class="btn-accent">Cetak Bukti</button>
                <a href="{{ route('user.payments.show', $payment) }}" class="btn-outline-light">Kembali ke Detail</a>
            </div>
        </div>

        <div class="mt-6 app-panel">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="app-metric-card">
                    <p class="text-sm text-slate-500">Kode pembayaran</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $payment->kode_pembayaran }}</p>
                </div>
                <div class="app-metric-card">
                    <p class="text-sm text-slate-500">Tanggal bayar</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $payment->tanggal_bayar->translatedFormat('d F Y') }}</p>
                </div>
                <div class="app-metric-card">
                    <p class="text-sm text-slate-500">Nominal bayar</p>
                    <p class="mt-2 font-semibold text-slate-950">Rp {{ number_format($payment->nominal_bayar, 0, ',', '.') }}</p>
                </div>
                <div class="app-metric-card">
                    <p class="text-sm text-slate-500">Metode bayar</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $payment->metodeBayar?->metode_pembayaran ?? $payment->metode_bayar ?? '-' }}</p>
                </div>
                <div class="app-metric-card">
                    <p class="text-sm text-slate-500">Pemilik rekening</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $payment->nama_pemilik_rekening ?: '-' }}</p>
                </div>
                <div class="app-metric-card">
                    <p class="text-sm text-slate-500">Bank pengirim</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $payment->nama_bank_pengirim ?: '-' }}</p>
                </div>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-[1.7rem] border border-[#ece4db] bg-[#fffaf6] p-5">
                    <p class="font-semibold text-slate-950">Kontrak kredit</p>
                    <div class="mt-3 grid gap-2 text-sm leading-7 text-slate-600">
                        <p><span class="font-semibold text-slate-950">Kode pengajuan:</span> {{ $payment->installment->application->kode_pengajuan }}</p>
                        <p><span class="font-semibold text-slate-950">Motor:</span> {{ $payment->installment->application->motor->nama_motor }}</p>
                        <p><span class="font-semibold text-slate-950">Angsuran ke:</span> {{ $payment->installment->angsuran_ke }}</p>
                        <p><span class="font-semibold text-slate-950">Status angsuran:</span> {{ str($payment->installment->status_pembayaran)->replace('_', ' ')->title() }}</p>
                    </div>
                </div>
                <div class="rounded-[1.7rem] border border-[#ece4db] bg-[#fffaf6] p-5">
                    <p class="font-semibold text-slate-950">Verifikasi</p>
                    <div class="mt-3 grid gap-2 text-sm leading-7 text-slate-600">
                        <p><span class="font-semibold text-slate-950">Status:</span> {{ str($payment->status_verifikasi)->replace('_', ' ')->title() }}</p>
                        <p><span class="font-semibold text-slate-950">Diverifikasi oleh:</span> {{ $payment->verifier?->name ?? 'Belum diverifikasi' }}</p>
                        <p><span class="font-semibold text-slate-950">Waktu verifikasi:</span> {{ optional($payment->verified_at)->translatedFormat('d F Y H:i') ?? '-' }}</p>
                        <p><span class="font-semibold text-slate-950">Catatan:</span> {{ $payment->catatan_verifikasi ?? 'Belum ada catatan tambahan.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

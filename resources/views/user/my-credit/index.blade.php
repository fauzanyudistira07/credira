@extends('layouts.user', [
    'title' => 'My Kredit',
    'heading' => 'My Kredit Pelanggan',
    'subheading' => 'Satu halaman untuk melihat kontrak aktif, angsuran, pembayaran, dan pengiriman motor Anda.',
])

@php
    $activeApplication = $dashboard['active_application'];
    $nextInstallment = $dashboard['next_installment'];
    $activeDelivery = $dashboard['active_delivery'];
@endphp

@section('content')
    <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <div class="app-panel-dark">
            <p class="app-kicker">My Kredit</p>
            <h2 class="mt-4 text-3xl font-semibold leading-tight text-white sm:text-4xl">Ringkasan kontrak pembiayaan motor Anda ada di satu halaman.</h2>
            <p class="mt-4 max-w-2xl text-sm leading-7 text-white/72">Halaman ini dibuat khusus sebagai pusat My Kredit pelanggan: kontrak aktif, tagihan terdekat, histori pembayaran, dan tracking pengiriman tampil dalam satu alur.</p>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Kredit aktif</p>
                    <p class="mt-3 text-3xl font-semibold text-white">{{ $dashboard['active_applications'] }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Angsuran berjalan</p>
                    <p class="mt-3 text-3xl font-semibold text-white">{{ $dashboard['pending_installments_count'] }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Pembayaran pending</p>
                    <p class="mt-3 text-3xl font-semibold text-white">{{ $dashboard['pending_payments_count'] }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Pengiriman aktif</p>
                    <p class="mt-3 text-3xl font-semibold text-white">{{ $dashboard['delivery_count'] }}</p>
                </div>
            </div>
        </div>

        <div class="app-panel">
            <p class="app-kicker">Kontrak Utama</p>
            @if ($activeApplication)
                <div class="mt-5 rounded-[1.7rem] border border-[#ece4db] bg-[#fffaf6] p-5">
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-500">{{ $activeApplication->kode_pengajuan }}</p>
                    <h3 class="mt-3 text-2xl font-semibold text-slate-950">{{ $activeApplication->motor->nama_motor }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $activeApplication->jenisCicilan->nama_cicilan ?? 'Skema pembiayaan' }}</p>
                    <div class="mt-4"><x-status-badge :status="$activeApplication->status_pengajuan" /></div>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="app-metric-card">
                        <p class="text-sm text-slate-500">Total bayar</p>
                        <p class="mt-2 text-lg font-semibold text-slate-950">Rp {{ number_format($activeApplication->total_bayar, 0, ',', '.') }}</p>
                    </div>
                    <div class="app-metric-card">
                        <p class="text-sm text-slate-500">Cicilan per bulan</p>
                        <p class="mt-2 text-lg font-semibold text-slate-950">Rp {{ number_format($activeApplication->cicilan_perbulan, 0, ',', '.') }}</p>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="{{ route('user.applications.show', $activeApplication) }}" class="btn-primary">Detail Pengajuan</a>
                    <a href="{{ route('user.installments.index') }}" class="btn-secondary">Jadwal Angsuran</a>
                </div>
            @else
                <x-empty-state
                    title="Belum ada kontrak aktif"
                    description="Mulai pengajuan kredit terlebih dahulu agar halaman My Kredit menampilkan ringkasan pembiayaan Anda."
                    action-label="Ajukan Kredit"
                    action-href="{{ route('user.applications.create') }}"
                />
            @endif
        </div>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-3">
        <div class="app-panel">
            <p class="app-kicker">Tagihan Berikutnya</p>
            @if ($nextInstallment)
                <div class="mt-5 app-list-card-muted">
                    <p class="text-sm text-slate-500">Angsuran ke-{{ $nextInstallment->angsuran_ke }}</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-950">Rp {{ number_format($nextInstallment->total_tagihan, 0, ',', '.') }}</p>
                    <p class="mt-2 text-sm text-slate-600">Jatuh tempo {{ $nextInstallment->tanggal_jatuh_tempo->translatedFormat('d F Y') }}</p>
                </div>
                <div class="mt-5 flex gap-3">
                    <a href="{{ route('user.payments.create', ['installment' => $nextInstallment->id]) }}" class="btn-primary flex-1">Bayar</a>
                    <a href="{{ route('user.installments.show', $nextInstallment) }}" class="btn-secondary flex-1">Detail</a>
                </div>
            @else
                <p class="mt-5 text-sm leading-7 text-slate-600">Belum ada tagihan aktif saat ini.</p>
            @endif
        </div>

        <div class="app-panel">
            <p class="app-kicker">Pembayaran Terakhir</p>
            @if ($dashboard['recent_payments']->isNotEmpty())
                @php($payment = $dashboard['recent_payments']->first())
                <div class="mt-5 app-list-card-muted">
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-500">{{ $payment->kode_pembayaran }}</p>
                    <p class="mt-2 font-semibold text-slate-950">Rp {{ number_format($payment->nominal_bayar, 0, ',', '.') }}</p>
                    <p class="mt-2 text-sm text-slate-600">Untuk {{ $payment->installment->application->motor->nama_motor }}</p>
                    <div class="mt-4"><x-status-badge :status="$payment->status_verifikasi" /></div>
                </div>
                <div class="mt-5 flex gap-3">
                    <a href="{{ route('user.payments.show', $payment) }}" class="btn-primary flex-1">Detail</a>
                    <a href="{{ route('user.payments.receipt', $payment) }}" class="btn-secondary flex-1">Receipt</a>
                </div>
            @else
                <p class="mt-5 text-sm leading-7 text-slate-600">Belum ada pembayaran yang tercatat.</p>
            @endif
        </div>

        <div class="app-panel">
            <p class="app-kicker">Tracking Pengiriman</p>
            @if ($activeDelivery)
                <div class="mt-5 app-list-card-muted">
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-500">{{ $activeDelivery->invoice }}</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $activeDelivery->application->motor->nama_motor }}</p>
                    <p class="mt-2 text-sm text-slate-600">{{ $activeDelivery->nama_kurir ?: 'Kurir belum ditentukan' }}</p>
                    <div class="mt-4"><x-status-badge :status="$activeDelivery->status_kirim" /></div>
                </div>
                <a href="{{ route('user.deliveries.show', $activeDelivery) }}" class="btn-secondary mt-5 w-full">Lacak Pengiriman</a>
            @else
                <p class="mt-5 text-sm leading-7 text-slate-600">Belum ada data pengiriman aktif.</p>
            @endif
        </div>
    </section>
@endsection

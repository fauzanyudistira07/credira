@extends('layouts.user', [
    'title' => 'Detail Pengiriman',
    'heading' => 'Detail Pengiriman',
    'subheading' => 'Pantau status pengiriman unit, kurir, alamat tujuan, dan bukti pengantaran motor.',
])

@php
    $deliverySteps = ['menunggu_pengiriman', 'disiapkan', 'dikirim', 'sampai_tujuan'];
    $currentStepIndex = array_search($delivery->status_kirim, $deliverySteps, true);
    $currentStepIndex = $currentStepIndex === false ? 0 : $currentStepIndex;
@endphp

@section('content')
    <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <div class="app-panel-dark">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="app-kicker">Tracking Pengiriman</p>
                    <p class="mt-3 text-xs uppercase tracking-[0.28em] text-white/50">{{ $delivery->no_invoice ?: $delivery->invoice }}</p>
                    <h2 class="mt-3 text-3xl font-semibold text-white">{{ $delivery->application->motor->nama_motor }}</h2>
                    <p class="mt-3 text-sm leading-7 text-white/70">Layar ini menunjukkan progress distribusi unit motor dari gudang sampai alamat penerima. Cocok untuk pengalaman aplikasi kredit motor yang lengkap dari approval sampai serah terima.</p>
                </div>
                <x-status-badge :status="$delivery->status_kirim" />
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Tanggal kirim</p>
                    <p class="mt-3 text-lg font-semibold text-white">{{ optional($delivery->tgl_kirim)->translatedFormat('d M Y') ?? '-' }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Estimasi tiba</p>
                    <p class="mt-3 text-lg font-semibold text-white">{{ optional($delivery->tgl_tiba)->translatedFormat('d M Y') ?? '-' }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Kurir</p>
                    <p class="mt-3 text-sm font-semibold text-white">{{ $delivery->nama_kurir ?: '-' }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Penerima</p>
                    <p class="mt-3 text-sm font-semibold text-white">{{ $delivery->nama_penerima ?: '-' }}</p>
                </div>
            </div>
        </div>

        <div class="app-panel">
            <p class="app-kicker">Progress Pengiriman</p>
            <div class="relative mt-8 grid gap-6">
                <div class="timeline-line"></div>
                @foreach ($deliverySteps as $index => $step)
                    @php($state = $index < $currentStepIndex ? 'complete' : ($index === $currentStepIndex ? 'current' : 'pending'))
                    <div class="relative flex gap-4">
                        <div class="timeline-point {{ $state === 'complete' ? 'timeline-point-complete' : ($state === 'current' ? 'timeline-point-current' : 'timeline-point-pending') }}">
                            {{ $state === 'complete' ? 'OK' : ($state === 'current' ? 'ON' : $index + 1) }}
                        </div>
                        <div class="min-w-0 flex-1 rounded-[1.4rem] border {{ $state === 'current' ? 'border-orange-200 bg-orange-50/70' : 'border-slate-200 bg-slate-50' }} p-4">
                            <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                                <p class="font-semibold text-slate-900">{{ str($step)->replace('_', ' ')->title() }}</p>
                                @if ($state === 'current')
                                    <x-status-badge :status="$delivery->status_kirim" />
                                @endif
                            </div>
                            <p class="mt-2 text-sm leading-7 text-slate-600">
                                @switch($step)
                                    @case('menunggu_pengiriman')
                                        Unit menunggu penjadwalan dan finalisasi data pengiriman.
                                        @break
                                    @case('disiapkan')
                                        Motor dan dokumen pendukung sedang dipersiapkan sebelum berangkat.
                                        @break
                                    @case('dikirim')
                                        Motor sedang dalam perjalanan menuju alamat penerima.
                                        @break
                                    @default
                                        Unit sudah diterima sesuai alamat yang terdaftar.
                                @endswitch
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <div class="app-panel">
            <p class="app-kicker">Alamat Tujuan</p>
            <div class="mt-6 rounded-[1.7rem] border border-[#ece4db] bg-[#fffaf6] p-5">
                <p class="font-semibold text-slate-950">{{ $delivery->nama_penerima ?: 'Penerima belum diisi' }}</p>
                <p class="mt-3 text-sm leading-7 text-slate-600">{{ $delivery->alamat_tujuan ?: ($delivery->address?->alamat_lengkap ?? '-') }}</p>
                @if ($delivery->address)
                    <p class="mt-3 text-sm leading-7 text-slate-600">{{ $delivery->address->kota }}, {{ $delivery->address->provinsi }} {{ $delivery->address->kode_pos }}</p>
                @endif
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="app-metric-card">
                    <p class="text-sm text-slate-500">Kurir</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $delivery->nama_kurir ?: '-' }}</p>
                </div>
                <div class="app-metric-card">
                    <p class="text-sm text-slate-500">Telepon kurir</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $delivery->telpon_kurir ?: '-' }}</p>
                </div>
            </div>
        </div>

        <div class="app-panel">
            <p class="app-kicker">Keterangan</p>
            <div class="mt-6 rounded-[1.7rem] border border-[#ece4db] bg-[#fffaf6] p-5">
                <p class="text-sm leading-7 text-slate-600">{{ $delivery->keterangan ?: 'Belum ada catatan tambahan untuk pengiriman ini.' }}</p>
            </div>

            @if ($delivery->proof_photo_url)
                <div class="mt-6 overflow-hidden rounded-[1.8rem] border border-[#ece4db] bg-white">
                    <a href="{{ $delivery->proof_photo_url }}" target="_blank" rel="noopener noreferrer" class="block">
                        <img src="{{ $delivery->proof_photo_url }}" alt="Bukti pengiriman" class="h-[26rem] w-full object-cover">
                    </a>
                </div>
            @endif
        </div>
    </section>
@endsection

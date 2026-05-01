@extends('layouts.admin', [
    'title' => $pelanggan->display_name,
    'heading' => 'Detail Pelanggan',
    'subheading' => 'Lihat data utama pelanggan, identitas singkat, alamat, dan daftar pengajuan yang terhubung.',
])

@section('content')
    <div class="space-y-6">
        <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <section class="admin-hero-panel">
                <span class="admin-eyebrow">Customer Profile</span>
                <h2 class="mt-5 text-3xl font-semibold text-white sm:text-4xl">{{ $pelanggan->display_name }}</h2>
                <p class="mt-4 admin-copy">{{ $pelanggan->alamat1 ?: 'Alamat utama belum tersimpan pada pelanggan ini.' }}</p>

                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                    <div class="admin-highlight-stat"><p class="admin-highlight-stat__label">Marketing Owner</p><p class="admin-highlight-stat__value !text-2xl">{{ $pelanggan->marketingOwner?->name ?? '-' }}</p></div>
                    <div class="admin-highlight-stat"><p class="admin-highlight-stat__label">Total Pengajuan</p><p class="admin-highlight-stat__value !text-2xl">{{ number_format($pelanggan->pengajuanKredit->count()) }}</p></div>
                </div>
            </section>

            <section class="admin-detail-panel">
                <p class="admin-eyebrow">Data Utama</p>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Email</p><p class="admin-metric-card__value">{{ $pelanggan->email ?: '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">No. Telepon</p><p class="admin-metric-card__value">{{ $pelanggan->no_telp ?: '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">No. KTP</p><p class="admin-metric-card__value">{{ $pelanggan->no_ktp ?: '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Tanggal Lahir</p><p class="admin-metric-card__value">{{ $pelanggan->tanggal_lahir?->format('d M Y') ?: '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Tempat Lahir</p><p class="admin-metric-card__value">{{ $pelanggan->tempat_lahir ?: '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Pekerjaan</p><p class="admin-metric-card__value">{{ $pelanggan->pekerjaan_default ?: '-' }}</p></div>
                    <div class="admin-metric-card sm:col-span-2"><p class="admin-metric-card__label">Alamat Utama</p><p class="admin-metric-card__value">{{ $pelanggan->alamat1 ?: '-' }}</p></div>
                </div>
            </section>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.84fr_1.16fr]">
            <section class="admin-detail-panel">
                <p class="admin-eyebrow">Alamat & Foto</p>
                <div class="mt-5 space-y-4">
                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach ([$pelanggan->foto_profil_url, $pelanggan->foto_ktp_url, $pelanggan->foto_selfie_url] as $image)
                            @if ($image)
                                <div class="admin-gallery-card">
                                    <a href="{{ $image }}" target="_blank" rel="noreferrer" class="block h-full w-full">
                                        <img src="{{ $image }}" alt="{{ $pelanggan->display_name }}" class="h-full w-full object-cover">
                                    </a>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    @foreach ($pelanggan->addresses as $address)
                        <div class="admin-metric-card">
                            <p class="admin-metric-card__label">{{ $address->label_alamat }}</p>
                            <p class="admin-metric-card__value">{{ $address->alamat_lengkap }}</p>
                            <p class="mt-2 text-sm text-slate-300">{{ $address->kota }}, {{ $address->provinsi }} {{ $address->kode_pos }}</p>
                        </div>
                    @endforeach

                    @if ($pelanggan->addresses->isEmpty())
                        <p class="admin-copy">Belum ada data alamat tambahan.</p>
                    @endif
                </div>
            </section>

            <section class="admin-detail-panel">
                <div class="dashboard-panel__head">
                    <div>
                        <p class="admin-eyebrow">Pengajuan</p>
                        <h3 class="mt-3 text-2xl font-semibold text-white">Riwayat pengajuan pelanggan</h3>
                    </div>
                </div>
                <div class="grid gap-3">
                    @forelse ($pelanggan->pengajuanKredit as $pengajuan)
                        <a href="{{ route('admin.pengajuan.show', $pengajuan) }}" class="admin-activity-card">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-semibold text-white">{{ $pengajuan->kode_pengajuan }}</p>
                                    <p class="mt-1 text-sm text-slate-400">{{ $pengajuan->motor?->nama_motor ?? '-' }} &middot; {{ $pengajuan->marketingOwner?->name ?? '-' }}</p>
                                </div>
                                <x-status-badge :status="$pengajuan->status_pengajuan" class="!border-white/10 !bg-white/8 !text-orange-100" />
                            </div>
                        </a>
                    @empty
                        <p class="admin-copy">Pelanggan ini belum memiliki pengajuan.</p>
                    @endforelse
                </div>
            </section>
        </section>
    </div>
@endsection

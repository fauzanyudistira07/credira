@extends('layouts.dashboard', [
    'title' => $pelanggan->display_name,
    'role' => 'marketing',
    'pageTitle' => 'Detail Pelanggan',
    'pageDescription' => 'Lihat identitas, alamat, dan riwayat pengajuan pelanggan untuk melanjutkan proses marketing tanpa berpindah-pindah layar.',
])

@section('content')
    <div class="marketing-page">
        <section class="grid gap-6 xl:grid-cols-[1fr_0.92fr]">
            <div class="marketing-surface">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="dashboard-kicker">Customer Profile</p>
                        <h2 class="mt-3 text-3xl font-semibold tracking-[-0.04em] text-slate-950">{{ $pelanggan->display_name }}</h2>
                        <p class="mt-3 text-sm leading-7 text-slate-600">Data pelanggan ini dapat dilihat oleh seluruh akun marketing untuk mendukung kolaborasi tim.</p>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('marketing.pelanggan.edit', $pelanggan) }}" class="btn-secondary">Edit Data</a>
                        <a href="{{ route('marketing.pengajuan.create', ['pelanggan_id' => $pelanggan->id]) }}" class="btn-accent">Buat Pengajuan</a>
                    </div>
                </div>

                <div class="marketing-data-grid mt-6">
                    <div class="marketing-data-card">
                        <p class="marketing-data-label">Email</p>
                        <p class="marketing-data-value">{{ $pelanggan->email }}</p>
                    </div>
                    <div class="marketing-data-card">
                        <p class="marketing-data-label">No. Telepon</p>
                        <p class="marketing-data-value">{{ $pelanggan->no_telp }}</p>
                    </div>
                    <div class="marketing-data-card">
                        <p class="marketing-data-label">No. KTP</p>
                        <p class="marketing-data-value">{{ $pelanggan->no_ktp ?: '-' }}</p>
                    </div>
                    <div class="marketing-data-card">
                        <p class="marketing-data-label">Status Pernikahan</p>
                        <p class="marketing-data-value">{{ $pelanggan->status_pernikahan ?: '-' }}</p>
                    </div>
                    <div class="marketing-data-card">
                        <p class="marketing-data-label">Pekerjaan</p>
                        <p class="marketing-data-value">{{ $pelanggan->pekerjaan_default ?: '-' }}</p>
                    </div>
                    <div class="marketing-data-card">
                        <p class="marketing-data-label">Penghasilan</p>
                        <p class="marketing-data-value">Rp {{ number_format($pelanggan->penghasilan_default ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="marketing-surface">
                <div class="dashboard-panel__head">
                    <div>
                        <p class="dashboard-kicker">Alamat</p>
                        <h3 class="marketing-section-title">Alamat utama & akun login</h3>
                    </div>
                </div>

                <div class="space-y-4 text-sm leading-7 text-slate-600">
                    <div class="marketing-soft-card !p-4">
                        <p class="font-semibold text-slate-950">Alamat utama</p>
                        <p class="mt-2">{{ $pelanggan->alamat1 ?: '-' }}</p>
                        <p class="mt-1">{{ $pelanggan->kota1 ?: '-' }}, {{ $pelanggan->propinsi1 ?: '-' }} {{ $pelanggan->kodepos1 ?: '' }}</p>
                    </div>
                    <div class="marketing-soft-card !p-4">
                        <p class="font-semibold text-slate-950">Akun pelanggan</p>
                        <p class="mt-2">Nama akun: {{ $pelanggan->user?->name ?: '-' }}</p>
                        <p>Email login: {{ $pelanggan->user?->email ?: '-' }}</p>
                    </div>
                    <div class="marketing-soft-card !p-4">
                        <p class="font-semibold text-slate-950">Foto / dokumen</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach (['foto_profil_url' => 'Profil', 'foto_ktp_url' => 'KTP', 'foto_selfie_url' => 'Selfie'] as $field => $label)
                                @if ($pelanggan->{$field})
                                    <a href="{{ $pelanggan->{$field} }}" target="_blank" rel="noreferrer" class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 transition hover:border-emerald-300 hover:bg-emerald-100">
                                        {{ $label }} tersedia
                                    </a>
                                @else
                                    <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-500">{{ $label }} belum ada</span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="marketing-surface">
            <div class="dashboard-panel__head">
                <div>
                    <p class="dashboard-kicker">Pengajuan</p>
                    <h3 class="marketing-section-title">Riwayat pengajuan pelanggan</h3>
                </div>
            </div>

            @if ($pelanggan->pengajuanKredit->isEmpty())
                <x-empty-state
                    title="Belum ada pengajuan"
                    description="Pelanggan ini belum memiliki pengajuan kredit. Anda bisa langsung memulai dari tombol buat pengajuan."
                    action-label="Buat Pengajuan"
                    action-href="{{ route('marketing.pengajuan.create', ['pelanggan_id' => $pelanggan->id]) }}"
                />
            @else
                <div class="grid gap-3">
                    @foreach ($pelanggan->pengajuanKredit->sortByDesc('created_at') as $item)
                        <a href="{{ route('marketing.pengajuan.show', $item) }}" class="dashboard-list-row">
                            <div>
                                <p class="font-semibold text-slate-950">{{ $item->kode_pengajuan }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $item->motor?->nama_motor ?? '-' }} - {{ $item->jenisCicilan?->durasi_bulan ?? '-' }} bulan</p>
                            </div>
                            <div class="flex flex-col items-start gap-3 sm:items-end">
                                <x-status-badge :status="$item->status_pengajuan" />
                                <span class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ optional($item->tgl_pengajuan)->format('d M Y') ?: $item->created_at?->format('d M Y') }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection

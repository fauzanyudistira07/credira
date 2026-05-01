@extends('layouts.dashboard', [
    'title' => $pengajuan->kode_pengajuan,
    'role' => 'marketing',
    'pageTitle' => 'Detail Pengajuan',
    'pageDescription' => 'Lihat status, data pelanggan, motor, finansial, dokumen, dan riwayat log pada satu halaman yang mudah discan.',
])

@section('content')
    <div class="marketing-page">
        <section class="grid gap-6 xl:grid-cols-[1.08fr_0.92fr]">
            <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-[linear-gradient(135deg,#111827,#1f2937)] p-6 text-white shadow-[0_32px_90px_-56px_rgba(15,23,42,0.78)]">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-orange-200">Application Detail</p>
                        <h2 class="mt-3 text-3xl font-semibold tracking-[-0.04em]">{{ $pengajuan->kode_pengajuan }}</h2>
                        <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-300">{{ $pengajuan->pelanggan?->display_name ?? '-' }} mengajukan {{ $pengajuan->motor?->nama_motor ?? '-' }} melalui akun marketing {{ $pengajuan->marketingOwner?->name ?? auth()->user()->name }}.</p>
                    </div>
                    <x-status-badge :status="$pengajuan->status_pengajuan" />
                </div>

                <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-[1.4rem] border border-white/10 bg-white/6 p-4">
                        <p class="text-[11px] uppercase tracking-[0.22em] text-white/46">Tanggal</p>
                        <p class="mt-2 font-semibold">{{ optional($pengajuan->tgl_pengajuan)->format('d M Y') ?: $pengajuan->created_at?->format('d M Y') }}</p>
                    </div>
                    <div class="rounded-[1.4rem] border border-white/10 bg-white/6 p-4">
                        <p class="text-[11px] uppercase tracking-[0.22em] text-white/46">Tenor</p>
                        <p class="mt-2 font-semibold">{{ $pengajuan->jenisCicilan?->durasi_bulan ?? '-' }} bulan</p>
                    </div>
                    <div class="rounded-[1.4rem] border border-white/10 bg-white/6 p-4">
                        <p class="text-[11px] uppercase tracking-[0.22em] text-white/46">DP</p>
                        <p class="mt-2 font-semibold">Rp {{ number_format($pengajuan->dp, 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-[1.4rem] border border-white/10 bg-white/6 p-4">
                        <p class="text-[11px] uppercase tracking-[0.22em] text-white/46">Total bayar</p>
                        <p class="mt-2 font-semibold">Rp {{ number_format($pengajuan->total_bayar, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="marketing-surface">
                <div class="dashboard-panel__head">
                    <div>
                        <p class="dashboard-kicker">Quick Summary</p>
                        <h3 class="marketing-section-title">Ringkasan approval pack</h3>
                    </div>
                </div>

                <div class="grid gap-4">
                    <div class="marketing-data-card">
                        <p class="marketing-data-label">Marketing pembuat</p>
                        <p class="marketing-data-value">{{ $pengajuan->marketingOwner?->name ?? '-' }}</p>
                    </div>
                    <div class="marketing-data-card">
                        <p class="marketing-data-label">Pelanggan</p>
                        <p class="marketing-data-value">{{ $pengajuan->pelanggan?->display_name ?? '-' }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $pengajuan->pelanggan?->no_telp ?? '-' }}</p>
                    </div>
                    <div class="marketing-data-card">
                        <p class="marketing-data-label">Motor</p>
                        <p class="marketing-data-value">{{ $pengajuan->motor?->nama_motor ?? '-' }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $pengajuan->motor?->merk ?? '-' }}</p>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('marketing.pengajuan.index') }}" class="btn-secondary flex-1">Kembali ke Daftar</a>
                        <a href="{{ route('marketing.pengajuan.review', $pengajuan) }}" class="btn-secondary flex-1">Approval Pengajuan</a>
                        <a href="{{ route('marketing.pelanggan.show', $pengajuan->pelanggan) }}" class="btn-accent flex-1">Lihat Pelanggan</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            <div class="marketing-surface">
                <p class="dashboard-kicker">Pelanggan</p>
                <div class="mt-5 grid gap-3 text-sm leading-7 text-slate-600">
                    <p><span class="font-semibold text-slate-950">Nama:</span> {{ $pengajuan->pelanggan?->display_name ?? '-' }}</p>
                    <p><span class="font-semibold text-slate-950">Telepon:</span> {{ $pengajuan->pelanggan?->no_telp ?? '-' }}</p>
                    <p><span class="font-semibold text-slate-950">Email:</span> {{ $pengajuan->pelanggan?->email ?? '-' }}</p>
                    <p><span class="font-semibold text-slate-950">No. KTP:</span> {{ $pengajuan->pelanggan?->no_ktp ?? '-' }}</p>
                    <p><span class="font-semibold text-slate-950">Alamat:</span> {{ $pengajuan->pelanggan?->alamat1 ?: '-' }}</p>
                </div>
            </div>

            <div class="marketing-surface">
                <p class="dashboard-kicker">Motor</p>
                <div class="mt-5 grid gap-3 text-sm leading-7 text-slate-600">
                    <p><span class="font-semibold text-slate-950">Unit:</span> {{ $pengajuan->motor?->nama_motor ?? '-' }}</p>
                    <p><span class="font-semibold text-slate-950">Merk:</span> {{ $pengajuan->motor?->merk ?? '-' }}</p>
                    <p><span class="font-semibold text-slate-950">Harga cash:</span> Rp {{ number_format($pengajuan->harga_cash, 0, ',', '.') }}</p>
                    <p><span class="font-semibold text-slate-950">Jenis:</span> {{ $pengajuan->motor?->jenisMotor?->jenis ?? '-' }}</p>
                    <p><span class="font-semibold text-slate-950">Stok:</span> {{ $pengajuan->motor?->stok ?? '-' }}</p>
                </div>
            </div>

            <div class="marketing-surface">
                <p class="dashboard-kicker">Finansial</p>
                <div class="mt-5 grid gap-3 text-sm leading-7 text-slate-600">
                    <p><span class="font-semibold text-slate-950">Tenor:</span> {{ $pengajuan->jenisCicilan?->nama_cicilan ?? '-' }}</p>
                    <p><span class="font-semibold text-slate-950">DP:</span> Rp {{ number_format($pengajuan->dp, 0, ',', '.') }}</p>
                    <p><span class="font-semibold text-slate-950">Pokok kredit:</span> Rp {{ number_format($pengajuan->pokok_kredit, 0, ',', '.') }}</p>
                    <p><span class="font-semibold text-slate-950">Biaya admin:</span> Rp {{ number_format($pengajuan->biaya_admin, 0, ',', '.') }}</p>
                    <p><span class="font-semibold text-slate-950">Biaya asuransi:</span> Rp {{ number_format($pengajuan->biaya_asuransi, 0, ',', '.') }}</p>
                    <p><span class="font-semibold text-slate-950">Cicilan per bulan:</span> Rp {{ number_format($pengajuan->cicilan_perbulan, 0, ',', '.') }}</p>
                    <p><span class="font-semibold text-slate-950">Total bayar:</span> Rp {{ number_format($pengajuan->total_bayar, 0, ',', '.') }}</p>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1fr_0.95fr]">
            <div class="marketing-surface">
                <div class="dashboard-panel__head">
                    <div>
                        <p class="dashboard-kicker">Dokumen</p>
                        <h3 class="marketing-section-title">Berkas pengajuan</h3>
                    </div>
                </div>

                @if ($pengajuan->documents->isEmpty())
                    <x-empty-state
                        title="Belum ada dokumen"
                        description="Dokumen yang diupload saat pengajuan dibuat akan tampil di sini."
                    />
                @else
                    <div class="grid gap-3">
                        @foreach ($pengajuan->documents as $document)
                            <a href="{{ $document->file_url }}" target="_blank" class="dashboard-list-row">
                                <div>
                                    <p class="font-semibold text-slate-950">{{ str($document->jenis_dokumen)->replace('_', ' ')->title() }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $document->nama_file }}</p>
                                </div>
                                <x-status-badge :status="$document->status_verifikasi" />
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="marketing-surface">
                <div class="dashboard-panel__head">
                    <div>
                        <p class="dashboard-kicker">Logs</p>
                        <h3 class="marketing-section-title">Riwayat status pengajuan</h3>
                    </div>
                </div>

                @if ($pengajuan->logs->isEmpty())
                    <p class="text-sm leading-7 text-slate-600">Belum ada log status tambahan.</p>
                @else
                    <div class="grid gap-3">
                        @foreach ($pengajuan->logs as $log)
                            <div class="marketing-data-card">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="font-semibold text-slate-950">{{ str($log->status_baru)->replace('_', ' ')->title() }}</p>
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ $log->created_at?->format('d M Y H:i') }}</p>
                                </div>
                                <p class="mt-2 text-sm leading-7 text-slate-600">{{ $log->catatan ?: 'Perubahan status tercatat di sistem.' }}</p>
                                <p class="mt-3 text-xs font-medium text-slate-500">Oleh: {{ $log->changedBy?->name ?? 'Sistem' }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection

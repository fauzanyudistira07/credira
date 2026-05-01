@extends('layouts.admin', [
    'title' => $pengajuan->kode_pengajuan,
    'heading' => 'Detail Pengajuan',
    'subheading' => 'Lihat seluruh informasi pengajuan, pelanggan, motor, pembiayaan, dokumen, dan log status.',
])

@section('content')
    <div class="space-y-6">
        <section class="grid gap-6 xl:grid-cols-[1.08fr_0.92fr]">
            <div class="admin-hero-panel">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <span class="admin-eyebrow">Pengajuan Detail</span>
                        <h2 class="mt-5 text-3xl font-semibold text-white sm:text-4xl">{{ $pengajuan->kode_pengajuan }}</h2>
                        <p class="mt-4 admin-copy">{{ $pengajuan->catatan_status ?: 'Belum ada catatan status tambahan.' }}</p>
                    </div>
                    <x-status-badge :status="$pengajuan->status_pengajuan" class="!border-white/10 !bg-white/8 !text-orange-100" />
                </div>

                <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="admin-highlight-stat"><p class="admin-highlight-stat__label">Tanggal</p><p class="admin-highlight-stat__value !text-2xl">{{ optional($pengajuan->tgl_pengajuan)->format('d M Y') ?: '-' }}</p></div>
                    <div class="admin-highlight-stat"><p class="admin-highlight-stat__label">Marketing</p><p class="admin-highlight-stat__value !text-2xl">{{ $pengajuan->marketingOwner?->name ?? '-' }}</p></div>
                    <div class="admin-highlight-stat"><p class="admin-highlight-stat__label">Tenor</p><p class="admin-highlight-stat__value !text-2xl">{{ $pengajuan->jenisCicilan?->durasi_bulan ?? '-' }} bln</p></div>
                    <div class="admin-highlight-stat"><p class="admin-highlight-stat__label">Total Bayar</p><p class="admin-highlight-stat__value !text-2xl">Rp {{ number_format($pengajuan->total_bayar, 0, ',', '.') }}</p></div>
                </div>
            </div>

            <section class="admin-detail-panel">
                <div class="dashboard-panel__head">
                    <div>
                        <p class="admin-eyebrow">Action</p>
                        <h3 class="mt-3 text-2xl font-semibold text-white">Review status</h3>
                    </div>
                    <a href="{{ route('admin.pengajuan.review', $pengajuan) }}" class="btn-accent">Buka Review</a>
                </div>

                <div class="grid gap-3">
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Approved at</p><p class="admin-metric-card__value">{{ $pengajuan->approved_at?->format('d M Y H:i') ?: '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Rejected at</p><p class="admin-metric-card__value">{{ $pengajuan->rejected_at?->format('d M Y H:i') ?: '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Catatan status</p><p class="admin-metric-card__value">{{ $pengajuan->keterangan_status_pengajuan ?: '-' }}</p></div>
                </div>
            </section>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <section class="admin-detail-panel">
                <p class="admin-eyebrow">Pelanggan</p>
                <div class="mt-5 grid gap-3">
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Nama</p><p class="admin-metric-card__value">{{ $pengajuan->pelanggan?->display_name ?? '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Telepon</p><p class="admin-metric-card__value">{{ $pengajuan->pelanggan?->no_telp ?? '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Email</p><p class="admin-metric-card__value">{{ $pengajuan->pelanggan?->email ?? '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Identitas</p><p class="admin-metric-card__value">{{ $pengajuan->pelanggan?->no_ktp ?? '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Alamat</p><p class="admin-metric-card__value">{{ $pengajuan->pelanggan?->alamat1 ?: '-' }}</p></div>
                </div>
            </section>

            <section class="admin-detail-panel">
                <p class="admin-eyebrow">Motor</p>
                <div class="mt-5 grid gap-3">
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Nama Motor</p><p class="admin-metric-card__value">{{ $pengajuan->motor?->nama_motor ?? '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Merk</p><p class="admin-metric-card__value">{{ $pengajuan->motor?->merk ?? '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Harga Cash</p><p class="admin-metric-card__value">Rp {{ number_format($pengajuan->harga_cash, 0, ',', '.') }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Jenis</p><p class="admin-metric-card__value">{{ $pengajuan->motor?->jenisMotor?->jenis ?? '-' }}</p></div>
                </div>
            </section>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <section class="admin-detail-panel">
                <p class="admin-eyebrow">Pembiayaan</p>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Jenis Cicilan</p><p class="admin-metric-card__value">{{ $pengajuan->jenisCicilan?->nama_cicilan ?? '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Asuransi</p><p class="admin-metric-card__value">{{ $pengajuan->asuransi?->nama_asuransi ?? '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">DP</p><p class="admin-metric-card__value">Rp {{ number_format($pengajuan->dp, 0, ',', '.') }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Pokok Kredit</p><p class="admin-metric-card__value">Rp {{ number_format($pengajuan->pokok_kredit, 0, ',', '.') }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Harga Kredit</p><p class="admin-metric-card__value">Rp {{ number_format($pengajuan->harga_kredit, 0, ',', '.') }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Margin</p><p class="admin-metric-card__value">Rp {{ number_format($pengajuan->margin_kredit, 0, ',', '.') }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Biaya Admin</p><p class="admin-metric-card__value">Rp {{ number_format($pengajuan->biaya_admin, 0, ',', '.') }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Biaya Asuransi</p><p class="admin-metric-card__value">Rp {{ number_format($pengajuan->biaya_asuransi, 0, ',', '.') }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Total Bayar</p><p class="admin-metric-card__value">Rp {{ number_format($pengajuan->total_bayar, 0, ',', '.') }}</p></div>
                </div>
            </section>

            <section class="admin-detail-panel">
                <p class="admin-eyebrow">Finansial Tambahan</p>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Pekerjaan</p><p class="admin-metric-card__value">{{ $pengajuan->financialDetail?->pekerjaan ?: '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Nama Perusahaan</p><p class="admin-metric-card__value">{{ $pengajuan->financialDetail?->nama_perusahaan ?: '-' }}</p></div>
                    <div class="admin-metric-card sm:col-span-2"><p class="admin-metric-card__label">Alamat Kantor</p><p class="admin-metric-card__value">{{ $pengajuan->financialDetail?->alamat_kantor ?: '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Lama Bekerja</p><p class="admin-metric-card__value">{{ $pengajuan->financialDetail?->lama_bekerja ?: '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Status Rumah</p><p class="admin-metric-card__value">{{ $pengajuan->financialDetail?->status_rumah ?: '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Penghasilan Bulanan</p><p class="admin-metric-card__value">{{ $pengajuan->financialDetail?->penghasilan_bulanan ? 'Rp '.number_format($pengajuan->financialDetail->penghasilan_bulanan, 0, ',', '.') : '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Pengeluaran Bulanan</p><p class="admin-metric-card__value">{{ $pengajuan->financialDetail?->pengeluaran_bulanan ? 'Rp '.number_format($pengajuan->financialDetail->pengeluaran_bulanan, 0, ',', '.') : '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Kontak Darurat</p><p class="admin-metric-card__value">{{ $pengajuan->financialDetail?->kontak_darurat_nama ?: '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">No. Kontak Darurat</p><p class="admin-metric-card__value">{{ $pengajuan->financialDetail?->kontak_darurat_nohp ?: '-' }}</p></div>
                </div>
            </section>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.92fr_1.08fr]">
            <section class="admin-detail-panel">
                <div class="dashboard-panel__head">
                    <div>
                        <p class="admin-eyebrow">Dokumen</p>
                        <h3 class="mt-3 text-2xl font-semibold text-white">Berkas pengajuan</h3>
                    </div>
                </div>
                <div class="grid gap-3">
                    @forelse ($pengajuan->documents as $document)
                        <a href="{{ $document->file_url }}" target="_blank" class="admin-activity-card">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-white">{{ str($document->jenis_dokumen)->replace('_', ' ')->title() }}</p>
                                    <p class="mt-1 text-sm text-slate-400">{{ $document->nama_file }}</p>
                                    <p class="mt-2 text-sm text-slate-300">{{ $document->catatan_verifikasi ?: 'Belum ada catatan verifikasi.' }}</p>
                                </div>
                                <x-status-badge :status="$document->status_verifikasi" class="!border-white/10 !bg-white/8 !text-orange-100" />
                            </div>
                        </a>
                    @empty
                        <p class="admin-copy">Belum ada dokumen pada pengajuan ini.</p>
                    @endforelse
                </div>
            </section>

            <section class="admin-detail-panel">
                <div class="dashboard-panel__head">
                    <div>
                        <p class="admin-eyebrow">Log Status</p>
                        <h3 class="mt-3 text-2xl font-semibold text-white">Riwayat perubahan</h3>
                    </div>
                </div>
                <div class="admin-timeline">
                    @forelse ($pengajuan->logs as $log)
                        <div class="admin-timeline__item">
                            <div class="admin-timeline__dot"></div>
                            <div class="admin-timeline__card">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="font-semibold text-white">{{ str($log->status_baru)->replace('_', ' ')->title() }}</p>
                                    <p class="text-xs uppercase tracking-[0.22em] text-slate-400">{{ $log->created_at?->format('d M Y H:i') }}</p>
                                </div>
                                <p class="mt-3 text-sm leading-7 text-slate-300">{{ $log->catatan ?: 'Perubahan status tercatat di sistem.' }}</p>
                                <p class="mt-3 text-xs font-semibold uppercase tracking-[0.2em] text-orange-200">Oleh {{ $log->changedBy?->name ?? 'Sistem' }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="admin-copy">Belum ada log pengajuan.</p>
                    @endforelse
                </div>
            </section>
        </section>
    </div>
@endsection

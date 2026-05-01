@extends('layouts.admin', [
    'title' => 'Detail Pengajuan',
    'heading' => 'Detail Pengajuan',
    'subheading' => 'Review data pengajuan, verifikasi dokumen, lalu update status proses kredit.',
])

@section('content')
    <section class="space-y-6">
        <div class="admin-detail-panel">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="admin-eyebrow">{{ $application->kode_pengajuan }}</p>
                    <h2 class="mt-3 text-2xl font-semibold text-white">{{ $application->motor->nama_motor }}</h2>
                    <p class="mt-2 text-sm text-slate-300">{{ $application->pelanggan->nama_lengkap }} &middot; {{ $application->jenisCicilan->nama_cicilan }}</p>
                </div>
                <div class="flex flex-col items-end gap-2">
                    <x-status-badge :status="$application->status_pengajuan" class="!border-white/10 !bg-white/8 !text-orange-100" />
                    <a href="{{ route('admin.applications.index') }}" class="admin-text-link">Kembali ke daftar</a>
                </div>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="admin-metric-card">
                    <p class="admin-metric-card__label">Harga Cash</p>
                    <p class="admin-metric-card__value">Rp {{ number_format($application->harga_cash, 0, ',', '.') }}</p>
                </div>
                <div class="admin-metric-card">
                    <p class="admin-metric-card__label">DP</p>
                    <p class="admin-metric-card__value">Rp {{ number_format($application->dp, 0, ',', '.') }}</p>
                </div>
                <div class="admin-metric-card">
                    <p class="admin-metric-card__label">Cicilan / Bulan</p>
                    <p class="admin-metric-card__value">Rp {{ number_format($application->cicilan_perbulan, 0, ',', '.') }}</p>
                </div>
                <div class="admin-metric-card">
                    <p class="admin-metric-card__label">Total Bayar</p>
                    <p class="admin-metric-card__value">Rp {{ number_format($application->total_bayar, 0, ',', '.') }}</p>
                </div>
                <div class="admin-metric-card">
                    <p class="admin-metric-card__label">Jadwal Angsuran</p>
                    <p class="admin-metric-card__value">{{ $application->installments->count() }} kali</p>
                </div>
                <div class="admin-metric-card">
                    <p class="admin-metric-card__label">Pengiriman</p>
                    <p class="admin-metric-card__value">{{ $application->delivery ? 'Sudah tersedia' : 'Belum dibuat' }}</p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <section class="admin-detail-panel admin-form-shell">
                <p class="admin-eyebrow">Update Status Pengajuan</p>
                <h3 class="mt-3 text-xl font-semibold text-white">Tindakan admin</h3>

                <form method="POST" action="{{ route('admin.applications.status', $application) }}" class="mt-5 grid gap-4">
                    @csrf
                    <div>
                        <label class="field-label">Status baru</label>
                        <select name="status_pengajuan" class="field-select">
                            @foreach ($statusOptions as $status)
                                <option value="{{ $status->value }}" @selected(old('status_pengajuan', $application->status_pengajuan) === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Catatan admin</label>
                        <textarea name="catatan" class="field-textarea" placeholder="Catatan perubahan status (opsional)">{{ old('catatan', $application->catatan_status) }}</textarea>
                    </div>
                    <button type="submit" class="admin-logout-button">Simpan Status</button>
                </form>
            </section>

            <section class="admin-detail-panel">
                <p class="admin-eyebrow">Data Nasabah</p>
                <h3 class="mt-3 text-xl font-semibold text-white">Profil singkat</h3>

                <div class="mt-5 grid gap-3 text-sm text-slate-300">
                    <p><span class="font-semibold text-white">Nama:</span> {{ $application->pelanggan->nama_lengkap }}</p>
                    <p><span class="font-semibold text-white">Email:</span> {{ $application->pelanggan->email }}</p>
                    <p><span class="font-semibold text-white">No. Telp:</span> {{ $application->pelanggan->no_telp }}</p>
                    <p><span class="font-semibold text-white">Pekerjaan:</span> {{ $application->financialDetail?->pekerjaan ?? '-' }}</p>
                    <p><span class="font-semibold text-white">Penghasilan:</span> Rp {{ number_format($application->financialDetail?->penghasilan_bulanan ?? 0, 0, ',', '.') }}</p>
                </div>
            </section>
        </div>

        <section class="admin-stream-panel admin-form-shell">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="admin-eyebrow">Dokumen Pengajuan</p>
                    <h3 class="mt-3 text-xl font-semibold text-white">Verifikasi dokumen</h3>
                </div>
            </div>

            <div class="mt-6 grid gap-4">
                @forelse ($application->documents as $document)
                    <article class="admin-activity-card">
                        <div class="grid gap-4 xl:grid-cols-[1fr_360px]">
                            <div>
                                <p class="font-semibold text-white">{{ str($document->jenis_dokumen)->replace('_', ' ')->title() }}</p>
                                <p class="mt-1 text-sm text-slate-400">{{ $document->nama_file }}</p>
                                <div class="mt-3">
                                    <x-status-badge :status="$document->status_verifikasi" class="!border-white/10 !bg-white/8 !text-orange-100" />
                                </div>
                                @if ($document->catatan_verifikasi)
                                    <p class="mt-3 text-sm text-slate-300">{{ $document->catatan_verifikasi }}</p>
                                @endif
                                <a href="{{ $document->file_url }}" target="_blank" rel="noopener noreferrer" class="admin-text-link mt-4 inline-flex">Lihat file</a>
                            </div>

                            <form method="POST" action="{{ route('admin.applications.documents.verify', [$application, $document]) }}" class="grid gap-3 rounded-[1.2rem] border border-white/10 bg-white/6 p-4">
                                @csrf
                                <div>
                                    <label class="field-label">Status dokumen</label>
                                    <select name="status_verifikasi" class="field-select">
                                        @foreach ($documentStatusOptions as $status)
                                            <option value="{{ $status->value }}" @selected($document->status_verifikasi === $status->value)>{{ $status->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="field-label">Catatan verifikasi</label>
                                    <textarea name="catatan_verifikasi" class="field-textarea" placeholder="Catatan verifikasi (opsional)">{{ $document->catatan_verifikasi }}</textarea>
                                </div>
                                <button type="submit" class="admin-utility-button">Simpan Verifikasi</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <x-empty-state
                        title="Dokumen belum ada"
                        description="Nasabah belum mengunggah dokumen pengajuan pada data ini."
                    />
                @endforelse
            </div>
        </section>

        <section class="admin-stream-panel">
            <p class="admin-eyebrow">Riwayat Log</p>
            <h3 class="mt-3 text-xl font-semibold text-white">Perubahan status</h3>

            <div class="mt-5 grid gap-3">
                @forelse ($application->logs as $log)
                    <article class="admin-activity-card">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <p class="font-medium text-white">{{ str($log->status_lama ?? '-')->replace('_', ' ')->title() }} -> {{ str($log->status_baru)->replace('_', ' ')->title() }}</p>
                            <p class="text-xs text-slate-500">{{ optional($log->created_at)->format('d M Y H:i') }}</p>
                        </div>
                        <p class="mt-2 text-sm text-slate-300">{{ $log->catatan ?? '-' }}</p>
                        <p class="mt-1 text-xs text-slate-500">Oleh: {{ $log->changedBy?->name ?? 'System' }}</p>
                    </article>
                @empty
                    <p class="text-sm text-slate-400">Belum ada riwayat perubahan.</p>
                @endforelse
            </div>
        </section>
    </section>
@endsection

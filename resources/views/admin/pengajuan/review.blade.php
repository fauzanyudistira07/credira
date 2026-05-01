@extends('layouts.admin', [
    'title' => 'Review '.$pengajuan->kode_pengajuan,
    'heading' => 'Review Pengajuan',
    'subheading' => 'Ubah status pengajuan secara aman tanpa perlu mengedit seluruh data operasional.',
])

@section('content')
    <div class="space-y-6">
        <section class="grid gap-6 xl:grid-cols-[0.88fr_1.12fr]">
            <section class="admin-detail-panel">
                <p class="admin-eyebrow">Current Status</p>
                <h2 class="mt-4 text-3xl font-semibold text-white">{{ $pengajuan->kode_pengajuan }}</h2>
                <div class="mt-5 flex flex-wrap gap-2">
                    <x-status-badge :status="$pengajuan->status_pengajuan" class="!border-white/10 !bg-white/8 !text-orange-100" />
                    <span class="inline-flex items-center rounded-full border border-white/10 bg-white/6 px-4 py-2 text-sm font-semibold text-slate-200">{{ $pengajuan->pelanggan?->display_name ?? '-' }}</span>
                    <span class="inline-flex items-center rounded-full border border-white/10 bg-white/6 px-4 py-2 text-sm font-semibold text-slate-200">{{ $pengajuan->marketingOwner?->name ?? '-' }}</span>
                </div>

                <div class="mt-6 grid gap-3">
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Motor</p><p class="admin-metric-card__value">{{ $pengajuan->motor?->nama_motor ?? '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Tenor</p><p class="admin-metric-card__value">{{ $pengajuan->jenisCicilan?->durasi_bulan ?? '-' }} bulan</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">DP</p><p class="admin-metric-card__value">Rp {{ number_format($pengajuan->dp, 0, ',', '.') }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Total Bayar</p><p class="admin-metric-card__value">Rp {{ number_format($pengajuan->total_bayar, 0, ',', '.') }}</p></div>
                </div>
            </section>

            <form method="POST" action="{{ route('admin.pengajuan.update-status', $pengajuan) }}" class="admin-detail-panel admin-form-shell space-y-6">
                @csrf
                @method('PUT')
                <div>
                    <p class="admin-eyebrow">Status Action</p>
                    <h3 class="mt-4 text-2xl font-semibold text-white">Update status pengajuan</h3>
                    <p class="mt-3 admin-copy">Tombol di bawah akan menyimpan status ke kolom `status_pengajuan`, mengisi timestamp terkait, dan membuat log baru dengan `changed_by` admin yang sedang login.</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($reviewOptions as $value => $label)
                        <label class="admin-action-choice">
                            <input type="radio" name="status" value="{{ $value }}" class="sr-only peer" @checked(old('status', 'review') === $value)>
                            <span class="admin-action-choice__panel">
                                <strong class="block text-white">{{ $label }}</strong>
                                <span class="mt-2 block text-sm text-slate-300">
                                    {{ match ($value) {
                                        'pending' => 'Kembalikan ke antrean pending.',
                                        'review' => 'Masuk ke tahap review dokumen.',
                                        'approved' => 'Setujui pengajuan.',
                                        'rejected' => 'Tolak pengajuan dan catat alasannya.',
                                    } }}
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
                <x-form-error name="status" />

                <div>
                    <label class="field-label" for="catatan">Catatan review</label>
                    <textarea id="catatan" name="catatan" class="field-textarea" placeholder="Tambahkan catatan untuk approval, penolakan, atau perubahan status lainnya">{{ old('catatan', $pengajuan->catatan_status) }}</textarea>
                    <p class="field-help">Catatan wajib saat reject, dan akan ikut tersimpan di `pengajuan_logs`.</p>
                    <x-form-error name="catatan" />
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <button type="submit" class="btn-accent flex-1">Simpan Status</button>
                    <a href="{{ route('admin.pengajuan.show', $pengajuan) }}" class="btn-secondary flex-1">Kembali ke Detail</a>
                </div>
            </form>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.96fr_1.04fr]">
            <section class="admin-detail-panel">
                <p class="admin-eyebrow">Dokumen</p>
                <div class="mt-5 grid gap-3">
                    @forelse ($pengajuan->documents as $document)
                        <a href="{{ $document->file_url }}" target="_blank" class="admin-activity-card">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-white">{{ str($document->jenis_dokumen)->replace('_', ' ')->title() }}</p>
                                    <p class="mt-1 text-sm text-slate-400">{{ $document->nama_file }}</p>
                                </div>
                                <x-status-badge :status="$document->status_verifikasi" class="!border-white/10 !bg-white/8 !text-orange-100" />
                            </div>
                        </a>
                    @empty
                        <p class="admin-copy">Belum ada dokumen yang terlampir.</p>
                    @endforelse
                </div>
            </section>

            <section class="admin-detail-panel">
                <p class="admin-eyebrow">Log Terbaru</p>
                <div class="mt-5 admin-timeline">
                    @forelse ($pengajuan->logs as $log)
                        <div class="admin-timeline__item">
                            <div class="admin-timeline__dot"></div>
                            <div class="admin-timeline__card">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="font-semibold text-white">{{ str($log->status_baru)->replace('_', ' ')->title() }}</p>
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ $log->created_at?->format('d M Y H:i') }}</p>
                                </div>
                                <p class="mt-2 text-sm leading-7 text-slate-300">{{ $log->catatan ?: 'Log perubahan status.' }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="admin-copy">Belum ada log perubahan.</p>
                    @endforelse
                </div>
            </section>
        </section>
    </div>
@endsection

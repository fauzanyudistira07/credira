@extends('layouts.admin', [
    'title' => 'Kelola Pengajuan',
    'heading' => 'Kelola Pengajuan',
    'subheading' => 'Tinjau pengajuan kredit, cek data nasabah, lalu update status pengajuan.',
])

@section('content')
    <section class="space-y-6">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="admin-summary-card">
                <p class="admin-summary-card__label">Total</p>
                <p class="admin-summary-card__value">{{ number_format($summary['total']) }}</p>
                <p class="admin-summary-card__copy">Seluruh pengajuan.</p>
            </article>
            <article class="admin-summary-card">
                <p class="admin-summary-card__label">Antrean</p>
                <p class="admin-summary-card__value">{{ number_format($summary['in_queue']) }}</p>
                <p class="admin-summary-card__copy">Butuh proses admin.</p>
            </article>
            <article class="admin-summary-card">
                <p class="admin-summary-card__label">Disetujui</p>
                <p class="admin-summary-card__value">{{ number_format($summary['approved']) }}</p>
                <p class="admin-summary-card__copy">Status approved/kontrak aktif.</p>
            </article>
            <article class="admin-summary-card">
                <p class="admin-summary-card__label">Ditolak</p>
                <p class="admin-summary-card__value">{{ number_format($summary['rejected']) }}</p>
                <p class="admin-summary-card__copy">Ditolak atau dibatalkan admin.</p>
            </article>
        </div>

        <div class="admin-filter-panel admin-form-shell">
            <form method="GET" class="grid gap-4 md:grid-cols-[1fr_260px_auto]">
                <div>
                    <label class="field-label">Cari pengajuan</label>
                    <input
                        type="text"
                        name="q"
                        class="field-input"
                        value="{{ $keyword }}"
                        placeholder="Kode pengajuan, nama nasabah, atau motor"
                    >
                </div>
                <div>
                    <label class="field-label">Status</label>
                    <select name="status" class="field-select">
                        <option value="">Semua status</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status['value'] }}" @selected($currentStatus === $status['value'])>{{ $status['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="admin-logout-button w-full md:w-auto">Filter</button>
                    <a href="{{ route('admin.applications.index') }}" class="admin-utility-button w-full md:w-auto">Reset</a>
                </div>
            </form>
        </div>

        <div class="admin-stream-panel">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="admin-eyebrow">Daftar Pengajuan</p>
                    <h2 class="mt-2 text-2xl font-semibold text-white">Pengajuan masuk</h2>
                </div>
                <p class="admin-muted">Total {{ number_format($applications->total()) }} data</p>
            </div>

            <div class="mt-6 grid gap-4">
                @forelse ($applications as $application)
                    <article class="admin-activity-card">
                        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">{{ $application->kode_pengajuan }}</p>
                                <h3 class="mt-2 text-lg font-semibold text-white">{{ $application->motor->nama_motor }}</h3>
                                <p class="mt-2 text-sm text-slate-300">{{ $application->pelanggan->nama_lengkap }} &middot; {{ $application->jenisCicilan->nama_cicilan }}</p>
                                <p class="mt-1 text-sm text-slate-400">DP Rp {{ number_format($application->dp, 0, ',', '.') }} &middot; Dokumen {{ $application->documents_count }} file</p>
                                <p class="mt-1 text-sm text-slate-500">{{ optional($application->created_at)->format('d M Y H:i') }}</p>
                            </div>
                            <div class="flex flex-col items-start gap-3 xl:items-end">
                                <x-status-badge :status="$application->status_pengajuan" class="!border-white/10 !bg-white/8 !text-orange-100" />
                                <a href="{{ route('admin.applications.show', $application) }}" class="admin-utility-button !min-h-0 !px-4 !py-2.5">Detail</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <x-empty-state
                        title="Belum ada pengajuan"
                        description="Belum ada data pengajuan yang cocok dengan filter saat ini."
                    />
                @endforelse
            </div>

            @if ($applications->hasPages())
                <div class="mt-6">
                    {{ $applications->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection

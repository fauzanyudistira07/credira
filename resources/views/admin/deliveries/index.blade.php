@extends('layouts.admin', [
    'title' => 'Monitoring Pengiriman',
    'heading' => 'Monitoring Pengiriman',
    'subheading' => 'Pantau distribusi unit motor dan update progres pengiriman.',
])

@section('content')
    <section class="space-y-6">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="admin-summary-card">
                <p class="admin-summary-card__label">Total</p>
                <p class="admin-summary-card__value">{{ number_format($summary['total']) }}</p>
                <p class="admin-summary-card__copy">Seluruh data pengiriman.</p>
            </article>
            <article class="admin-summary-card">
                <p class="admin-summary-card__label">Menunggu</p>
                <p class="admin-summary-card__value">{{ number_format($summary['waiting']) }}</p>
                <p class="admin-summary-card__copy">Belum dijadwalkan kirim.</p>
            </article>
            <article class="admin-summary-card">
                <p class="admin-summary-card__label">Berjalan</p>
                <p class="admin-summary-card__value">{{ number_format($summary['on_delivery']) }}</p>
                <p class="admin-summary-card__copy">Sedang disiapkan/dikirim.</p>
            </article>
            <article class="admin-summary-card">
                <p class="admin-summary-card__label">Selesai</p>
                <p class="admin-summary-card__value">{{ number_format($summary['completed']) }}</p>
                <p class="admin-summary-card__copy">Sudah sampai tujuan.</p>
            </article>
        </div>

        <div class="admin-filter-panel admin-form-shell">
            <form method="GET" class="grid gap-4 md:grid-cols-[1fr_260px_auto]">
                <div>
                    <label class="field-label">Cari pengiriman</label>
                    <input
                        type="text"
                        name="q"
                        class="field-input"
                        value="{{ $keyword }}"
                        placeholder="No. invoice, kode pengajuan, nama nasabah, atau motor"
                    >
                </div>
                <div>
                    <label class="field-label">Status kirim</label>
                    <select name="status" class="field-select">
                        <option value="">Semua status</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected($currentStatus === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="admin-logout-button w-full md:w-auto">Filter</button>
                    <a href="{{ route('admin.deliveries.index') }}" class="admin-utility-button w-full md:w-auto">Reset</a>
                </div>
            </form>
        </div>

        <div class="admin-stream-panel">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="admin-eyebrow">Daftar Pengiriman</p>
                    <h2 class="mt-2 text-2xl font-semibold text-white">Distribusi unit</h2>
                </div>
                <p class="admin-muted">Total {{ number_format($deliveries->total()) }} data</p>
            </div>

            <div class="mt-6 grid gap-4">
                @forelse ($deliveries as $delivery)
                    <article class="admin-activity-card">
                        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">{{ $delivery->invoice }}</p>
                                <h3 class="mt-2 text-lg font-semibold text-white">{{ $delivery->application->motor->nama_motor }}</h3>
                                <p class="mt-2 text-sm text-slate-300">{{ $delivery->application->pelanggan->nama_lengkap }} &middot; {{ $delivery->application->kode_pengajuan }}</p>
                                <p class="mt-1 text-sm text-slate-400">Kirim {{ optional($delivery->tgl_kirim)->format('d M Y') ?? '-' }} &middot; Tiba {{ optional($delivery->tgl_tiba)->format('d M Y') ?? '-' }}</p>
                            </div>
                            <div class="flex flex-col items-start gap-3 xl:items-end">
                                <x-status-badge :status="$delivery->status_kirim" class="!border-white/10 !bg-white/8 !text-orange-100" />
                                <a href="{{ route('admin.deliveries.show', $delivery) }}" class="admin-utility-button !min-h-0 !px-4 !py-2.5">Detail</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <x-empty-state
                        title="Belum ada data pengiriman"
                        description="Belum ada data pengiriman yang cocok dengan filter saat ini."
                    />
                @endforelse
            </div>

            @if ($deliveries->hasPages())
                <div class="mt-6">
                    {{ $deliveries->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection

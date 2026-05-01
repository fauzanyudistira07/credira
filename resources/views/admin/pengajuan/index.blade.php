@extends('layouts.admin', [
    'title' => 'Manajemen Pengajuan',
    'heading' => 'Manajemen Pengajuan',
    'subheading' => 'Admin melihat seluruh pengajuan dari semua marketing, lalu review, approve, atau reject dari area yang lebih usable.',
])

@section('content')
    <div class="space-y-6">
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <article class="admin-secondary-stat"><p class="admin-secondary-stat__label">Total</p><p class="admin-secondary-stat__value">{{ number_format($summary['total']) }}</p><p class="admin-secondary-stat__copy">Seluruh pengajuan sistem.</p></article>
            <article class="admin-secondary-stat"><p class="admin-secondary-stat__label">Pending</p><p class="admin-secondary-stat__value">{{ number_format($summary['pending']) }}</p><p class="admin-secondary-stat__copy">Menunggu tindakan awal admin.</p></article>
            <article class="admin-secondary-stat"><p class="admin-secondary-stat__label">Review</p><p class="admin-secondary-stat__value">{{ number_format($summary['review']) }}</p><p class="admin-secondary-stat__copy">Masuk tahap penelaahan dokumen.</p></article>
            <article class="admin-secondary-stat"><p class="admin-secondary-stat__label">Approved</p><p class="admin-secondary-stat__value">{{ number_format($summary['approved']) }}</p><p class="admin-secondary-stat__copy">Sudah melewati approval.</p></article>
            <article class="admin-secondary-stat"><p class="admin-secondary-stat__label">Rejected</p><p class="admin-secondary-stat__value">{{ number_format($summary['rejected']) }}</p><p class="admin-secondary-stat__copy">Ditolak atau dibatalkan admin.</p></article>
        </section>

        <x-filter-toolbar
            class="admin-filter-panel"
            title="Filter dan export pengajuan"
            description="Gunakan filter cepat untuk review, lalu unduh CSV untuk kebutuhan demo atau analisis dasar."
            :result-text="$pengajuan->total().' pengajuan ditemukan'"
            :active-filters="array_values(array_filter([
                filled($filters['q'] ?? null) ? 'Cari: '.($filters['q'] ?? '') : null,
                filled($filters['status'] ?? null) ? 'Status: '.($statusOptions[$filters['status']] ?? $filters['status']) : null,
                filled($filters['marketing_id'] ?? null) ? 'Marketing terpilih' : null,
            ]))"
            :reset-href="route('admin.pengajuan.index')"
        >
            <div class="mb-4 flex flex-wrap items-center justify-end gap-3">
                <a href="{{ route('admin.pengajuan.index', array_filter(array_merge($filters, ['export' => 'csv']))) }}" class="btn-secondary">Export CSV</a>
            </div>
            <form method="GET" class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_0.72fr_0.88fr_auto]">
                <div>
                    <label class="field-label">Cari kode atau pelanggan</label>
                    <input type="text" name="q" class="field-input" value="{{ $filters['q'] ?? '' }}" placeholder="Cari pengajuan" data-chip-label="Cari">
                </div>
                <div>
                    <label class="field-label">Status</label>
                    <select name="status" class="field-select" data-chip-label="Status">
                        <option value="">Semua status</option>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label">Marketing</label>
                    <select name="marketing_id" class="field-select" data-chip-label="Marketing">
                        <option value="">Semua marketing</option>
                        @foreach ($marketingUsers as $marketing)
                            <option value="{{ $marketing->id }}" @selected((string) ($filters['marketing_id'] ?? '') === (string) $marketing->id)>{{ $marketing->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-3">
                    <button type="submit" class="btn-accent">Filter</button>
                    @if (filled($filters['q'] ?? null) || filled($filters['status'] ?? null) || filled($filters['marketing_id'] ?? null))
                        <a href="{{ route('admin.pengajuan.index') }}" class="btn-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </x-filter-toolbar>

        @if ($pengajuan->isEmpty())
            <x-empty-state
                title="Belum ada pengajuan"
                description="Daftar pengajuan dari semua marketing akan tampil di sini lengkap dengan status review dan akses detail."
            />
        @else
            <x-table-shell class="admin-stream-panel" title="Queue pengajuan" description="Tabel mempertahankan filter saat pagination dan siap diexport ke CSV.">
                <div class="admin-table-wrap overflow-x-auto">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Kode / Tanggal</th>
                                <th>Pelanggan</th>
                                <th>Motor</th>
                                <th>Marketing</th>
                                <th>Tenor / DP</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pengajuan as $item)
                                <tr>
                                    <td>
                                        <p class="font-semibold text-white">{{ $item->kode_pengajuan }}</p>
                                        <p class="mt-1 text-sm text-slate-400">{{ optional($item->tgl_pengajuan)->format('d M Y') ?: $item->created_at?->format('d M Y') }}</p>
                                    </td>
                                    <td>
                                        <p class="font-semibold text-white">{{ $item->pelanggan?->display_name ?? '-' }}</p>
                                        <p class="mt-1 text-sm text-slate-400">{{ $item->pelanggan?->no_telp ?? '-' }}</p>
                                    </td>
                                    <td>
                                        <p class="font-semibold text-white">{{ $item->motor?->nama_motor ?? '-' }}</p>
                                        <p class="mt-1 text-sm text-slate-400">{{ $item->motor?->merk ?? '-' }}</p>
                                    </td>
                                    <td>{{ $item->marketingOwner?->name ?? '-' }}</td>
                                    <td>
                                        <p>{{ $item->jenisCicilan?->durasi_bulan ?? '-' }} bulan</p>
                                        <p class="mt-1 text-sm text-slate-400">DP Rp {{ number_format($item->dp, 0, ',', '.') }}</p>
                                    </td>
                                    <td>Rp {{ number_format($item->total_bayar, 0, ',', '.') }}</td>
                                    <td><x-status-badge :status="$item->status_pengajuan" class="!border-white/10 !bg-white/8 !text-orange-100" /></td>
                                    <td class="text-right">
                                        <div class="flex flex-col items-end gap-2">
                                            <a href="{{ route('admin.pengajuan.show', $item) }}" class="admin-text-link">Detail</a>
                                            <a href="{{ route('admin.pengajuan.review', $item) }}" class="admin-text-link">Review</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-table-shell>

            <x-pagination :paginator="$pengajuan" surface-class="text-white" />
        @endif
    </div>
@endsection

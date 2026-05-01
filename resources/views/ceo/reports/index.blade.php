@extends('layouts.dashboard', [
    'title' => 'Laporan Pengajuan',
    'role' => 'ceo',
    'pageTitle' => 'Laporan Pengajuan',
    'pageDescription' => 'Area read-only untuk membaca seluruh pengajuan dengan filter cepat, ringkasan hasil, dan export CSV lengkap.',
])

@section('content')
    <div class="ceo-page">
        <section class="ceo-summary-grid" data-reveal>
            <article class="ceo-summary-card"><p class="ceo-summary-card__label">Total hasil filter</p><p class="ceo-summary-card__value">{{ number_format($summary['total']) }}</p></article>
            <article class="ceo-summary-card ceo-summary-card--warning"><p class="ceo-summary-card__label">Pending</p><p class="ceo-summary-card__value">{{ number_format($summary['pending']) }}</p></article>
            <article class="ceo-summary-card ceo-summary-card--success"><p class="ceo-summary-card__label">Approved</p><p class="ceo-summary-card__value">{{ number_format($summary['approved']) }}</p></article>
            <article class="ceo-summary-card ceo-summary-card--danger"><p class="ceo-summary-card__label">Rejected</p><p class="ceo-summary-card__value">{{ number_format($summary['rejected']) }}</p></article>
        </section>

        <x-filter-toolbar
            class="ceo-panel"
            title="Laporan dan pencarian"
            description="Filter, hasil, dan export disatukan agar reporting CEO tetap ringkas."
            :result-text="$reports->total().' hasil sesuai filter'"
            :active-filters="array_values(array_filter([
                filled($filters['q'] ?? null) ? 'Cari: '.($filters['q'] ?? '') : null,
                filled($filters['status'] ?? null) ? 'Status: '.($statusOptions[$filters['status']] ?? $filters['status']) : null,
                filled($filters['marketing_id'] ?? null) ? 'Marketing terpilih' : null,
                filled($filters['date_from'] ?? null) ? 'Dari: '.($filters['date_from'] ?? '') : null,
                filled($filters['date_to'] ?? null) ? 'Sampai: '.($filters['date_to'] ?? '') : null,
            ]))"
            :reset-href="route('ceo.reports.index')"
            data-reveal
        >
            <div class="ceo-panel__head !mb-0">
                <span class="ceo-chip ceo-chip--soft">Filter</span>
                <a
                    href="{{ route('ceo.reports.index', array_filter(array_merge($filters, ['export' => 'csv']))) }}"
                    class="btn-secondary"
                >
                    Export CSV
                </a>
            </div>

            <form method="GET" class="ceo-filter-grid">
                <div>
                    <label class="field-label">Cari kode / pelanggan</label>
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
                <div>
                    <label class="field-label">Tanggal mulai</label>
                    <input type="date" name="date_from" class="field-input" value="{{ $filters['date_from'] ?? '' }}" data-chip-label="Dari">
                </div>
                <div>
                    <label class="field-label">Tanggal akhir</label>
                    <input type="date" name="date_to" class="field-input" value="{{ $filters['date_to'] ?? '' }}" data-chip-label="Sampai">
                </div>
                <div class="ceo-filter-actions">
                    <button type="submit" class="btn-accent">Terapkan Filter</button>
                    <a href="{{ route('ceo.reports.index') }}" class="btn-secondary">Reset</a>
                </div>
            </form>
        </x-filter-toolbar>

        <section class="ceo-panel" data-reveal>
            <div class="ceo-table-wrap">
                <table class="ceo-table">
                    <thead>
                        <tr>
                            <th>Kode / Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Marketing</th>
                            <th>Motor</th>
                            <th>Tenor</th>
                            <th>Total Bayar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $item)
                            <tr>
                                <td>
                                    <p class="font-semibold text-slate-950">{{ $item->kode_pengajuan }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ optional($item->tgl_pengajuan)->format('d M Y') ?: $item->created_at?->format('d M Y') }}</p>
                                </td>
                                <td>
                                    <p class="font-semibold text-slate-950">{{ $item->pelanggan?->display_name ?? '-' }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $item->pelanggan?->no_telp ?? '-' }}</p>
                                </td>
                                <td>{{ $item->marketingOwner?->name ?? '-' }}</td>
                                <td>
                                    <p class="font-semibold text-slate-950">{{ $item->motor?->nama_motor ?? '-' }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $item->motor?->merk ?? '-' }}</p>
                                </td>
                                <td>{{ $item->jenisCicilan?->durasi_bulan ?? '-' }} bulan</td>
                                <td>Rp {{ number_format((int) $item->total_bayar, 0, ',', '.') }}</td>
                                <td><x-status-badge :status="$item->status_pengajuan" /></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="dashboard-empty-state">
                                        Tidak ada data pengajuan untuk filter yang dipilih.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5">
                <x-pagination :paginator="$reports" />
            </div>
        </section>
    </div>
@endsection

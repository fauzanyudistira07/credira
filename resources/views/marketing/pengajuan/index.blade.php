@extends('layouts.dashboard', [
    'title' => 'Pengajuan Kredit',
    'role' => 'marketing',
    'pageTitle' => 'Pengajuan Kredit',
    'pageDescription' => 'Pantau semua pengajuan milik Anda, saring berdasarkan status, lalu buka detail untuk melihat progres dan dokumen.',
])

@section('content')
    <div class="marketing-page">
        <section class="marketing-hero">
            <div class="grid gap-8 xl:grid-cols-[minmax(0,1.06fr)_minmax(320px,0.94fr)] xl:items-end">
                <div>
                    <p class="marketing-hero__eyebrow">Applications</p>
                    <h2 class="marketing-hero__title">Pantau semua pengajuan milik Anda dengan status yang lebih mudah discan.</h2>
                    <p class="marketing-hero__copy">Gunakan pencarian dan filter status untuk mempercepat follow up ke pelanggan dan melihat progres aplikasi yang masih berjalan.</p>
                </div>
                <div class="marketing-soft-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/45">Total hasil</p>
                    <p class="mt-2 text-2xl font-semibold text-white">{{ $pengajuan->total() }}</p>
                    <p class="mt-2 text-sm leading-6 text-white/65">Daftar ini hanya memuat pengajuan yang dibuat oleh marketing yang sedang login.</p>
                </div>
            </div>
        </section>

        <x-filter-toolbar
            class="marketing-surface"
            title="Daftar pengajuan marketing"
            description="Filter pipeline aktif Anda dengan status yang lebih jelas dan tetap menjaga context saat pindah halaman."
            :result-text="$pengajuan->total().' hasil'"
            :active-filters="array_values(array_filter([
                filled($filters['q'] ?? null) ? 'Cari: '.($filters['q'] ?? '') : null,
                filled($filters['status'] ?? null) ? 'Status: '.($statusOptions[$filters['status']] ?? $filters['status']) : null,
            ]))"
            :reset-href="route('marketing.pengajuan.index')"
        >
            <div class="dashboard-panel__head !mb-0">
                <p class="dashboard-kicker">Applications</p>
                <a href="{{ route('marketing.pengajuan.create') }}" class="btn-accent">Buat Pengajuan</a>
            </div>

            <form method="GET" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_0.7fr_auto]">
                <div>
                    <label class="field-label">Cari kode atau pelanggan</label>
                    <input type="text" name="q" class="field-input" value="{{ $filters['q'] ?? '' }}" placeholder="Contoh: CRD-MKT atau nama pelanggan" data-chip-label="Cari">
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
                <div class="flex items-end gap-3">
                    <button type="submit" class="btn-accent">Filter</button>
                    @if (filled($filters['q'] ?? null) || filled($filters['status'] ?? null))
                        <a href="{{ route('marketing.pengajuan.index') }}" class="btn-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </x-filter-toolbar>

        @if ($pengajuan->isEmpty())
            <x-empty-state
                title="Belum ada pengajuan"
                description="Pengajuan yang Anda buat akan muncul di sini lengkap dengan status, motor, pelanggan, dan detail finansial."
                action-label="Buat Pengajuan"
                action-href="{{ route('marketing.pengajuan.create') }}"
            />
        @else
            <x-table-shell class="marketing-surface" title="Pipeline pengajuan" description="Status, tenor, dan nominal ditata agar lebih mudah discan saat follow up.">
                <div class="marketing-table-wrap overflow-x-auto">
                    <table class="marketing-table">
                        <thead>
                            <tr>
                                <th class="px-4 py-4 font-semibold">Kode / Tanggal</th>
                                <th class="px-4 py-4 font-semibold">Pelanggan</th>
                                <th class="px-4 py-4 font-semibold">Motor</th>
                                <th class="px-4 py-4 font-semibold">Tenor / DP</th>
                                <th class="px-4 py-4 font-semibold">Total</th>
                                <th class="px-4 py-4 font-semibold">Status</th>
                                <th class="px-4 py-4 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pengajuan as $item)
                                <tr>
                                    <td class="px-4 py-4">
                                        <p class="font-semibold text-slate-950">{{ $item->kode_pengajuan }}</p>
                                        <p class="mt-1 text-slate-500">{{ optional($item->tgl_pengajuan)->format('d M Y') ?: $item->created_at?->format('d M Y') }}</p>
                                    </td>
                                    <td class="px-4 py-4">
                                        <p class="font-semibold text-slate-950">{{ $item->pelanggan?->display_name ?? '-' }}</p>
                                        <p class="mt-1 text-slate-500">{{ $item->pelanggan?->no_telp ?? '-' }}</p>
                                    </td>
                                    <td class="px-4 py-4 text-slate-600">
                                        <p>{{ $item->motor?->nama_motor ?? '-' }}</p>
                                        <p class="mt-1">{{ $item->motor?->merk ?? '-' }}</p>
                                    </td>
                                    <td class="px-4 py-4 text-slate-600">
                                        <p>{{ $item->jenisCicilan?->durasi_bulan ?? '-' }} bulan</p>
                                        <p class="mt-1">DP Rp {{ number_format($item->dp, 0, ',', '.') }}</p>
                                    </td>
                                    <td class="px-4 py-4 font-semibold text-slate-950">
                                        Rp {{ number_format($item->total_bayar, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <x-status-badge :status="$item->status_pengajuan" />
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <a href="{{ route('marketing.pengajuan.show', $item) }}" class="dashboard-text-link">Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-table-shell>

            <x-pagination :paginator="$pengajuan" />
        @endif
    </div>
@endsection

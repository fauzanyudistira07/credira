@extends('layouts.admin', [
    'title' => 'Manajemen Pelanggan',
    'heading' => 'Manajemen Pelanggan',
    'subheading' => 'Pantau seluruh pelanggan, owner marketing, dan riwayat pengajuannya dari satu meja admin.',
])

@section('content')
    <div class="space-y-6">
        <section class="grid gap-4 md:grid-cols-3">
            <article class="admin-secondary-stat"><p class="admin-secondary-stat__label">Total pelanggan</p><p class="admin-secondary-stat__value">{{ number_format($summary['total']) }}</p><p class="admin-secondary-stat__copy">Seluruh data pelanggan existing.</p></article>
            <article class="admin-secondary-stat"><p class="admin-secondary-stat__label">Dengan pengajuan</p><p class="admin-secondary-stat__value">{{ number_format($summary['with_pengajuan']) }}</p><p class="admin-secondary-stat__copy">Sudah masuk ke flow pengajuan kredit.</p></article>
            <article class="admin-secondary-stat"><p class="admin-secondary-stat__label">Belum ada pengajuan</p><p class="admin-secondary-stat__value">{{ number_format($summary['without_pengajuan']) }}</p><p class="admin-secondary-stat__copy">Masih berupa prospect atau data awal.</p></article>
        </section>

        <x-filter-toolbar
            class="admin-filter-panel"
            title="Filter dan export pelanggan"
            description="Data pelanggan bisa dicari, difilter per owner marketing, lalu diexport ke CSV sederhana."
            :result-text="$pelanggan->total().' pelanggan ditemukan'"
            :active-filters="array_values(array_filter([
                filled($filters['q'] ?? null) ? 'Cari: '.($filters['q'] ?? '') : null,
                filled($filters['marketing_id'] ?? null) ? 'Marketing terpilih' : null,
            ]))"
            :reset-href="route('admin.pelanggan.index')"
        >
            <div class="mb-4 flex flex-wrap items-center justify-end gap-3">
                <a href="{{ route('admin.pelanggan.index', array_filter(array_merge($filters, ['export' => 'csv']))) }}" class="btn-secondary">Export CSV</a>
            </div>
            <form method="GET" class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_0.92fr_auto]">
                <div>
                    <label class="field-label">Cari pelanggan</label>
                    <input type="text" name="q" class="field-input" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama, email, atau telepon" data-chip-label="Cari">
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
                    @if (filled($filters['q'] ?? null) || filled($filters['marketing_id'] ?? null))
                        <a href="{{ route('admin.pelanggan.index') }}" class="btn-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </x-filter-toolbar>

        @if ($pelanggan->isEmpty())
            <x-empty-state
                title="Belum ada data pelanggan"
                description="Data pelanggan dari semua marketing akan tampil di sini lengkap dengan relasi owner dan jumlah pengajuan."
            />
        @else
            <x-table-shell class="admin-stream-panel" title="Daftar pelanggan" description="Relasi marketing owner dan jumlah pengajuan lebih mudah discan pada satu tabel.">
                <div class="admin-table-wrap overflow-x-auto">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Pelanggan</th>
                                <th>Kontak</th>
                                <th>Marketing</th>
                                <th>Pengajuan</th>
                                <th>Dibuat</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pelanggan as $item)
                                <tr>
                                    <td>
                                        <p class="font-semibold text-white">{{ $item->display_name }}</p>
                                        <p class="mt-1 text-sm text-slate-400">{{ $item->no_ktp ?: 'KTP belum terisi' }}</p>
                                    </td>
                                    <td>
                                        <p>{{ $item->email }}</p>
                                        <p class="mt-1 text-sm text-slate-400">{{ $item->no_telp }}</p>
                                    </td>
                                    <td>{{ $item->marketingOwner?->name ?? '-' }}</td>
                                    <td>{{ number_format($item->pengajuan_kredit_count) }} pengajuan</td>
                                    <td>{{ $item->created_at?->format('d M Y') }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.pelanggan.show', $item) }}" class="admin-text-link">Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-table-shell>

            <x-pagination :paginator="$pelanggan" surface-class="text-white" />
        @endif
    </div>
@endsection

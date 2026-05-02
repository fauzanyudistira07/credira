@extends('layouts.dashboard', [
    'title' => 'Pelanggan Marketing',
    'role' => 'marketing',
    'pageTitle' => 'Pelanggan Saya',
    'pageDescription' => 'Daftar pelanggan yang dimiliki akun marketing Anda lengkap dengan kontak, alamat singkat, dan riwayat pengajuan.',
])

@section('content')
    <div class="marketing-page">
        <section class="marketing-hero">
            <div class="grid gap-8 xl:grid-cols-[minmax(0,1.06fr)_minmax(320px,0.94fr)] xl:items-end">
                <div>
                    <p class="marketing-hero__eyebrow">Customer Desk</p>
                    <h2 class="marketing-hero__title">Kelola pelanggan milik Anda dalam workspace yang lebih rapi dan mudah ditindaklanjuti.</h2>
                    <p class="marketing-hero__copy">Cari cepat berdasarkan nama, email, atau telepon, lalu lanjutkan ke edit profil atau pembuatan pengajuan tanpa keluar dari alur marketing.</p>
                </div>
                <div class="marketing-soft-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/45">Total pelanggan</p>
                    <p class="mt-2 text-2xl font-semibold text-white">{{ $pelanggan->total() }}</p>
                    <p class="mt-2 text-sm leading-6 text-white/65">Semua data pelanggan tampil untuk kolaborasi tim marketing yang sedang login.</p>
                </div>
            </div>
        </section>

        <x-filter-toolbar
            class="marketing-surface"
            title="Kelola pelanggan milik Anda"
            description="Cari cepat berdasarkan identitas inti, lalu lanjutkan ke aksi berikutnya tanpa kehilangan konteks."
            :result-text="$pelanggan->total().' pelanggan ditemukan'"
            :active-filters="array_values(array_filter([
                filled($filters['q'] ?? null) ? 'Pencarian: '.($filters['q'] ?? '') : null,
            ]))"
            :reset-href="route('marketing.pelanggan.index')"
        >
            <div class="dashboard-panel__head !mb-0">
                <p class="dashboard-kicker">Customer Desk</p>
                <a href="{{ route('marketing.pelanggan.create') }}" class="btn-accent">Tambah Pelanggan</a>
            </div>

            <form method="GET" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto]">
                <div>
                    <label class="field-label">Cari pelanggan</label>
                    <input type="text" name="q" class="field-input" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama, email, atau nomor telepon" data-chip-label="Cari">
                </div>
                <div class="flex items-end gap-3">
                    <button type="submit" class="btn-accent">Cari</button>
                    @if (filled($filters['q'] ?? null))
                        <a href="{{ route('marketing.pelanggan.index') }}" class="btn-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </x-filter-toolbar>

        @if ($pelanggan->isEmpty())
            <x-empty-state
                title="Belum ada pelanggan"
                description="Mulai dengan menambahkan pelanggan baru agar flow marketing dari katalog motor ke pengajuan bisa langsung berjalan."
                action-label="Tambah Pelanggan"
                action-href="{{ route('marketing.pelanggan.create') }}"
            />
        @else
            <x-table-shell class="marketing-surface" title="Daftar pelanggan" description="Semua data pelanggan dapat diakses lintas akun marketing untuk memudahkan follow up tim.">
                <div class="marketing-table-wrap overflow-x-auto">
                    <table class="marketing-table">
                        <thead>
                            <tr>
                                <th class="px-4 py-4 font-semibold">Pelanggan</th>
                                <th class="px-4 py-4 font-semibold">Kontak</th>
                                <th class="px-4 py-4 font-semibold">Alamat Singkat</th>
                                <th class="px-4 py-4 font-semibold">Pengajuan</th>
                                <th class="px-4 py-4 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pelanggan as $item)
                                <tr>
                                    <td class="px-4 py-4">
                                        <p class="font-semibold text-slate-950">{{ $item->display_name }}</p>
                                        <p class="mt-1 text-xs uppercase tracking-[0.2em] text-orange-500">{{ $item->pekerjaan_default ?: 'Prospect' }}</p>
                                    </td>
                                    <td class="px-4 py-4 text-slate-600">
                                        <p>{{ $item->email }}</p>
                                        <p class="mt-1">{{ $item->no_telp }}</p>
                                    </td>
                                    <td class="px-4 py-4 text-slate-600">
                                        <p>{{ $item->kota1 ?: '-' }}</p>
                                        <p class="mt-1">{{ $item->propinsi1 ?: '-' }}</p>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700">{{ $item->pengajuan_kredit_count }} pengajuan</span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col items-stretch gap-2 sm:items-end">
                                            <a href="{{ route('marketing.pelanggan.show', $item) }}" class="dashboard-text-link">Detail</a>
                                            <a href="{{ route('marketing.pelanggan.edit', $item) }}" class="dashboard-text-link">Edit</a>
                                            <a href="{{ route('marketing.pengajuan.create', ['pelanggan_id' => $item->id]) }}" class="dashboard-text-link">Buat Pengajuan</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-table-shell>

            <x-pagination :paginator="$pelanggan" />
        @endif
    </div>
@endsection

@extends('layouts.admin', [
    'title' => 'Manajemen Motor',
    'heading' => 'Manajemen Motor',
    'subheading' => 'Kelola master motor, stok, foto, dan status tampil dengan UI yang tetap premium dan efisien.',
])

@section('content')
    <div class="space-y-6">
        <section class="admin-hero-panel">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <span class="admin-eyebrow">Motor Master</span>
                    <h2 class="mt-5 max-w-3xl text-3xl font-semibold tracking-[-0.04em] text-white sm:text-4xl">Admin bisa menjaga katalog Credira tetap rapi, aktif, dan siap dipakai seluruh marketing.</h2>
                    <p class="mt-4 max-w-2xl admin-copy">Filter berdasarkan nama, merk, jenis, dan status aktif lalu masuk ke detail atau edit tanpa perlu menyentuh schema database besar.</p>
                </div>
                <div class="admin-hero-actions">
                    <a href="{{ route('admin.motors.create') }}" class="btn-accent">Tambah Motor</a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="admin-secondary-stat">
                <p class="admin-secondary-stat__label">Total motors</p>
                <p class="admin-secondary-stat__value">{{ number_format($summary['total']) }}</p>
                <p class="admin-secondary-stat__copy">Semua unit pada master katalog.</p>
            </article>
            <article class="admin-secondary-stat">
                <p class="admin-secondary-stat__label">Aktif</p>
                <p class="admin-secondary-stat__value">{{ number_format($summary['active']) }}</p>
                <p class="admin-secondary-stat__copy">Unit yang siap tampil di katalog.</p>
            </article>
            <article class="admin-secondary-stat">
                <p class="admin-secondary-stat__label">Featured</p>
                <p class="admin-secondary-stat__value">{{ number_format($summary['featured']) }}</p>
                <p class="admin-secondary-stat__copy">Unit unggulan untuk highlight.</p>
            </article>
            <article class="admin-secondary-stat">
                <p class="admin-secondary-stat__label">Total stok</p>
                <p class="admin-secondary-stat__value">{{ number_format($summary['stock']) }}</p>
                <p class="admin-secondary-stat__copy">Akumulasi stok dari semua unit.</p>
            </article>
        </section>

        <x-filter-toolbar
            class="admin-filter-panel"
            title="Filter katalog motor"
            description="Pencarian, jenis, dan status aktif dipertahankan saat pagination untuk audit katalog yang lebih nyaman."
            :result-text="$motors->total().' motor ditemukan'"
            :active-filters="array_values(array_filter([
                filled($filters['q'] ?? null) ? 'Cari: '.($filters['q'] ?? '') : null,
                filled($filters['jenis_motor_id'] ?? null) ? 'Jenis dipilih' : null,
                filled($filters['status_aktif'] ?? null) ? 'Status: '.(($filters['status_aktif'] ?? '') === '1' ? 'Aktif' : 'Nonaktif') : null,
            ]))"
            :reset-href="route('admin.motors.index')"
        >
            <form method="GET" class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_0.9fr_0.8fr_auto]">
                <div>
                    <label class="field-label">Cari nama atau merk</label>
                    <input type="text" name="q" class="field-input" value="{{ $filters['q'] ?? '' }}" placeholder="Cari motor" data-chip-label="Cari">
                </div>
                <div>
                    <label class="field-label">Jenis motor</label>
                    <select name="jenis_motor_id" class="field-select" data-chip-label="Jenis">
                        <option value="">Semua jenis</option>
                        @foreach ($jenisMotors as $jenis)
                            <option value="{{ $jenis->id }}" @selected((string) ($filters['jenis_motor_id'] ?? '') === (string) $jenis->id)>{{ $jenis->merk }} - {{ $jenis->jenis }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label">Status</label>
                    <select name="status_aktif" class="field-select" data-chip-label="Status">
                        <option value="">Semua status</option>
                        <option value="1" @selected(($filters['status_aktif'] ?? '') === '1')>Aktif</option>
                        <option value="0" @selected(($filters['status_aktif'] ?? '') === '0')>Nonaktif</option>
                    </select>
                </div>
                <div class="flex items-end gap-3">
                    <button type="submit" class="btn-accent">Filter</button>
                    @if (filled($filters['q'] ?? null) || filled($filters['jenis_motor_id'] ?? null) || filled($filters['status_aktif'] ?? null))
                        <a href="{{ route('admin.motors.index') }}" class="btn-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </x-filter-toolbar>

        @if ($motors->isEmpty())
            <x-empty-state
                title="Belum ada data motor"
                description="Tambahkan motor baru agar marketing dan area publik memiliki katalog yang siap digunakan."
                action-label="Tambah Motor"
                action-href="{{ route('admin.motors.create') }}"
            />
        @else
            <x-table-shell class="admin-stream-panel" title="Master motor" description="Foto utama, harga, stok, dan status tampil dalam satu tabel yang lebih modern.">
                <div class="admin-table-wrap overflow-x-auto">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Motor</th>
                                <th>Jenis</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($motors as $item)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="admin-thumb">
                                                @if ($item->primary_image_url)
                                                    <img src="{{ $item->primary_image_url }}" alt="{{ $item->nama_motor }}" class="h-full w-full object-cover">
                                                @else
                                                    <span>{{ str($item->nama_motor)->substr(0, 2)->upper() }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="font-semibold text-white">{{ $item->nama_motor }}</p>
                                                <p class="mt-1 text-sm text-slate-400">{{ $item->merk }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $item->jenisMotor?->merk }} - {{ $item->jenisMotor?->jenis }}</td>
                                    <td>Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                                    <td>{{ number_format($item->stok) }}</td>
                                    <td>
                                        <div class="flex flex-wrap gap-2">
                                            <x-status-badge :status="$item->status_aktif ? 'tersedia' : 'nonaktif'" class="!border-white/10 !bg-white/8 !text-orange-100" />
                                            @if ($item->is_featured)
                                                <x-status-badge status="featured" class="!border-white/10 !bg-white/8 !text-orange-100" />
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <div class="flex flex-col items-end gap-2">
                                            <a href="{{ route('admin.motors.show', $item) }}" class="admin-text-link">Detail</a>
                                            <a href="{{ route('admin.motors.edit', $item) }}" class="admin-text-link">Edit</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-table-shell>

            <x-pagination :paginator="$motors" surface-class="text-white" />
        @endif
    </div>
@endsection

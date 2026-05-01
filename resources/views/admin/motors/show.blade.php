@extends('layouts.admin', [
    'title' => $motor->nama_motor,
    'heading' => 'Detail Motor',
    'subheading' => 'Lihat data lengkap master motor, gallery, spesifikasi, dan status tampil dalam satu halaman.',
])

@section('content')
    <div class="space-y-6">
        <section class="grid gap-6 xl:grid-cols-[1.02fr_0.98fr]">
            <div class="admin-hero-panel">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <span class="admin-eyebrow">Motor Detail</span>
                        <h2 class="mt-5 text-3xl font-semibold text-white sm:text-4xl">{{ $motor->nama_motor }}</h2>
                        <p class="mt-4 admin-copy">{{ $motor->deskripsi_motor ?: $motor->deskripsi ?: 'Belum ada deskripsi tambahan untuk unit ini.' }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <x-status-badge :status="$motor->status_aktif ? 'tersedia' : 'nonaktif'" class="!border-white/10 !bg-white/8 !text-orange-100" />
                        @if ($motor->is_featured)
                            <x-status-badge status="featured" class="!border-white/10 !bg-white/8 !text-orange-100" />
                        @endif
                    </div>
                </div>

                <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="admin-highlight-stat">
                        <p class="admin-highlight-stat__label">Merk</p>
                        <p class="admin-highlight-stat__value !text-2xl">{{ $motor->merk }}</p>
                    </div>
                    <div class="admin-highlight-stat">
                        <p class="admin-highlight-stat__label">Harga</p>
                        <p class="admin-highlight-stat__value !text-2xl">Rp {{ number_format($motor->harga_jual, 0, ',', '.') }}</p>
                    </div>
                    <div class="admin-highlight-stat">
                        <p class="admin-highlight-stat__label">Stok</p>
                        <p class="admin-highlight-stat__value !text-2xl">{{ number_format($motor->stok) }}</p>
                    </div>
                    <div class="admin-highlight-stat">
                        <p class="admin-highlight-stat__label">Jenis</p>
                        <p class="admin-highlight-stat__value !text-2xl">{{ $motor->jenisMotor?->jenis ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <section class="admin-detail-panel">
                <div class="dashboard-panel__head">
                    <div>
                        <p class="admin-eyebrow">Gallery</p>
                        <h3 class="mt-3 text-2xl font-semibold text-white">Foto unit</h3>
                    </div>
                    <a href="{{ route('admin.motors.edit', $motor) }}" class="btn-secondary">Edit Motor</a>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    @forelse ($motor->gallery_urls as $image)
                        <div class="admin-gallery-card">
                            <img src="{{ $image }}" alt="{{ $motor->nama_motor }}" class="h-full w-full object-cover">
                        </div>
                    @empty
                        <x-empty-state
                            title="Belum ada gambar"
                            description="Upload foto pada form edit untuk menampilkan gallery motor."
                            action-label="Edit Motor"
                            action-href="{{ route('admin.motors.edit', $motor) }}"
                        />
                    @endforelse
                </div>
            </section>
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            <section class="admin-detail-panel">
                <p class="admin-eyebrow">Spesifikasi</p>
                <div class="mt-5 grid gap-3">
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Warna</p><p class="admin-metric-card__value">{{ $motor->warna ?: '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Kapasitas Mesin</p><p class="admin-metric-card__value">{{ $motor->kapasitas_mesin ?: '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Transmisi</p><p class="admin-metric-card__value">{{ $motor->transmisi ?: '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Bahan Bakar</p><p class="admin-metric-card__value">{{ $motor->bahan_bakar ?: '-' }}</p></div>
                </div>
            </section>

            <section class="admin-detail-panel">
                <p class="admin-eyebrow">Produksi</p>
                <div class="mt-5 grid gap-3">
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Tahun Produksi</p><p class="admin-metric-card__value">{{ $motor->tahun_produksi ?: '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Berat</p><p class="admin-metric-card__value">{{ $motor->berat ? $motor->berat.' kg' : '-' }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Dibuat</p><p class="admin-metric-card__value">{{ $motor->created_at?->format('d M Y H:i') }}</p></div>
                    <div class="admin-metric-card"><p class="admin-metric-card__label">Diupdate</p><p class="admin-metric-card__value">{{ $motor->updated_at?->format('d M Y H:i') }}</p></div>
                </div>
            </section>

            <section class="admin-detail-panel">
                <p class="admin-eyebrow">Aksi Cepat</p>
                <div class="mt-5 flex flex-col gap-3">
                    <a href="{{ route('admin.motors.edit', $motor) }}" class="btn-accent w-full">Edit Motor</a>
                    <a href="{{ route('admin.motors.index') }}" class="btn-secondary w-full">Kembali ke List</a>
                </div>
            </section>
        </section>
    </div>
@endsection

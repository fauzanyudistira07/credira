@extends('layouts.dashboard', [
    'title' => $motor->nama_motor,
    'role' => 'marketing',
    'pageTitle' => 'Detail Motor',
    'pageDescription' => 'Ringkasan unit, spesifikasi utama, dan jalur cepat untuk mengubah minat pelanggan menjadi pengajuan.',
])

@section('content')
    <div class="marketing-page">
        <section class="grid gap-6 xl:grid-cols-[1.02fr_0.98fr]">
            <div class="overflow-hidden rounded-[2rem] border border-white/80 bg-[linear-gradient(135deg,#111827,#374151)] p-6 shadow-[0_30px_90px_-56px_rgba(15,23,42,0.72)]">
                <div class="grid gap-4">
                    <div class="flex min-h-[340px] items-center justify-center rounded-[1.8rem] bg-white/6 p-6">
                        @if ($motor->primary_image_url)
                            <img src="{{ $motor->primary_image_url }}" alt="{{ $motor->nama_motor }}" class="h-72 w-full object-contain">
                        @else
                            <div class="flex h-72 w-full items-center justify-center rounded-[1.4rem] border border-dashed border-white/12 text-white/60">Foto belum tersedia</div>
                        @endif
                    </div>

                    @if (count($motor->gallery_urls) > 1)
                        <div class="grid grid-cols-3 gap-3">
                            @foreach (array_slice($motor->gallery_urls, 0, 3) as $image)
                                <div class="flex h-24 items-center justify-center rounded-[1.3rem] bg-white/8 p-3">
                                    <img src="{{ $image }}" alt="{{ $motor->nama_motor }}" class="h-full w-full object-contain">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="marketing-surface">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="rounded-full border border-orange-200 bg-orange-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-orange-600">{{ $motor->merk }}</span>
                    <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">{{ $motor->jenisMotor?->jenis ?? 'Motor' }}</span>
                    @if ($motor->is_featured)
                        <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-amber-600">Featured</span>
                    @endif
                </div>

                <h2 class="mt-4 text-[2.35rem] font-semibold leading-tight tracking-[-0.04em] text-slate-950">{{ $motor->nama_motor }}</h2>
                <p class="mt-4 text-sm leading-7 text-slate-600">{{ $motor->deskripsi_motor ?? $motor->deskripsi ?? 'Belum ada deskripsi motor.' }}</p>

                <div class="marketing-inline-note mt-6">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400">Harga jual</p>
                    <p class="mt-3 text-3xl font-bold tracking-[-0.04em] text-slate-950">{{ $motor->formatted_harga_jual }}</p>
                    <p class="mt-2 text-sm text-slate-500">Status unit: {{ $motor->stok > 0 ? 'Siap ditawarkan' : 'Perlu cek ulang stok' }}</p>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">Kapasitas Mesin</p>
                        <p class="mt-2 font-semibold text-slate-950">{{ $motor->kapasitas_mesin ?: '-' }}</p>
                    </div>
                    <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">Transmisi</p>
                        <p class="mt-2 font-semibold text-slate-950">{{ $motor->transmisi ?: '-' }}</p>
                    </div>
                    <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">Bahan Bakar</p>
                        <p class="mt-2 font-semibold text-slate-950">{{ $motor->bahan_bakar ?: '-' }}</p>
                    </div>
                    <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">Warna</p>
                        <p class="mt-2 font-semibold text-slate-950">{{ $motor->warna ?: '-' }}</p>
                    </div>
                    <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">Tahun Produksi</p>
                        <p class="mt-2 font-semibold text-slate-950">{{ $motor->tahun_produksi ?: '-' }}</p>
                    </div>
                    <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 px-4 py-4">
                        <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">Stok</p>
                        <p class="mt-2 font-semibold text-slate-950">{{ $motor->stok }} unit</p>
                    </div>
                </div>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('marketing.pengajuan.create', ['motor_id' => $motor->id]) }}" class="btn-accent flex-1">Buat Pengajuan</a>
                    <a href="{{ route('marketing.motors.index') }}" class="btn-secondary flex-1">Kembali ke Katalog</a>
                </div>
            </div>
        </section>

        @if ($relatedMotors->isNotEmpty())
            <section class="marketing-surface">
                <div class="dashboard-panel__head">
                    <div>
                        <p class="dashboard-kicker">Unit Terkait</p>
                        <h3 class="marketing-section-title">Alternatif dalam kategori serupa</h3>
                    </div>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach ($relatedMotors as $relatedMotor)
                        <a href="{{ route('marketing.motors.show', $relatedMotor) }}" class="rounded-[1.5rem] border border-[#f2e9e1] bg-[#fffaf6] p-4 transition duration-200 hover:-translate-y-1 hover:shadow-[0_22px_58px_-40px_rgba(255,98,37,0.25)]">
                            <div class="flex h-36 items-center justify-center rounded-[1.2rem] bg-white p-4">
                                @if ($relatedMotor->primary_image_url)
                                    <img src="{{ $relatedMotor->primary_image_url }}" alt="{{ $relatedMotor->nama_motor }}" class="h-full w-full object-contain">
                                @endif
                            </div>
                            <p class="mt-4 text-sm font-semibold uppercase tracking-[0.22em] text-orange-500">{{ $relatedMotor->merk }}</p>
                            <p class="mt-2 text-lg font-semibold text-slate-950">{{ $relatedMotor->nama_motor }}</p>
                            <p class="mt-2 text-sm text-slate-600">{{ $relatedMotor->formatted_harga_jual }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection

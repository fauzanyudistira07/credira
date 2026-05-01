@extends('layouts.dashboard', [
    'title' => 'Katalog Motor',
    'role' => 'marketing',
    'pageTitle' => 'Katalog Motor',
    'pageDescription' => 'Pilih unit aktif yang siap ditawarkan ke pelanggan dan lanjutkan ke pengajuan kredit dengan cepat.',
])

@section('content')
    <div class="marketing-page">
        <section class="marketing-hero">
            <div class="grid gap-8 xl:grid-cols-[minmax(0,1.06fr)_minmax(320px,0.94fr)] xl:items-end">
                <div>
                    <p class="marketing-hero__eyebrow">Motor Catalog</p>
                    <h2 class="marketing-hero__title">Semua unit aktif Credira dalam katalog marketing yang cepat discan.</h2>
                    <p class="marketing-hero__copy">Gunakan pencarian, filter jenis motor, dan filter merk untuk menemukan motor yang paling relevan saat berbicara dengan calon pelanggan.</p>
                </div>
                <div class="marketing-soft-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/45">Katalog siap jual</p>
                    <p class="mt-2 text-lg font-semibold text-white">{{ $motors->total() }} unit aktif dalam hasil saat ini.</p>
                    <p class="mt-2 text-sm leading-6 text-white/65">Masuk ke detail unit untuk melihat spesifikasi, stok, dan langsung mulai pengajuan kredit.</p>
                </div>
            </div>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('marketing.pengajuan.create') }}" class="btn-accent">Buat Pengajuan</a>
                <a href="{{ route('marketing.dashboard') }}" class="btn-outline-light">Kembali ke Dashboard</a>
            </div>
        </section>

        <section class="marketing-surface">
            <form method="GET" class="grid gap-4 lg:grid-cols-[minmax(0,1.3fr)_0.8fr_0.7fr_auto]">
                <div>
                    <label class="field-label">Cari motor</label>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="field-input" placeholder="Cari nama motor atau merk">
                </div>
                <div>
                    <label class="field-label">Jenis motor</label>
                    <select name="jenis_motor_id" class="field-select">
                        <option value="">Semua jenis</option>
                        @foreach ($jenisMotors as $jenisMotor)
                            <option value="{{ $jenisMotor->id }}" @selected(($filters['jenis_motor_id'] ?? '') == $jenisMotor->id)>{{ $jenisMotor->jenis }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label">Merk</label>
                    <select name="merk" class="field-select">
                        <option value="">Semua merk</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand }}" @selected(($filters['merk'] ?? '') === $brand)>{{ $brand }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-3">
                    <button type="submit" class="btn-accent w-full lg:w-auto">Filter</button>
                    @if (filled($filters['q'] ?? null) || filled($filters['jenis_motor_id'] ?? null) || filled($filters['merk'] ?? null))
                        <a href="{{ route('marketing.motors.index') }}" class="btn-secondary w-full lg:w-auto">Reset</a>
                    @endif
                </div>
            </form>
        </section>

        @if ($motors->isEmpty())
            <x-empty-state
                title="Belum ada motor yang cocok"
                description="Coba ubah kata kunci atau filter. Hanya motor aktif yang tampil di area marketing."
                action-label="Reset Filter"
                action-href="{{ route('marketing.motors.index') }}"
            />
        @else
            <section class="grid gap-5 md:grid-cols-2 2xl:grid-cols-3">
                @foreach ($motors as $motor)
                    <article class="overflow-hidden rounded-[2rem] border border-white/80 bg-white/92 shadow-[0_28px_80px_-50px_rgba(15,23,42,0.2)] transition duration-300 hover:-translate-y-1.5 hover:border-orange-200 hover:shadow-[0_28px_80px_-42px_rgba(255,98,37,0.28)]">
                        <div class="relative overflow-hidden bg-[linear-gradient(135deg,#111827,#374151)] p-5">
                            @if ($motor->is_featured)
                                <span class="absolute left-5 top-5 rounded-full bg-orange-500 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-white">Featured</span>
                            @endif
                            <div class="flex min-h-[210px] items-center justify-center rounded-[1.6rem] bg-white/6 p-5">
                                @if ($motor->primary_image_url)
                                    <img src="{{ $motor->primary_image_url }}" alt="{{ $motor->nama_motor }}" class="h-44 w-full object-contain">
                                @else
                                    <div class="flex h-44 w-full items-center justify-center rounded-[1.4rem] border border-dashed border-white/10 text-sm text-white/60">Foto belum tersedia</div>
                                @endif
                            </div>
                        </div>
                        <div class="space-y-4 p-5">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full border border-orange-200 bg-orange-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-orange-600">{{ $motor->merk }}</span>
                                    <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500">{{ $motor->jenisMotor?->jenis ?? 'Motor' }}</span>
                                </div>
                                <h3 class="mt-3 text-2xl font-semibold tracking-[-0.03em] text-slate-950">{{ $motor->nama_motor }}</h3>
                                <p class="mt-2 text-sm leading-7 text-slate-600">{{ \Illuminate\Support\Str::limit($motor->deskripsi_motor ?? $motor->deskripsi, 120) }}</p>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="marketing-soft-card !p-4">
                                    <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">Harga</p>
                                    <p class="mt-2 font-semibold text-slate-950">{{ $motor->formatted_harga_jual }}</p>
                                </div>
                                <div class="marketing-soft-card !p-4">
                                    <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">Stok</p>
                                    <p class="mt-2 font-semibold text-slate-950">{{ $motor->stok }} unit</p>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2 text-xs font-semibold text-slate-600">
                                @if ($motor->warna)
                                    <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1">{{ $motor->warna }}</span>
                                @endif
                                @if ($motor->kapasitas_mesin)
                                    <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1">{{ $motor->kapasitas_mesin }}</span>
                                @endif
                            </div>

                            <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                                <a href="{{ route('marketing.motors.show', $motor) }}" class="btn-accent flex-1">Lihat Detail</a>
                                <a href="{{ route('marketing.pengajuan.create', ['motor_id' => $motor->id]) }}" class="btn-secondary flex-1">Buat Pengajuan</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>

            <div>
                {{ $motors->links() }}
            </div>
        @endif
    </div>
@endsection

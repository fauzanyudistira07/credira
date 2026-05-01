@extends('layouts.public', ['title' => $motor->nama_motor])

@section('content')
    <section class="shell pt-28 pb-28 sm:pt-32 sm:pb-24 lg:pt-36 lg:pb-20">
        <div class="page-banner">
            <span class="section-kicker section-kicker-dark">Detail motor</span>
            <h1>{{ $motor->nama_motor }}</h1>
            <p>Lihat spesifikasi utama, estimasi pembiayaan, dan langkah lanjutan untuk mengajukan kredit atas unit yang Anda pilih.</p>
        </div>

        <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1.08fr)_minmax(0,0.92fr)] lg:items-start">
            <div class="order-2 space-y-5 lg:order-1">
                <div class="surface overflow-hidden rounded-[2rem] bg-[#eef2f7]">
                    <div class="flex min-h-[280px] items-center justify-center p-5 sm:min-h-[360px] sm:p-8 lg:min-h-[520px]">
                        @if ($motor->primary_image_url)
                            <img src="{{ $motor->primary_image_url }}" alt="{{ $motor->nama_motor }}" class="h-full max-h-[460px] w-full object-contain">
                        @else
                            <div class="flex h-full min-h-[280px] w-full items-center justify-center rounded-[1.6rem] border border-dashed border-slate-300 bg-white/60 px-6 text-center text-sm font-semibold uppercase tracking-[0.22em] text-slate-500 sm:min-h-[360px] lg:min-h-[460px]">
                                Gambar motor belum tersedia
                            </div>
                        @endif
                    </div>
                </div>

                @if (! empty($motor->gallery_urls))
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                        @foreach (collect($motor->gallery_urls)->take(3) as $imageUrl)
                            <div class="surface overflow-hidden rounded-[1.7rem] bg-[#eef2f7]">
                                <div class="flex h-32 items-center justify-center p-3 sm:h-40">
                                    <img src="{{ $imageUrl }}" alt="{{ $motor->nama_motor }}" class="h-full w-full object-contain">
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="order-1 space-y-6 lg:order-2">
                <div class="content-panel p-6 sm:p-8">
                    <p class="text-[11px] uppercase tracking-[0.28em] text-orange-200">{{ $motor->merk }} &bull; {{ $motor->jenisMotor->jenis }}</p>
                    <h2 class="mt-3 text-3xl font-semibold leading-tight text-white sm:text-4xl">{{ $motor->nama_motor }}</h2>
                    <p class="mt-4 text-3xl font-semibold tracking-[-0.03em] text-white sm:text-[2.6rem]">Rp {{ number_format($motor->harga_jual, 0, ',', '.') }}</p>
                    <p class="mt-4 text-sm leading-7 text-slate-300 sm:text-base">{{ $motor->deskripsi }}</p>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div class="spec-grid-card">
                            <p class="text-sm text-slate-400">Warna</p>
                            <p class="mt-2 font-semibold leading-7 text-white">{{ $motor->warna }}</p>
                        </div>
                        <div class="spec-grid-card">
                            <p class="text-sm text-slate-400">Stok</p>
                            <p class="mt-2 font-semibold text-white">{{ $motor->stok }} unit</p>
                        </div>
                        <div class="spec-grid-card">
                            <p class="text-sm text-slate-400">Mesin</p>
                            <p class="mt-2 font-semibold text-white">{{ $motor->kapasitas_mesin }}</p>
                        </div>
                        <div class="spec-grid-card">
                            <p class="text-sm text-slate-400">Transmisi</p>
                            <p class="mt-2 font-semibold text-white">{{ $motor->transmisi }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8">
            <div class="content-panel p-6 sm:p-7 lg:p-8">
                <p class="text-[11px] uppercase tracking-[0.28em] text-orange-200">Simulasi singkat</p>
                <h2 class="mt-3 text-2xl font-semibold text-white sm:text-3xl">Estimasi kredit</h2>

                <form class="mt-5 grid gap-4" data-simulation-form data-simulation-target="#detail-simulation-output">
                    @csrf
                    <input type="hidden" name="motor_id" value="{{ $motor->id }}">

                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="field-label">Uang muka</label>
                            <input type="number" name="dp" class="field-input" value="{{ (int) round($motor->harga_jual * 0.2) }}">
                        </div>
                        <div>
                            <label class="field-label">Tenor</label>
                            <select name="jenis_cicilan_id" class="field-select">
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}" class="bg-slate-900 text-white">{{ $plan->nama_cicilan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Asuransi</label>
                            <select name="asuransi_id" class="field-select">
                                <option value="" class="bg-slate-900 text-white">Tanpa asuransi</option>
                                @foreach ($insurances as $insurance)
                                    <option value="{{ $insurance->id }}" class="bg-slate-900 text-white">{{ $insurance->nama_asuransi }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn-accent w-full justify-center" data-label="Hitung Estimasi">Hitung estimasi</button>
                </form>

                <div id="detail-simulation-output" class="mt-6">
                    @if ($simulation)
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="spec-grid-card">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Pokok kredit</p>
                                <p class="mt-2 text-xl font-semibold text-white">Rp {{ number_format($simulation['pokok_kredit'], 0, ',', '.') }}</p>
                            </div>
                            <div class="spec-grid-card">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Angsuran per bulan</p>
                                <p class="mt-2 text-xl font-semibold text-white">Rp {{ number_format($simulation['angsuran_per_bulan'], 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="motor-detail-actions mt-6 flex gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-accent flex-1">Buka dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn-accent flex-1">Masuk untuk melanjutkan</a>
                    @endauth
                    <a href="{{ route('home') }}#catalog" class="btn-secondary flex-1">Kembali ke katalog</a>
                </div>
            </div>
        </div>
    </section>

    <div class="mobile-action-bar">
        <div class="flex items-center justify-between gap-4">
            <div class="min-w-0">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Harga mulai</p>
                <p class="mt-1 text-lg font-semibold text-slate-900 sm:text-xl">Rp {{ number_format($motor->harga_jual, 0, ',', '.') }}</p>
            </div>
            @auth
                <a href="{{ route('dashboard') }}" class="btn-accent !min-h-11 !px-5 !py-3">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn-accent !min-h-11 !px-5 !py-3">Masuk</a>
            @endauth
        </div>
    </div>
@endsection

@extends('layouts.public', ['title' => 'Daftar Motor'])

@section('content')
    @php
        $activeFilters = collect($filters)->filter(fn ($value) => filled($value))->count();
        $startingPrice = $motors->count() ? $motors->min('harga_jual') : null;
        $endingPrice = $motors->count() ? $motors->max('harga_jual') : null;
    @endphp

    <section class="shell pt-28 pb-14 sm:pt-32 lg:pt-36 lg:pb-20" x-data="asyncList({ skeletonCount: 6 })">
        <div class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-[linear-gradient(180deg,rgba(13,18,33,0.96),rgba(9,14,28,0.98))] px-5 py-8 text-white shadow-[0_36px_120px_-65px_rgba(0,0,0,0.7)] sm:px-8 lg:px-10 lg:py-10">
            <div class="absolute inset-0 bg-[linear-gradient(132deg,rgba(255,97,43,0.92)_0%,rgba(255,122,56,0.78)_16%,rgba(255,122,56,0.12)_22%,transparent_34%),radial-gradient(circle_at_top_right,rgba(255,112,43,0.18),transparent_24rem)]"></div>
            <div class="relative z-10 grid gap-8 lg:grid-cols-[minmax(0,1.1fr)_minmax(280px,0.9fr)] lg:items-end">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/8 px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.28em] text-white/72">
                        Katalog Motor
                    </span>
                    <h1 class="mt-5 max-w-4xl text-[2.3rem] font-semibold leading-[1.02] tracking-[-0.04em] text-white sm:text-5xl lg:text-[4rem]">
                        Temukan unit yang sesuai dengan
                        <span class="text-[#ff8d57]">preferensi, kebutuhan, dan rencana cicilan Anda.</span>
                    </h1>
                    <p class="mt-5 max-w-3xl text-sm leading-7 text-white/68 sm:text-base">
                        Gunakan filter untuk mempersempit pilihan berdasarkan merek, tipe motor, kisaran harga, warna, dan urutan tampilan tanpa membuat halaman terasa padat.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-white/6 p-4 backdrop-blur-sm">
                        <p class="text-[11px] uppercase tracking-[0.24em] text-white/45">Unit tampil</p>
                        <p class="mt-2 text-2xl font-semibold text-white">{{ $motors->total() }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/6 p-4 backdrop-blur-sm">
                        <p class="text-[11px] uppercase tracking-[0.24em] text-white/45">Filter aktif</p>
                        <p class="mt-2 text-2xl font-semibold text-white">{{ $activeFilters }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/6 p-4 backdrop-blur-sm">
                        <p class="text-[11px] uppercase tracking-[0.24em] text-white/45">Harga awal</p>
                        <p class="mt-2 text-lg font-semibold text-white">
                            {{ $startingPrice ? 'Rp '.number_format($startingPrice, 0, ',', '.') : '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 grid gap-8 xl:grid-cols-[320px_minmax(0,1fr)]">
            <aside class="xl:sticky xl:top-32 xl:self-start">
                <div class="rounded-[1.9rem] border border-white/10 bg-[linear-gradient(180deg,rgba(13,18,33,0.96),rgba(8,11,24,0.98))] p-6 shadow-[0_30px_90px_-55px_rgba(0,0,0,0.72)]">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-orange-200/80">Filter Pencarian</p>
                            <h2 class="mt-3 text-2xl font-semibold text-white">Cari motor yang paling pas</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-300">Atur preferensi pencarian dan hasil akan diperbarui secara dinamis.</p>
                        </div>
                        <a href="{{ route('motors.index') }}" class="inline-flex items-center rounded-full border border-white/10 bg-white/6 px-3 py-2 text-xs font-semibold text-white/72 transition hover:bg-white/10 hover:text-white">
                            Reset
                        </a>
                    </div>

                    <form method="GET" class="mt-6 grid gap-4" x-ref="form">
                        <div>
                            <label class="field-label !text-white/82">Cari motor</label>
                            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="field-input !rounded-2xl !border-white/10 !bg-white/6 !text-white placeholder:!text-slate-500 focus:!border-orange-300/40 focus:!ring-orange-400/10" placeholder="Nama atau merk" data-chip-label="Cari">
                        </div>
                        <div>
                            <label class="field-label !text-white/82">Merk</label>
                            <select name="merk" class="field-select !rounded-2xl !border-white/10 !bg-white/6 !text-white focus:!border-orange-300/40 focus:!ring-orange-400/10" data-chip-label="Merk">
                                <option value="">Semua merk</option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand }}" @selected(($filters['merk'] ?? '') === $brand) class="text-slate-900">{{ $brand }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="field-label !text-white/82">Jenis motor</label>
                            <select name="jenis_motor_id" class="field-select !rounded-2xl !border-white/10 !bg-white/6 !text-white focus:!border-orange-300/40 focus:!ring-orange-400/10" data-chip-label="Jenis">
                                <option value="">Semua jenis</option>
                                @foreach ($jenisMotors as $jenis)
                                    <option value="{{ $jenis->id }}" @selected((string) ($filters['jenis_motor_id'] ?? '') === (string) $jenis->id) class="text-slate-900">{{ $jenis->jenis }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                            <div>
                                <label class="field-label !text-white/82">Harga minimum</label>
                                <input type="number" name="min_price" value="{{ $filters['min_price'] ?? '' }}" class="field-input !rounded-2xl !border-white/10 !bg-white/6 !text-white placeholder:!text-slate-500 focus:!border-orange-300/40 focus:!ring-orange-400/10" placeholder="Rp 0" data-chip-label="Min">
                            </div>
                            <div>
                                <label class="field-label !text-white/82">Harga maksimum</label>
                                <input type="number" name="max_price" value="{{ $filters['max_price'] ?? '' }}" class="field-input !rounded-2xl !border-white/10 !bg-white/6 !text-white placeholder:!text-slate-500 focus:!border-orange-300/40 focus:!ring-orange-400/10" placeholder="Rp 0" data-chip-label="Max">
                            </div>
                        </div>
                        <div>
                            <label class="field-label !text-white/82">Warna</label>
                            <input type="text" name="warna" value="{{ $filters['warna'] ?? '' }}" class="field-input !rounded-2xl !border-white/10 !bg-white/6 !text-white placeholder:!text-slate-500 focus:!border-orange-300/40 focus:!ring-orange-400/10" placeholder="Contoh: Hitam" data-chip-label="Warna">
                        </div>
                        <div>
                            <label class="field-label !text-white/82">Urutkan</label>
                            <select name="sort" class="field-select !rounded-2xl !border-white/10 !bg-white/6 !text-white focus:!border-orange-300/40 focus:!ring-orange-400/10" data-chip-label="Urut">
                                <option value="" class="text-slate-900">A-Z</option>
                                <option value="termurah" @selected(($filters['sort'] ?? '') === 'termurah') class="text-slate-900">Termurah</option>
                                <option value="termahal" @selected(($filters['sort'] ?? '') === 'termahal') class="text-slate-900">Termahal</option>
                                <option value="terbaru" @selected(($filters['sort'] ?? '') === 'terbaru') class="text-slate-900">Terbaru</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-accent mt-2 w-full justify-center">
                            Terapkan Filter
                        </button>
                    </form>
                </div>
            </aside>

            <div class="min-w-0">
                <div class="rounded-[1.8rem] border border-white/10 bg-[linear-gradient(180deg,rgba(13,18,33,0.82),rgba(9,14,28,0.9))] p-5 shadow-[0_26px_70px_-50px_rgba(0,0,0,0.62)] sm:p-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-white/42">Hasil pencarian</p>
                            <h2 class="mt-2 text-2xl font-semibold text-white sm:text-3xl">
                                {{ $motors->total() }} motor tersedia
                            </h2>
                            <p class="mt-2 text-sm text-slate-300">
                                @if ($endingPrice)
                                    Rentang harga saat ini dari Rp {{ number_format($startingPrice, 0, ',', '.') }} sampai Rp {{ number_format($endingPrice, 0, ',', '.') }}.
                                @else
                                    Belum ada unit yang cocok dengan filter saat ini.
                                @endif
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2" x-ref="chips"></div>
                    </div>

                    <div class="mt-6 grid gap-6 md:grid-cols-2 2xl:grid-cols-3" x-ref="results" data-async-results>
                        @forelse ($motors as $motor)
                            @include('public.partials.motor-card', ['motor' => $motor])
                        @empty
                            <div class="md:col-span-2 2xl:col-span-3">
                                <x-empty-state
                                    title="Belum ada motor yang cocok"
                                    description="Coba ubah pencarian atau filter yang aktif untuk melihat unit lain."
                                    action-label="Reset Pencarian"
                                    action-href="{{ route('motors.index') }}"
                                />
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-8" x-ref="pagination" data-async-pagination>
                        {{ $motors->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

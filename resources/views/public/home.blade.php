@extends('layouts.public', ['title' => 'Credira - Pembiayaan Motor Premium'])

@section('content')
    @php
        $heroMotor = $featuredMotors->first();
        $catalogMotors = $featuredMotors->take(6);
        $faqsToShow = $faqs->take(5);

        $advantages = [
            [
                'title' => 'Approval lebih terarah',
                'text' => 'Alur pengajuan dibuat ringkas dengan proses review awal yang jelas dan cepat dipahami.',
            ],
            [
                'title' => 'Pilihan unit premium',
                'text' => 'Kurasi motor premium dengan visual rapi, detail unit jelas, dan posisi harga transparan.',
            ],
            [
                'title' => 'Simulasi transparan',
                'text' => 'Perkirakan cicilan, biaya admin, dan total pembayaran langsung dari halaman utama.',
            ],
            [
                'title' => 'Pendampingan sampai unit tiba',
                'text' => 'Status aplikasi, pembayaran, dan serah terima dirancang tetap mudah dipantau.',
            ],
        ];

        $trustPoints = [
            'Proses digital lebih cepat',
            'Tenor fleksibel sesuai kebutuhan',
            'Didukung tim verifikasi berpengalaman',
        ];

        $stats = [
            ['value' => $featuredMotors->count(), 'label' => 'Model unggulan'],
            ['value' => $installmentOptions->count(), 'label' => 'Pilihan tenor'],
            ['value' => '1x24 jam', 'label' => 'Review awal'],
        ];
    @endphp

    <section id="hero" data-section class="relative overflow-hidden bg-[#111111] text-white">
        <div class="absolute inset-0">
            <div class="absolute inset-x-0 top-0 h-32 bg-gradient-to-b from-[#111111] via-[#111111]/95 to-transparent"></div>
            <div class="absolute -left-24 top-24 h-72 w-72 rounded-full bg-orange-500/20 blur-3xl"></div>
            <div class="absolute right-0 top-0 h-full w-full bg-[radial-gradient(circle_at_top_right,rgba(255,112,43,0.18),transparent_28rem)]"></div>
            <div class="absolute inset-0 bg-[linear-gradient(120deg,rgba(255,88,43,0.16)_0%,rgba(255,88,43,0.12)_16%,transparent_30%),linear-gradient(180deg,rgba(255,255,255,0.04),rgba(255,255,255,0))]"></div>
        </div>

        <div class="shell relative z-10 pt-28 pb-16 sm:pt-32 lg:pt-36 lg:pb-24">
            <div class="grid gap-12 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)] lg:items-center">
                <div class="max-w-2xl" data-reveal>
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/8 px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.28em] text-white/72">
                        Premium Financing Experience
                    </span>

                    <h1 class="mt-6 max-w-xl text-[2.6rem] font-semibold leading-[0.95] tracking-[-0.04em] text-white sm:text-5xl lg:text-[4.4rem]">
                        Pembiayaan motor premium yang terasa
                        <span class="bg-gradient-to-r from-[#ffb088] via-[#ff7a38] to-[#ff4d2f] bg-clip-text text-transparent">lebih presisi dan modern</span>
                    </h1>

                    <p class="mt-6 max-w-xl text-sm leading-7 text-white/70 sm:text-base">
                        Credira membantu Anda memilih unit unggulan, menghitung cicilan, dan memulai pengajuan dengan tampilan yang tegas, nyaman dipakai, dan tetap ringan di semua device.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="#simulation" class="btn-accent min-w-[200px]">
                            Coba Simulasi Sekarang
                        </a>
                        <a href="#catalog" class="btn-outline-light min-w-[200px]">
                            Lihat Katalog Model
                        </a>
                    </div>

                    <div class="mt-8 flex flex-wrap gap-3 text-sm text-white/72">
                        @foreach ($trustPoints as $point)
                            <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/6 px-4 py-2 backdrop-blur-sm">
                                <span class="h-2 w-2 rounded-full bg-[#ff7a38] shadow-[0_0_14px_rgba(255,122,56,0.8)]"></span>
                                <span>{{ $point }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-10 grid gap-4 sm:grid-cols-3">
                        @foreach ($stats as $stat)
                            <div class="rounded-2xl border border-white/10 bg-white/6 px-5 py-4 shadow-[0_24px_60px_-40px_rgba(0,0,0,0.7)] backdrop-blur-sm" data-reveal>
                                <p class="text-2xl font-semibold tracking-[-0.03em] text-white">{{ $stat['value'] }}</p>
                                <p class="mt-1 text-[11px] uppercase tracking-[0.22em] text-white/55">{{ $stat['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="relative" data-reveal>
                    <div class="absolute inset-x-6 bottom-10 h-8 rounded-full bg-black/50 blur-xl"></div>
                    <div class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-[linear-gradient(180deg,rgba(255,255,255,0.08),rgba(255,255,255,0.03))] p-5 shadow-[0_40px_120px_-60px_rgba(0,0,0,0.9)] backdrop-blur-xl sm:p-6">
                        <div class="absolute right-4 top-4 rounded-full border border-white/10 bg-white/8 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.24em] text-white/70">
                            Featured Unit
                        </div>

                        @if ($heroMotor)
                            <div class="rounded-[1.75rem] bg-[radial-gradient(circle_at_center,rgba(255,122,56,0.14),transparent_58%),linear-gradient(180deg,rgba(255,255,255,0.04),rgba(255,255,255,0.01))] px-4 py-8 sm:px-6">
                                @if ($heroMotor->primary_image_url)
                                    <img
                                        src="{{ $heroMotor->primary_image_url }}"
                                        alt="{{ $heroMotor->nama_motor }}"
                                        class="mx-auto h-[220px] w-full max-w-xl object-contain drop-shadow-[0_24px_40px_rgba(0,0,0,0.55)] transition duration-500 motion-safe:hover:-translate-y-1 motion-safe:hover:scale-[1.02] sm:h-[280px] lg:h-[340px]"
                                    >
                                @else
                                    <div class="flex min-h-[220px] items-center justify-center rounded-[1.5rem] border border-dashed border-white/15 bg-white/5 px-6 text-center text-sm font-semibold uppercase tracking-[0.22em] text-white/55 sm:min-h-[280px] lg:min-h-[340px]">
                                        Gambar unit belum tersedia
                                    </div>
                                @endif
                            </div>

                            <div class="mt-5 grid gap-4 sm:grid-cols-[1fr_auto] sm:items-end">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-[#ffae86]">{{ strtoupper($heroMotor->merk) }}</p>
                                    <h2 class="mt-2 text-2xl font-semibold tracking-[-0.03em] text-white sm:text-[2rem]">{{ $heroMotor->nama_motor }}</h2>
                                    <p class="mt-3 max-w-lg text-sm leading-7 text-white/65">
                                        {{ \Illuminate\Support\Str::limit($heroMotor->deskripsi ?: 'Unit premium dengan karakter desain tegas, posisi riding nyaman, dan proses pembiayaan yang dibuat lebih transparan bersama Credira.', 140) }}
                                    </p>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-black/20 px-5 py-4 text-left sm:min-w-[190px]">
                                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/48">Harga mulai</p>
                                    <p class="mt-2 text-2xl font-semibold text-white">Rp {{ number_format($heroMotor->harga_jual, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @else
                            <div class="flex min-h-[420px] items-center justify-center rounded-[1.75rem] border border-dashed border-white/15 bg-white/5 px-6 text-center text-white/70">
                                Motor unggulan akan tampil di sini setelah data tersedia.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="catalog" data-section class="shell py-14 sm:py-16 lg:py-24">
        <div class="mx-auto max-w-3xl text-center" data-reveal>
            <span class="section-kicker section-kicker-accent">Pilihan Model</span>
            <h2 class="mt-5 text-3xl font-semibold tracking-[-0.04em] text-white sm:text-4xl lg:text-5xl">
                Katalog motor premium dengan tampilan lebih solid dan mudah dipilih
            </h2>
            <p class="mt-4 text-sm leading-7 text-slate-300 sm:text-base">
                Setiap unit dirancang tampil lebih premium, informatif, dan nyaman discan baik di mobile maupun desktop.
            </p>
        </div>

        <div class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($catalogMotors as $motor)
                <article
                    class="group relative overflow-hidden rounded-[1.9rem] border border-slate-200/80 bg-white p-5 shadow-[0_24px_80px_-50px_rgba(15,23,42,0.18)] transition duration-300 ease-out hover:-translate-y-1.5 hover:border-orange-200 hover:shadow-[0_30px_100px_-52px_rgba(255,106,43,0.3)] sm:p-6"
                    data-reveal
                >
                    <div class="absolute right-5 top-5 rounded-full border border-orange-100 bg-orange-50 px-3 py-1 text-[11px] font-semibold text-orange-600">
                        4.9
                    </div>

                    <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-orange-600">{{ strtoupper($motor->merk) }}</p>
                    <h3 class="mt-3 pr-16 text-xl font-semibold tracking-[-0.03em] text-slate-950 sm:text-2xl">{{ $motor->nama_motor }}</h3>

                    <div class="mt-5 flex h-52 items-center justify-center rounded-[1.6rem] border border-slate-100 bg-[linear-gradient(180deg,#fff7f2,#ffffff)] p-4">
                        @if ($motor->primary_image_url)
                            <img
                                src="{{ $motor->primary_image_url }}"
                                alt="{{ $motor->nama_motor }}"
                                class="h-full w-full object-contain transition duration-300 group-hover:scale-[1.04]"
                            >
                        @else
                            <div class="flex h-full w-full items-center justify-center text-center text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">
                                Gambar belum tersedia
                            </div>
                        @endif
                    </div>

                    <p class="mt-5 text-sm leading-7 text-slate-600">
                        {{ \Illuminate\Support\Str::limit($motor->deskripsi ?: 'Motor premium dengan build berkualitas, karakter desain kuat, dan cocok untuk pembiayaan yang lebih fleksibel.', 125) }}
                    </p>

                    <div class="mt-5 flex items-center justify-between gap-4">
                        <div>
                            <p class="text-[11px] uppercase tracking-[0.22em] text-slate-400">Harga</p>
                            <p class="mt-1 text-xl font-semibold text-slate-950">Rp {{ number_format($motor->harga_jual, 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600">
                            Premium Choice
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('motors.show', $motor) }}" class="btn-accent w-full justify-center !min-h-12 !px-5 !py-3">
                            Lihat Detail
                        </a>
                    </div>
                </article>
            @empty
                <div class="sm:col-span-2 xl:col-span-3">
                    <div class="rounded-[1.9rem] border border-dashed border-slate-300 bg-slate-50/80 px-6 py-12 text-center text-slate-500">
                        Katalog motor unggulan belum tersedia.
                    </div>
                </div>
            @endforelse
        </div>
    </section>

    <section id="advantages" data-section class="bg-[#f7f3ee] py-14 sm:py-16 lg:py-24">
        <div class="shell">
            <div class="grid gap-10 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] lg:items-start">
                <div data-reveal>
                    <span class="section-kicker section-kicker-accent">Keunggulan Credira</span>
                    <h2 class="mt-5 text-3xl font-semibold tracking-[-0.04em] text-slate-950 sm:text-4xl">
                        Visual lebih bersih, pengalaman pengajuan terasa lebih meyakinkan
                    </h2>
                    <p class="mt-4 max-w-xl text-sm leading-7 text-slate-600 sm:text-base">
                        Credira memadukan nuansa premium fintech dan otomotif agar pengguna bisa lebih cepat memahami unit, simulasi, dan langkah pengajuan tanpa merasa sesak.
                    </p>

                    <div class="mt-8 rounded-[1.8rem] bg-[#171717] p-6 text-white shadow-[0_32px_80px_-50px_rgba(0,0,0,0.6)]" data-reveal>
                        <p class="text-[11px] uppercase tracking-[0.28em] text-white/48">Mengapa terasa lebih premium</p>
                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-white/10 bg-white/6 p-4">
                                <p class="text-2xl font-semibold">Clean</p>
                                <p class="mt-2 text-sm leading-6 text-white/65">Whitespace lebih lega, hierarchy lebih kuat, dan konten inti lebih cepat terlihat.</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/6 p-4">
                                <p class="text-2xl font-semibold">Focused</p>
                                <p class="mt-2 text-sm leading-6 text-white/65">CTA, simulasi, dan katalog menjadi fokus tanpa membuat halaman terasa berat.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2" data-reveal>
                    @foreach ($advantages as $item)
                        <article class="rounded-[1.7rem] border border-slate-200/80 bg-white p-5 shadow-[0_20px_60px_-46px_rgba(15,23,42,0.18)] transition duration-300 hover:-translate-y-1 hover:border-orange-200 hover:shadow-[0_28px_70px_-45px_rgba(255,106,43,0.24)]">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#ff5e2b,#ff8a37)] text-sm font-semibold text-white shadow-[0_18px_36px_-18px_rgba(255,94,43,0.55)]">
                                0{{ $loop->iteration }}
                            </div>
                            <h3 class="mt-5 text-xl font-semibold tracking-[-0.03em] text-slate-950">{{ $item['title'] }}</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600">{{ $item['text'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section id="simulation" data-section class="shell py-14 sm:py-16 lg:py-24">
        <div class="relative overflow-hidden rounded-[2rem] border border-slate-200/80 bg-[#111111] px-5 py-6 text-white shadow-[0_40px_120px_-64px_rgba(15,23,42,0.7)] sm:px-7 sm:py-8 lg:px-10 lg:py-10">
            <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(255,96,43,0.24),transparent_30%),radial-gradient(circle_at_top_right,rgba(255,122,56,0.18),transparent_24rem)]"></div>
            <div class="relative z-10 grid gap-8 lg:grid-cols-[minmax(0,0.82fr)_minmax(0,1.18fr)] lg:items-start">
                <div data-reveal>
                    <span class="section-kicker section-kicker-dark">Simulasi Cicilan</span>
                    <h2 class="mt-5 text-3xl font-semibold tracking-[-0.04em] text-white sm:text-4xl">
                        Hitung skema pembiayaan dengan panel yang lebih jelas dan terasa premium
                    </h2>
                    <p class="mt-4 max-w-lg text-sm leading-7 text-white/68 sm:text-base">
                        Masukkan unit, tenor, DP, dan opsi asuransi. Hasil simulasi akan tampil lebih cepat dibaca dengan susunan yang konsisten.
                    </p>

                    <div class="mt-8 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl border border-white/10 bg-white/6 p-4">
                            <p class="text-[11px] uppercase tracking-[0.24em] text-white/48">Estimasi</p>
                            <p class="mt-2 text-lg font-semibold text-white">Real-time</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/6 p-4">
                            <p class="text-[11px] uppercase tracking-[0.24em] text-white/48">Fokus</p>
                            <p class="mt-2 text-lg font-semibold text-white">Lebih ringkas</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/6 p-4">
                            <p class="text-[11px] uppercase tracking-[0.24em] text-white/48">Output</p>
                            <p class="mt-2 text-lg font-semibold text-white">Mudah discan</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-5" data-reveal>
                    <div class="rounded-[1.8rem] border border-white/10 bg-white/6 p-5 backdrop-blur-xl sm:p-6">
                        <form class="grid gap-5" data-simulation-form data-simulation-target="#home-simulation-output">
                            @csrf
                            <div class="grid gap-5 sm:grid-cols-2">
                                <label class="block">
                                    <span class="mb-2 block text-sm font-medium text-white/82">Pilih motor</span>
                                    <select
                                        name="motor_id"
                                        class="w-full rounded-2xl border border-white/10 bg-white/8 px-4 py-3.5 text-sm text-white outline-none transition duration-200 focus:border-orange-300 focus:bg-white/10 focus:ring-2 focus:ring-orange-400/20"
                                    >
                                        @foreach ($featuredMotors as $motor)
                                            <option value="{{ $motor->id }}" class="text-slate-900">{{ $motor->nama_motor }}</option>
                                        @endforeach
                                    </select>
                                </label>

                                <label class="block">
                                    <span class="mb-2 block text-sm font-medium text-white/82">Tenor cicilan</span>
                                    <select
                                        name="jenis_cicilan_id"
                                        class="w-full rounded-2xl border border-white/10 bg-white/8 px-4 py-3.5 text-sm text-white outline-none transition duration-200 focus:border-orange-300 focus:bg-white/10 focus:ring-2 focus:ring-orange-400/20"
                                    >
                                        @foreach ($installmentOptions as $option)
                                            <option value="{{ $option->id }}" class="text-slate-900">{{ $option->nama_cicilan }}</option>
                                        @endforeach
                                    </select>
                                </label>

                                <label class="block">
                                    <span class="mb-2 block text-sm font-medium text-white/82">Down payment</span>
                                    <input
                                        type="number"
                                        name="dp"
                                        value="6000000"
                                        placeholder="Masukkan nominal DP"
                                        class="w-full rounded-2xl border border-white/10 bg-white/8 px-4 py-3.5 text-sm text-white outline-none transition duration-200 placeholder:text-white/35 focus:border-orange-300 focus:bg-white/10 focus:ring-2 focus:ring-orange-400/20"
                                    >
                                    <span class="mt-2 block text-xs text-white/45">Gunakan nominal DP yang paling mendekati rencana Anda.</span>
                                </label>

                                <label class="block">
                                    <span class="mb-2 block text-sm font-medium text-white/82">Asuransi</span>
                                    <select
                                        name="asuransi_id"
                                        class="w-full rounded-2xl border border-white/10 bg-white/8 px-4 py-3.5 text-sm text-white outline-none transition duration-200 focus:border-orange-300 focus:bg-white/10 focus:ring-2 focus:ring-orange-400/20"
                                    >
                                        <option value="" class="text-slate-900">Tanpa asuransi</option>
                                        @foreach ($insuranceOptions as $insurance)
                                            <option value="{{ $insurance->id }}" class="text-slate-900">{{ $insurance->nama_asuransi }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </div>

                            <button type="submit" class="btn-accent w-full justify-center text-base" data-label="Hitung Simulasi">
                                Hitung Simulasi
                            </button>
                        </form>
                    </div>

                    <div id="home-simulation-output" class="rounded-[1.8rem] border border-white/10 bg-white/6 p-5 text-sm text-white/65 backdrop-blur-xl sm:p-6">
                        Isi parameter pembiayaan untuk melihat estimasi cicilan yang lebih detail.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="faq" data-section class="bg-[#fcfaf8] py-14 sm:py-16 lg:py-24">
        <div class="shell">
            <div class="mx-auto max-w-3xl text-center" data-reveal>
                <span class="section-kicker section-kicker-accent">FAQ</span>
                <h2 class="mt-5 text-3xl font-semibold tracking-[-0.04em] text-slate-950 sm:text-4xl">
                    Pertanyaan yang paling sering diajukan calon pelanggan Credira
                </h2>
                <p class="mt-4 text-sm leading-7 text-slate-600 sm:text-base">
                    Disusun dalam format accordion agar lebih mudah dibaca, lebih ringkas, dan tetap nyaman di layar kecil.
                </p>
            </div>

            <div class="mx-auto mt-10 max-w-4xl space-y-4">
                @forelse ($faqsToShow as $faq)
                    <article
                        x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }"
                        class="overflow-hidden rounded-[1.6rem] border border-slate-200 bg-white shadow-[0_20px_60px_-48px_rgba(15,23,42,0.18)]"
                        data-reveal
                    >
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-4 px-5 py-5 text-left transition duration-200 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-200 sm:px-6"
                            @click="open = !open"
                            :aria-expanded="open.toString()"
                        >
                            <span class="text-base font-semibold leading-7 text-slate-950 sm:text-lg">{{ $faq->question }}</span>
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-slate-200 text-slate-600 transition duration-300" :class="open ? 'rotate-45 border-orange-200 bg-orange-50 text-orange-600' : ''">
                                +
                            </span>
                        </button>
                        <div
                            x-show="open"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-2"
                            class="px-5 pb-5 text-sm leading-7 text-slate-600 sm:px-6"
                        >
                            {{ $faq->answer }}
                        </div>
                    </article>
                @empty
                    <div class="rounded-[1.6rem] border border-dashed border-slate-300 bg-slate-50/80 px-6 py-12 text-center text-slate-500">
                        FAQ belum tersedia.
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection

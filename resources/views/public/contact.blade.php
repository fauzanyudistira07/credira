@extends('layouts.public', ['title' => 'Kontak Credira'])

@section('content')
    <section class="shell pt-28 pb-14 sm:pt-32 lg:pt-36 lg:pb-20">
        <div class="page-banner">
            <span class="section-kicker section-kicker-dark">Kontak</span>
            <h1>Hubungi tim Credira untuk pertanyaan produk, kerja sama, atau kebutuhan demonstrasi sistem.</h1>
            <p>Kami menyiapkan kanal komunikasi yang ringkas agar setiap pertanyaan mengenai pembiayaan, simulasi, dan alur pengajuan dapat ditangani dengan cepat.</p>
        </div>

        <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,0.72fr)_minmax(0,1.28fr)] lg:items-start">
            <div class="space-y-6" data-reveal>
                <div class="content-panel p-6 sm:p-7">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-orange-200">Informasi Kontak</p>
                    <div class="mt-5 grid gap-4 text-sm leading-7 text-slate-300">
                        <p><span class="font-semibold text-white">Alamat:</span> Jl. Pembiayaan Raya No. 12, Jakarta</p>
                        <p><span class="font-semibold text-white">Telepon:</span> +62 21 555 0199</p>
                        <p><span class="font-semibold text-white">Email:</span> hello@credira.test</p>
                    </div>
                </div>

                <div class="content-panel p-6 sm:p-7">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-orange-200">Jam Respons</p>
                    <p class="mt-4 text-sm leading-7 text-slate-300">
                        Tim Credira merespons pertanyaan umum, kebutuhan simulasi, dan permintaan kerja sama pada hari kerja agar alur komunikasi tetap cepat dan tertata.
                    </p>
                </div>
            </div>

            <div class="content-panel p-6 sm:p-8" data-reveal>
                <form method="POST" action="{{ route('contact.send') }}" class="grid gap-5">
                    @csrf
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="field-label">Nama</label>
                            <input type="text" name="name" class="field-input" value="{{ old('name') }}">
                        </div>
                        <div>
                            <label class="field-label">Email</label>
                            <input type="email" name="email" class="field-input" value="{{ old('email') }}">
                        </div>
                        <div class="md:col-span-2">
                            <label class="field-label">No. HP</label>
                            <input type="text" name="phone" class="field-input" value="{{ old('phone') }}">
                        </div>
                        <div class="md:col-span-2">
                            <label class="field-label">Subjek</label>
                            <input type="text" name="subject" class="field-input" value="{{ old('subject') }}">
                        </div>
                        <div class="md:col-span-2">
                            <label class="field-label">Pesan</label>
                            <textarea name="message" class="field-textarea">{{ old('message') }}</textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn-accent w-full justify-center sm:w-auto">Kirim Pesan</button>
                </form>
            </div>
        </div>
    </section>
@endsection

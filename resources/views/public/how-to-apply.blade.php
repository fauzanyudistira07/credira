@extends('layouts.public', ['title' => 'Cara Pengajuan'])

@section('content')
    <section class="shell pt-28 pb-14 sm:pt-32 lg:pt-36 lg:pb-20">
        <div class="page-banner">
            <span class="section-kicker section-kicker-dark">Cara Pengajuan</span>
            <h1>Alur pengajuan disusun ringkas agar mudah diikuti dari pemilihan unit hingga pengiriman motor.</h1>
            <p>Setiap tahap menampilkan kebutuhan utama calon nasabah, sehingga proses pembiayaan dapat dipahami dengan cepat dan tetap tertib.</p>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,0.78fr)_minmax(0,1.22fr)] lg:items-start">
            <div data-reveal>
                <span class="section-kicker section-kicker-dark">Ringkasan Proses</span>
                <h2 class="mt-5 text-3xl font-semibold leading-tight tracking-[-0.04em] text-white sm:text-4xl">
                    Setiap tahap dibuat singkat, jelas, dan mudah dipahami calon pelanggan.
                </h2>
                <p class="mt-4 text-sm leading-7 text-slate-300 sm:text-base">
                    Mulai dari pemilihan unit, simulasi cicilan, pengisian data, unggah dokumen, sampai pemantauan progres dilakukan dengan ritme yang konsisten.
                </p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3" data-reveal>
                @foreach ([
                    ['title' => 'Pilih motor', 'copy' => 'Lihat katalog, filter unit, dan buka detail motor.'],
                    ['title' => 'Simulasi kredit', 'copy' => 'Tentukan DP, tenor, dan opsi asuransi.'],
                    ['title' => 'Login & isi pengajuan', 'copy' => 'Lengkapi data pribadi serta pekerjaan.'],
                    ['title' => 'Upload dokumen', 'copy' => 'Unggah KTP, slip gaji, dan selfie bersama KTP.'],
                    ['title' => 'Pantau progres', 'copy' => 'Pantau status pengajuan, angsuran, pembayaran, dan pengiriman dari akun Anda.'],
                ] as $index => $step)
                    <article class="content-panel p-6">
                        <p class="text-xs uppercase tracking-[0.22em] text-orange-200">Tahap {{ $index + 1 }}</p>
                        <h2 class="mt-3 text-2xl font-semibold leading-tight text-white">{{ $step['title'] }}</h2>
                        <p class="mt-3 text-sm leading-7 text-slate-300">{{ $step['copy'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection

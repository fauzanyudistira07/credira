@extends('layouts.public', ['title' => 'Tentang Credira'])

@section('content')
    <section class="shell py-16">
        <div class="page-banner">
            <span class="section-kicker section-kicker-dark">Tentang Credira</span>
            <h1>Platform pembiayaan motor yang dirancang dengan alur digital yang jelas, cepat, dan profesional.</h1>
            <p>Credira menggabungkan katalog unit, simulasi kredit, pengajuan terstruktur, pemantauan dokumen, pembayaran, dan pengiriman dalam satu pengalaman yang konsisten.</p>
        </div>
        <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_0.9fr]">
            <div class="content-panel space-y-5">
                <span class="section-kicker section-kicker-dark">Tentang kami</span>
                <h2 class="text-3xl font-semibold text-white">Credira membantu calon nasabah mengambil keputusan pembiayaan secara lebih terarah.</h2>
                <p class="text-lg leading-8 text-slate-300">Fokus utama sistem ini adalah transparansi informasi, kerapian proses, dan kemudahan pemantauan di setiap tahap, mulai dari memilih motor hingga unit dikirimkan.</p>
            </div>
            <div class="content-panel-dark">
                <p class="text-sm uppercase tracking-[0.22em] text-white/55">Prinsip layanan</p>
                <div class="mt-5 grid gap-4 text-sm text-slate-300">
                    <p>Transparan dalam perhitungan kredit dan biaya terkait.</p>
                    <p>Konsisten dalam tampilan agar informasi mudah dipahami.</p>
                    <p>Terstruktur dalam status, riwayat, dan tindak lanjut.</p>
                    <p>Siap digunakan untuk kebutuhan presentasi maupun implementasi.</p>
                </div>
            </div>
        </div>
    </section>
@endsection

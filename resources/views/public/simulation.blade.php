@extends('layouts.public', ['title' => 'Simulasi Kredit'])

@section('content')
    <section class="shell pt-28 pb-14 sm:pt-32 lg:pt-36 lg:pb-20">
        <div class="page-banner">
            <span class="section-kicker section-kicker-dark">Simulasi Kredit</span>
            <h1>Hitung estimasi kredit secara mandiri sebelum melanjutkan ke tahap pengajuan.</h1>
            <p>Pilih unit, tentukan uang muka, tenor, dan opsi asuransi untuk mendapatkan gambaran cicilan dengan logika perhitungan yang sama seperti modul pengajuan.</p>
        </div>

        <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,0.72fr)_minmax(0,1.28fr)] lg:items-start">
            <div class="space-y-6" data-reveal>
                <div>
                    <span class="section-kicker section-kicker-dark">Perencanaan Pembiayaan</span>
                    <h2 class="mt-5 text-3xl font-semibold leading-tight tracking-[-0.04em] text-white sm:text-4xl">
                        Simulasi yang lebih jelas untuk membandingkan skema pembayaran.
                    </h2>
                    <p class="mt-4 text-sm leading-7 text-slate-300 sm:text-base">
                        Panel ini membantu Anda melihat kebutuhan dana awal, jenis cicilan, dan estimasi angsuran bulanan sebelum masuk ke proses pengajuan.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-1">
                    <div class="rounded-[1.5rem] border border-white/10 bg-white/6 p-5">
                        <p class="text-[11px] uppercase tracking-[0.24em] text-white/45">Akurasi</p>
                        <p class="mt-2 text-lg font-semibold text-white">Berbasis data unit</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/10 bg-white/6 p-5">
                        <p class="text-[11px] uppercase tracking-[0.24em] text-white/45">Input utama</p>
                        <p class="mt-2 text-lg font-semibold text-white">DP, tenor, asuransi</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/10 bg-white/6 p-5">
                        <p class="text-[11px] uppercase tracking-[0.24em] text-white/45">Output</p>
                        <p class="mt-2 text-lg font-semibold text-white">Rincian mudah discan</p>
                    </div>
                </div>
            </div>

            <div class="content-panel p-6 sm:p-8" data-reveal>
                <form class="grid gap-5" data-simulation-form data-simulation-target="#simulation-page-output">
                    @csrf
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="field-label">Motor</label>
                            <select name="motor_id" class="field-select">
                                @foreach ($motors as $motor)
                                    <option value="{{ $motor->id }}" @selected(optional($selectedMotor)->id === $motor->id)>{{ $motor->nama_motor }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Harga motor</label>
                            <input type="text" readonly class="field-input !bg-white/8" value="{{ $selectedMotor ? 'Rp '.number_format($selectedMotor->harga_jual, 0, ',', '.') : '-' }}">
                        </div>
                        <div>
                            <label class="field-label">DP</label>
                            <input type="number" name="dp" class="field-input" value="{{ $defaultDp }}">
                        </div>
                        <div>
                            <label class="field-label">Jenis cicilan</label>
                            <select name="jenis_cicilan_id" class="field-select">
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}" @selected(optional($selectedPlan)->id === $plan->id)>{{ $plan->nama_cicilan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="field-label">Asuransi</label>
                            <select name="asuransi_id" class="field-select">
                                <option value="">Tanpa asuransi</option>
                                @foreach ($insurances as $insurance)
                                    <option value="{{ $insurance->id }}" @selected(optional($selectedInsurance)->id === $insurance->id)>{{ $insurance->nama_asuransi }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-accent w-full justify-center sm:w-auto" data-label="Hitung Simulasi">Hitung Simulasi</button>
                </form>

                <div id="simulation-page-output" class="simulation-output-panel mt-6">
                    Jalankan simulasi untuk melihat rincian pokok kredit, biaya admin, dan angsuran per bulan.
                </div>
            </div>
        </div>
    </section>
@endsection

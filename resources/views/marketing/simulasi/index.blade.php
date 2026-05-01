@extends('layouts.dashboard', [
    'title' => 'Simulasi Kredit',
    'role' => 'marketing',
    'pageTitle' => 'Simulasi Kredit',
    'pageDescription' => 'Hitung estimasi cicilan marketing dengan rumus yang sama seperti modul pembuatan pengajuan.',
])

@section('content')
    <div class="marketing-page">
        <section class="marketing-hero">
            <div class="grid gap-8 xl:grid-cols-[minmax(0,1.04fr)_minmax(320px,0.96fr)] xl:items-end">
                <div>
                    <p class="marketing-hero__eyebrow">Marketing Workspace</p>
                    <h2 class="marketing-hero__title">Simulasi kredit yang siap dipakai saat follow up calon pelanggan.</h2>
                    <p class="marketing-hero__copy">Pilih unit, tentukan DP, tenor, dan opsi asuransi. Hasilnya memakai formula yang sama dengan proses pembuatan pengajuan, jadi angka yang Anda diskusikan tetap konsisten.</p>
                </div>
                <div class="marketing-soft-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/45">Output utama</p>
                    <p class="mt-2 text-lg font-semibold text-white">Pokok kredit, total bayar, biaya tambahan, dan cicilan per bulan.</p>
                    <p class="mt-2 text-sm leading-6 text-white/65">Setelah simulasi cocok, lanjutkan langsung ke pengajuan dengan motor yang sama tanpa mengulang perhitungan dari awal.</p>
                </div>
            </div>
        </section>

        @if ($motors->isEmpty() || $plans->isEmpty())
            <x-empty-state
                title="Simulasi belum bisa dijalankan"
                description="Data motor aktif atau jenis cicilan belum tersedia. Lengkapi master data terlebih dahulu agar simulasi dapat digunakan."
                action-label="Lihat Motor"
                action-href="{{ route('marketing.motors.index') }}"
            />
        @else
            <section class="marketing-simulator-grid">
                <form method="GET" class="marketing-simulator-form space-y-6">
                    <div class="dashboard-panel__head !mb-0">
                        <div>
                            <p class="dashboard-kicker">Input</p>
                            <h3 class="marketing-section-title">Atur skenario pembiayaan</h3>
                        </div>
                    </div>

                    <div class="grid gap-5">
                        <div>
                            <label class="field-label">Motor</label>
                            <select name="motor_id" class="field-select">
                                @foreach ($motors as $motor)
                                    <option value="{{ $motor->id }}" @selected($selectedMotor?->id === $motor->id)>{{ $motor->nama_motor }} - {{ $motor->merk }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="field-label">DP</label>
                                <input type="number" name="dp" class="field-input" min="0" value="{{ $dp }}">
                                <p class="field-help">Saran awal {{ $selectedMotor ? 'Rp '.number_format($defaultDp, 0, ',', '.') : '-' }} atau sekitar 20% dari harga motor.</p>
                            </div>
                            <div>
                                <label class="field-label">Tenor / jenis cicilan</label>
                                <select name="jenis_cicilan_id" class="field-select">
                                    @foreach ($plans as $plan)
                                        <option value="{{ $plan->id }}" @selected($selectedPlan?->id === $plan->id)>{{ $plan->nama_cicilan }} - {{ $plan->durasi_bulan }} bulan</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="field-label">Asuransi</label>
                            <select name="asuransi_id" class="field-select">
                                <option value="">Tanpa asuransi</option>
                                @foreach ($insurances as $insurance)
                                    <option value="{{ $insurance->id }}" @selected($selectedInsurance?->id === $insurance->id)>{{ $insurance->nama_asuransi }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @if ($simulationError)
                        <div class="marketing-simulator-alert">{{ $simulationError }}</div>
                    @endif

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button type="submit" class="btn-accent">Hitung Simulasi</button>
                        <a href="{{ route('marketing.simulasi.index', ['motor_id' => $selectedMotor?->id]) }}" class="btn-secondary">Reset</a>
                    </div>
                </form>

                <section class="marketing-simulator-result space-y-6">
                    <div class="dashboard-panel__head !mb-0">
                        <div>
                            <p class="dashboard-kicker">Output</p>
                            <h3 class="marketing-section-title">Ringkasan hasil simulasi</h3>
                        </div>
                        @if ($selectedPlan)
                            <span class="marketing-simulator-pill">{{ $selectedPlan->durasi_bulan }} bulan</span>
                        @endif
                    </div>

                    @if ($selectedMotor)
                        <article class="marketing-simulator-motor">
                            <div class="marketing-simulator-motor__media">
                                @if ($selectedMotor->primary_image_url)
                                    <img src="{{ $selectedMotor->primary_image_url }}" alt="{{ $selectedMotor->nama_motor }}" class="h-52 w-full object-contain">
                                @else
                                    <div class="flex h-48 w-full items-center justify-center rounded-[1.3rem] border border-dashed border-white/12 text-sm text-slate-300">Foto motor belum tersedia</div>
                                @endif
                            </div>
                            <div class="marketing-simulator-motor__body">
                                <div>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="marketing-simulator-pill">{{ $selectedMotor->merk }}</span>
                                        <span class="marketing-simulator-pill">{{ $selectedMotor->jenisMotor?->jenis ?? 'Motor' }}</span>
                                    </div>
                                    <h4 class="mt-4 text-2xl font-semibold tracking-[-0.03em] text-white">{{ $selectedMotor->nama_motor }}</h4>
                                    <p class="mt-2 text-sm leading-7 text-slate-300">{{ $selectedMotor->formatted_harga_jual }}</p>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="marketing-simulator-card">
                                        <p class="marketing-simulator-card__label">Cicilan per bulan</p>
                                        <p class="marketing-simulator-card__value-accent">{{ $simulation ? 'Rp '.number_format($simulation['angsuran_per_bulan'], 0, ',', '.') : '-' }}</p>
                                    </div>
                                    <div class="marketing-simulator-card">
                                        <p class="marketing-simulator-card__label">Total bayar</p>
                                        <p class="marketing-simulator-card__value-accent">{{ $simulation ? 'Rp '.number_format($simulation['total_bayar'], 0, ',', '.') : '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endif

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="marketing-simulator-card">
                            <p class="marketing-simulator-card__label">Harga motor</p>
                            <p class="marketing-simulator-card__value">{{ $simulation ? 'Rp '.number_format($simulation['harga_motor'], 0, ',', '.') : '-' }}</p>
                        </div>
                        <div class="marketing-simulator-card">
                            <p class="marketing-simulator-card__label">DP</p>
                            <p class="marketing-simulator-card__value">{{ $simulation ? 'Rp '.number_format($simulation['dp'], 0, ',', '.') : '-' }}</p>
                        </div>
                        <div class="marketing-simulator-card">
                            <p class="marketing-simulator-card__label">Pokok kredit</p>
                            <p class="marketing-simulator-card__value">{{ $simulation ? 'Rp '.number_format($simulation['pokok_kredit'], 0, ',', '.') : '-' }}</p>
                        </div>
                        <div class="marketing-simulator-card">
                            <p class="marketing-simulator-card__label">Margin kredit</p>
                            <p class="marketing-simulator-card__value">{{ $simulation ? 'Rp '.number_format($simulation['margin_amount'], 0, ',', '.') : '-' }}</p>
                        </div>
                        <div class="marketing-simulator-card">
                            <p class="marketing-simulator-card__label">Biaya admin</p>
                            <p class="marketing-simulator-card__value">{{ $simulation ? 'Rp '.number_format($simulation['biaya_admin'], 0, ',', '.') : '-' }}</p>
                        </div>
                        <div class="marketing-simulator-card">
                            <p class="marketing-simulator-card__label">Biaya asuransi</p>
                            <p class="marketing-simulator-card__value">{{ $simulation ? 'Rp '.number_format($simulation['biaya_asuransi'], 0, ',', '.') : '-' }}</p>
                        </div>
                    </div>

                    <div class="marketing-inline-note">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-orange-500">Next Step</p>
                        <div class="mt-4 space-y-3 text-sm leading-7 text-slate-600">
                            <p>Gunakan hasil ini sebagai angka diskusi awal dengan pelanggan.</p>
                            <p>Setelah skenarionya cocok, lanjutkan ke form pengajuan untuk menyimpan simulasi ke proses kredit yang sesungguhnya.</p>
                        </div>
                        <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('marketing.pengajuan.create', ['motor_id' => $selectedMotor?->id]) }}" class="btn-accent">Lanjut ke Pengajuan</a>
                            @if ($selectedMotor)
                                <a href="{{ route('marketing.motors.show', $selectedMotor) }}" class="btn-secondary">Lihat Detail Motor</a>
                            @endif
                        </div>
                    </div>
                </section>
            </section>
        @endif
    </div>
@endsection

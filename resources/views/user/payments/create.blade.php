@extends('layouts.user', [
    'title' => 'Upload Pembayaran',
    'heading' => 'Upload Bukti Pembayaran',
    'subheading' => 'Pilih tagihan aktif, transfer sesuai metode bayar, lalu unggah bukti pembayaran angsuran Anda.',
])

@section('content')
    <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
        <div class="app-panel-dark">
            <p class="app-kicker">Tagihan Aktif</p>
            <h2 class="mt-4 text-3xl font-semibold leading-tight text-white sm:text-4xl">Pilih angsuran yang ingin dibayarkan hari ini.</h2>
            <p class="mt-4 max-w-2xl text-sm leading-7 text-white/72">Halaman ini dirancang seperti layar pembayaran aplikasi kredit motor. Semua tagihan aktif ditampilkan lebih dulu, lalu user tinggal unggah bukti transfer pada form di sebelahnya.</p>

            <div class="mt-8 grid gap-4">
                @forelse ($installments as $installment)
                    <div class="app-metric-card-dark">
                        <p class="font-semibold text-white">{{ $installment->application->motor->nama_motor }}</p>
                        <p class="mt-2 text-sm text-white/68">Angsuran ke-{{ $installment->angsuran_ke }} &middot; Jatuh tempo {{ $installment->tanggal_jatuh_tempo->translatedFormat('d F Y') }}</p>
                        <p class="mt-3 text-xl font-semibold text-white">Rp {{ number_format($installment->total_tagihan, 0, ',', '.') }}</p>
                    </div>
                @empty
                    <div class="rounded-[1.7rem] border border-white/10 bg-white/6 p-5 text-sm leading-7 text-white/72">
                        Tidak ada tagihan aktif. Semua pembayaran sudah lengkap atau belum ada angsuran yang bisa dibayarkan.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="app-panel">
            <p class="app-kicker">Form Pembayaran</p>
            <h2 class="mt-3 text-2xl font-semibold text-slate-950">Upload bukti transfer untuk angsuran pilihan Anda</h2>

            @if ($installments->isNotEmpty())
                <form method="POST" action="{{ route('user.payments.store') }}" enctype="multipart/form-data" class="mt-6 grid gap-5 pb-28 lg:pb-0" data-upload-form id="payment-upload-form">
                    @csrf
                    <div class="hidden rounded-[1.4rem] border border-slate-200 bg-slate-50 p-4" data-upload-progress>
                        <div class="flex items-center justify-between gap-4">
                            <p class="text-sm font-medium text-slate-700" data-upload-text>Mengunggah 0%</p>
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Upload</p>
                        </div>
                        <div class="progress-rail mt-3">
                            <div class="progress-bar" style="width: 0%" data-upload-bar></div>
                        </div>
                    </div>

                    <div>
                        <label class="field-label">Pilih angsuran</label>
                        <select name="angsuran_id" class="field-select">
                            @foreach ($installments as $installment)
                                <option value="{{ $installment->id }}" @selected(optional($selectedInstallment)->id === $installment->id)>
                                    {{ $installment->application->motor->nama_motor }} &middot; Angsuran ke-{{ $installment->angsuran_ke }} &middot; Rp {{ number_format($installment->total_tagihan, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="field-label">Nominal bayar</label>
                            <input type="number" name="nominal_bayar" class="field-input" value="{{ old('nominal_bayar', optional($selectedInstallment)->total_tagihan) }}">
                            <x-form-error name="nominal_bayar" />
                        </div>
                        <div>
                            <label class="field-label">Tanggal bayar</label>
                            <input type="date" name="tanggal_bayar" class="field-input" value="{{ old('tanggal_bayar', now()->format('Y-m-d')) }}">
                            <x-form-error name="tanggal_bayar" />
                        </div>
                        <div>
                            <label class="field-label">Metode bayar</label>
                            <select name="id_metode_bayar" class="field-select">
                                <option value="">Pilih metode bayar</option>
                                @foreach ($paymentMethods as $method)
                                    <option value="{{ $method->id }}" @selected((string) old('id_metode_bayar') === (string) $method->id)>
                                        {{ $method->metode_pembayaran }}{{ $method->tempat_bayar ? ' - '.$method->tempat_bayar : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <x-form-error name="id_metode_bayar" />
                        </div>
                        <div>
                            <label class="field-label">Bank pengirim</label>
                            <input type="text" name="nama_bank_pengirim" class="field-input" value="{{ old('nama_bank_pengirim') }}">
                            <x-form-error name="nama_bank_pengirim" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="field-label">Nama pemilik rekening</label>
                            <input type="text" name="nama_pemilik_rekening" class="field-input" value="{{ old('nama_pemilik_rekening') }}">
                            <x-form-error name="nama_pemilik_rekening" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="field-label">Bukti transfer</label>
                            <input type="file" name="bukti_bayar" class="field-input" data-file-preview-input data-preview-target="#payment-proof-preview">
                            <x-form-error name="bukti_bayar" />
                            <div id="payment-proof-preview" class="mt-4"></div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="field-label">Catatan</label>
                            <textarea name="catatan" class="field-textarea">{{ old('catatan') }}</textarea>
                            <x-form-error name="catatan" />
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">Upload Bukti Pembayaran</button>
                </form>
            @else
                <x-empty-state
                    title="Tidak ada tagihan aktif"
                    description="Semua pembayaran sudah lengkap atau belum ada angsuran yang bisa dibayarkan sekarang."
                    action-label="Lihat Jadwal Angsuran"
                    action-href="{{ route('user.installments.index') }}"
                />
            @endif
        </div>
    </section>

    @if ($paymentMethods->isNotEmpty())
        <section class="mt-6 app-panel">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="app-kicker">Metode Pembayaran</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-950">Rekening tujuan dan kanal bayar yang tersedia</h2>
                </div>
                <a href="{{ route('user.payments.index') }}" class="btn-secondary">Riwayat Pembayaran</a>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($paymentMethods as $method)
                    <div class="app-list-card-muted">
                        <p class="font-semibold text-slate-950">{{ $method->metode_pembayaran }}</p>
                        <p class="mt-2 text-sm text-slate-600">{{ $method->tempat_bayar ?: 'Metode pembayaran aktif' }}</p>
                        @if ($method->no_rekening)
                            <p class="mt-3 text-sm font-medium text-slate-950">No. rekening: {{ $method->no_rekening }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if ($installments->isNotEmpty())
        <div class="mobile-action-bar">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Pembayaran</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">Upload bukti transfer</p>
                </div>
                <button type="submit" form="payment-upload-form" class="btn-primary">Bayar Sekarang</button>
            </div>
        </div>
    @endif
@endsection

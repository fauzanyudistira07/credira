@extends('layouts.user', [
    'title' => 'Pembayaran Midtrans',
    'heading' => 'Pembayaran Angsuran',
    'subheading' => 'Pilih tagihan aktif lalu lanjutkan pembayaran melalui Midtrans Snap.',
])

@section('content')
    <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
        <div class="app-panel-dark">
            <p class="app-kicker">Tagihan Aktif</p>
            <h2 class="mt-4 text-3xl font-semibold leading-tight text-white sm:text-4xl">Bayar angsuran secara online lewat Midtrans.</h2>
            <p class="mt-4 max-w-2xl text-sm leading-7 text-white/72">Setiap pembayaran akan diarahkan ke halaman Midtrans Snap. Status transaksi akan tersinkron otomatis saat pembayaran berhasil.</p>

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
            <p class="app-kicker">Checkout Midtrans</p>
            <h2 class="mt-3 text-2xl font-semibold text-slate-950">Pilih angsuran lalu lanjut ke halaman Midtrans</h2>

            @if ($installments->isNotEmpty())
                <form method="POST" action="{{ route('user.payments.store') }}" class="mt-6 grid gap-5 pb-28 lg:pb-0" id="payment-midtrans-form">
                    @csrf

                    <div>
                        <label class="field-label">Pilih angsuran</label>
                        <select name="angsuran_id" class="field-select">
                            @foreach ($installments as $installment)
                                <option value="{{ $installment->id }}" @selected(optional($selectedInstallment)->id === $installment->id)>
                                    {{ $installment->application->motor->nama_motor }} &middot; Angsuran ke-{{ $installment->angsuran_ke }} &middot; Rp {{ number_format($installment->total_tagihan, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                        <x-form-error name="angsuran_id" />
                    </div>

                    <div class="rounded-[1.7rem] border border-[#ece4db] bg-[#fffaf6] p-5">
                        <p class="font-semibold text-slate-950">Metode Pembayaran</p>
                        <p class="mt-3 text-sm leading-7 text-slate-600">Transaksi akan diproses melalui Midtrans Snap (VA, e-wallet, QRIS, kartu). Setelah selesai, Anda otomatis kembali ke halaman detail pembayaran.</p>
                    </div>

                    <button type="submit" class="btn-primary">Lanjut ke Midtrans</button>
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

    @if ($installments->isNotEmpty())
        <div class="mobile-action-bar">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Pembayaran</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">Checkout Midtrans</p>
                </div>
                <button type="submit" form="payment-midtrans-form" class="btn-primary">Bayar Sekarang</button>
            </div>
        </div>
    @endif
@endsection

@extends('layouts.admin', [
    'title' => 'Detail Pembayaran',
    'heading' => 'Detail Pembayaran',
    'subheading' => 'Lakukan verifikasi pembayaran angsuran dan sinkronkan status tagihan.',
])

@section('content')
    <section class="space-y-6">
        <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <section class="admin-detail-panel">
                <p class="admin-eyebrow">{{ $payment->kode_pembayaran }}</p>
                <h2 class="mt-3 text-2xl font-semibold text-white">{{ $payment->installment->application->motor->nama_motor }}</h2>
                <a href="{{ route('admin.payments.index') }}" class="admin-text-link mt-3 inline-flex">Kembali ke daftar</a>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <div class="admin-metric-card">
                        <p class="admin-metric-card__label">Nasabah</p>
                        <p class="admin-metric-card__value">{{ $payment->pelanggan->nama_lengkap }}</p>
                    </div>
                    <div class="admin-metric-card">
                        <p class="admin-metric-card__label">Status</p>
                        <div class="mt-2"><x-status-badge :status="$payment->status_verifikasi" class="!border-white/10 !bg-white/8 !text-orange-100" /></div>
                    </div>
                    <div class="admin-metric-card">
                        <p class="admin-metric-card__label">Nominal Bayar</p>
                        <p class="admin-metric-card__value">Rp {{ number_format($payment->nominal_bayar, 0, ',', '.') }}</p>
                    </div>
                    <div class="admin-metric-card">
                        <p class="admin-metric-card__label">Tanggal Bayar</p>
                        <p class="admin-metric-card__value">{{ optional($payment->tanggal_bayar)->format('d M Y') ?? '-' }}</p>
                    </div>
                </div>

                <div class="mt-6 grid gap-3 text-sm text-slate-300">
                    <p><span class="font-semibold text-white">Angsuran:</span> Ke-{{ $payment->installment->angsuran_ke }}</p>
                    <p><span class="font-semibold text-white">Metode:</span> {{ $payment->metodeBayar?->metode_pembayaran ?? $payment->metode_bayar }}</p>
                    <p><span class="font-semibold text-white">Midtrans Order ID:</span> {{ $payment->midtrans_order_id ?? '-' }}</p>
                    <p><span class="font-semibold text-white">Midtrans Transaction ID:</span> {{ $payment->midtrans_transaction_id ?? '-' }}</p>
                    <p><span class="font-semibold text-white">Bank Pengirim:</span> {{ $payment->nama_bank_pengirim ?? '-' }}</p>
                    <p><span class="font-semibold text-white">Pemilik Rekening:</span> {{ $payment->nama_pemilik_rekening ?? '-' }}</p>
                    <p><span class="font-semibold text-white">Catatan Verifikasi:</span> {{ $payment->catatan_verifikasi ?? '-' }}</p>
                </div>

                @if ($payment->proof_url)
                    <a href="{{ $payment->proof_url }}" target="_blank" rel="noopener noreferrer" class="admin-utility-button mt-6">Lihat Bukti Pembayaran</a>
                @elseif ($payment->midtrans_redirect_url)
                    <a href="{{ $payment->midtrans_redirect_url }}" target="_blank" rel="noopener noreferrer" class="admin-utility-button mt-6">Buka Halaman Midtrans</a>
                @endif
            </section>

            <section class="admin-detail-panel admin-form-shell">
                <p class="admin-eyebrow">Verifikasi Admin</p>
                <h3 class="mt-3 text-xl font-semibold text-white">Update status pembayaran</h3>

                <form method="POST" action="{{ route('admin.payments.status', $payment) }}" class="mt-5 grid gap-4">
                    @csrf
                    <div>
                        <label class="field-label">Status verifikasi</label>
                        <select name="status_verifikasi" class="field-select">
                            @foreach ($statusOptions as $status)
                                <option value="{{ $status->value }}" @selected(old('status_verifikasi', $payment->status_verifikasi) === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Catatan admin</label>
                        <textarea name="catatan_verifikasi" class="field-textarea" placeholder="Tambahkan alasan verifikasi atau penolakan">{{ old('catatan_verifikasi', $payment->catatan_verifikasi) }}</textarea>
                    </div>
                    <button type="submit" class="admin-logout-button">Simpan Verifikasi</button>
                </form>
            </section>
        </div>
    </section>
@endsection

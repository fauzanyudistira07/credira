@extends('layouts.admin', [
    'title' => 'Verifikasi Pembayaran',
    'heading' => 'Verifikasi Pembayaran',
    'subheading' => 'Tinjau transaksi angsuran, lalu validasi atau tolak pembayaran.',
])

@section('content')
    <section class="space-y-6">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="admin-summary-card">
                <p class="admin-summary-card__label">Total</p>
                <p class="admin-summary-card__value">{{ number_format($summary['total']) }}</p>
                <p class="admin-summary-card__copy">Seluruh pembayaran.</p>
            </article>
            <article class="admin-summary-card">
                <p class="admin-summary-card__label">Pending</p>
                <p class="admin-summary-card__value">{{ number_format($summary['pending']) }}</p>
                <p class="admin-summary-card__copy">Menunggu verifikasi admin.</p>
            </article>
            <article class="admin-summary-card">
                <p class="admin-summary-card__label">Valid</p>
                <p class="admin-summary-card__value">{{ number_format($summary['valid']) }}</p>
                <p class="admin-summary-card__copy">Pembayaran berhasil diverifikasi.</p>
            </article>
            <article class="admin-summary-card">
                <p class="admin-summary-card__label">Ditolak</p>
                <p class="admin-summary-card__value">{{ number_format($summary['rejected']) }}</p>
                <p class="admin-summary-card__copy">Perlu pembayaran ulang.</p>
            </article>
        </div>

        <div class="admin-filter-panel admin-form-shell">
            <form method="GET" class="grid gap-4 md:grid-cols-[1fr_260px_auto]">
                <div>
                    <label class="field-label">Cari pembayaran</label>
                    <input
                        type="text"
                        name="q"
                        class="field-input"
                        value="{{ $keyword }}"
                        placeholder="Kode pembayaran, nama nasabah, atau motor"
                    >
                </div>
                <div>
                    <label class="field-label">Status verifikasi</label>
                    <select name="status" class="field-select">
                        <option value="">Semua status</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected($currentStatus === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="admin-logout-button w-full md:w-auto">Filter</button>
                    <a href="{{ route('admin.payments.index') }}" class="admin-utility-button w-full md:w-auto">Reset</a>
                </div>
            </form>
        </div>

        <div class="admin-stream-panel">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="admin-eyebrow">Daftar Pembayaran</p>
                    <h2 class="mt-2 text-2xl font-semibold text-white">Transaksi angsuran</h2>
                </div>
                <p class="admin-muted">Total {{ number_format($payments->total()) }} data</p>
            </div>

            <div class="mt-6 grid gap-4">
                @forelse ($payments as $payment)
                    <article class="admin-activity-card">
                        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">{{ $payment->kode_pembayaran }}</p>
                                <h3 class="mt-2 text-lg font-semibold text-white">{{ $payment->pelanggan->nama_lengkap }}</h3>
                                <p class="mt-2 text-sm text-slate-300">
                                    {{ $payment->installment->application->motor->nama_motor }} &middot; Angsuran ke-{{ $payment->installment->angsuran_ke }}
                                </p>
                                <p class="mt-1 text-sm text-slate-400">{{ optional($payment->tanggal_bayar)->format('d M Y') ?? '-' }} &middot; Rp {{ number_format($payment->nominal_bayar, 0, ',', '.') }}</p>
                            </div>
                            <div class="flex flex-col items-start gap-3 xl:items-end">
                                <x-status-badge :status="$payment->status_verifikasi" class="!border-white/10 !bg-white/8 !text-orange-100" />
                                <a href="{{ route('admin.payments.show', $payment) }}" class="admin-utility-button !min-h-0 !px-4 !py-2.5">Detail</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <x-empty-state
                        title="Belum ada pembayaran"
                        description="Belum ada transaksi yang cocok dengan filter saat ini."
                    />
                @endforelse
            </div>

            @if ($payments->hasPages())
                <div class="mt-6">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection

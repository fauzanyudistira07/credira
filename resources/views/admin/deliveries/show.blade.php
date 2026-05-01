@extends('layouts.admin', [
    'title' => 'Detail Pengiriman',
    'heading' => 'Detail Pengiriman',
    'subheading' => 'Perbarui status pengiriman unit dan data kurir.',
])

@section('content')
    <section class="space-y-6">
        <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <section class="admin-detail-panel">
                <p class="admin-eyebrow">{{ $delivery->invoice }}</p>
                <h2 class="mt-3 text-2xl font-semibold text-white">{{ $delivery->application->motor->nama_motor }}</h2>
                <p class="mt-2 text-sm text-slate-300">{{ $delivery->application->kode_pengajuan }}, {{ $delivery->application->pelanggan->nama_lengkap }}</p>
                <a href="{{ route('admin.deliveries.index') }}" class="admin-text-link mt-3 inline-flex">Kembali ke daftar</a>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <div class="admin-metric-card">
                        <p class="admin-metric-card__label">Status</p>
                        <div class="mt-2"><x-status-badge :status="$delivery->status_kirim" class="!border-white/10 !bg-white/8 !text-orange-100" /></div>
                    </div>
                    <div class="admin-metric-card">
                        <p class="admin-metric-card__label">Kurir</p>
                        <p class="admin-metric-card__value">{{ $delivery->nama_kurir ?? '-' }}</p>
                    </div>
                    <div class="admin-metric-card">
                        <p class="admin-metric-card__label">Tanggal Kirim</p>
                        <p class="admin-metric-card__value">{{ optional($delivery->tgl_kirim)->format('d M Y') ?? '-' }}</p>
                    </div>
                    <div class="admin-metric-card">
                        <p class="admin-metric-card__label">Tanggal Tiba</p>
                        <p class="admin-metric-card__value">{{ optional($delivery->tgl_tiba)->format('d M Y') ?? '-' }}</p>
                    </div>
                </div>

                <div class="mt-6 grid gap-3 text-sm text-slate-300">
                    <p><span class="font-semibold text-white">Telpon Kurir:</span> {{ $delivery->telpon_kurir ?? '-' }}</p>
                    <p><span class="font-semibold text-white">Penerima:</span> {{ $delivery->nama_penerima ?? '-' }}</p>
                    <p><span class="font-semibold text-white">Alamat Tujuan:</span> {{ $delivery->alamat_tujuan }}</p>
                    <p><span class="font-semibold text-white">Keterangan:</span> {{ $delivery->keterangan ?? '-' }}</p>
                </div>
            </section>

            <section class="admin-detail-panel admin-form-shell">
                <p class="admin-eyebrow">Update Pengiriman</p>
                <h3 class="mt-3 text-xl font-semibold text-white">Tindakan admin</h3>

                <form method="POST" action="{{ route('admin.deliveries.update', $delivery) }}" class="mt-5 grid gap-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="field-label">Status kirim</label>
                        <select name="status_kirim" class="field-select">
                            @foreach ($statusOptions as $status)
                                <option value="{{ $status->value }}" @selected(old('status_kirim', $delivery->status_kirim) === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="field-label">Tanggal kirim</label>
                            <input type="date" name="tgl_kirim" class="field-input" value="{{ old('tgl_kirim', optional($delivery->tgl_kirim)->format('Y-m-d')) }}">
                        </div>
                        <div>
                            <label class="field-label">Tanggal tiba</label>
                            <input type="date" name="tgl_tiba" class="field-input" value="{{ old('tgl_tiba', optional($delivery->tgl_tiba)->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div>
                        <label class="field-label">Nama kurir</label>
                        <input type="text" name="nama_kurir" class="field-input" value="{{ old('nama_kurir', $delivery->nama_kurir) }}">
                    </div>
                    <div>
                        <label class="field-label">Telpon kurir</label>
                        <input type="text" name="telpon_kurir" class="field-input" value="{{ old('telpon_kurir', $delivery->telpon_kurir) }}">
                    </div>
                    <div>
                        <label class="field-label">Nama penerima</label>
                        <input type="text" name="nama_penerima" class="field-input" value="{{ old('nama_penerima', $delivery->nama_penerima) }}">
                    </div>
                    <div>
                        <label class="field-label">Keterangan</label>
                        <textarea name="keterangan" class="field-textarea">{{ old('keterangan', $delivery->keterangan) }}</textarea>
                    </div>
                    <button type="submit" class="admin-logout-button">Simpan Pengiriman</button>
                </form>
            </section>
        </div>
    </section>
@endsection

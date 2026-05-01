@extends('layouts.dashboard', [
    'title' => 'Review '.$pengajuan->kode_pengajuan,
    'role' => 'marketing',
    'pageTitle' => 'Approval Pengajuan',
    'pageDescription' => 'Marketing dapat melakukan review, approve, atau reject pengajuan miliknya langsung dari halaman ini.',
])

@section('content')
    <div class="marketing-page">
        <section class="grid gap-6 xl:grid-cols-[0.92fr_1.08fr]">
            <div class="marketing-surface">
                <p class="dashboard-kicker">Approval Context</p>
                <h2 class="mt-3 text-2xl font-semibold text-slate-950">{{ $pengajuan->kode_pengajuan }}</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    <x-status-badge :status="$pengajuan->status_pengajuan" />
                    <span class="rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700">{{ $pengajuan->pelanggan?->display_name ?? '-' }}</span>
                    <span class="rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700">{{ $pengajuan->motor?->nama_motor ?? '-' }}</span>
                </div>

                <div class="mt-6 grid gap-3">
                    @forelse ($pengajuan->documents as $document)
                        <a href="{{ $document->file_url }}" target="_blank" rel="noopener noreferrer" class="dashboard-list-row">
                            <div>
                                <p class="font-semibold text-slate-950">{{ str($document->jenis_dokumen)->replace('_', ' ')->title() }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $document->nama_file }}</p>
                            </div>
                            <x-status-badge :status="$document->status_verifikasi" />
                        </a>
                    @empty
                        <p class="text-sm leading-7 text-slate-600">Belum ada dokumen yang terlampir pada pengajuan ini.</p>
                    @endforelse
                </div>
            </div>

            <form method="POST" action="{{ route('marketing.pengajuan.update-status', $pengajuan) }}" class="marketing-surface space-y-6">
                @csrf
                @method('PUT')
                <div>
                    <p class="dashboard-kicker">Status Action</p>
                    <h3 class="mt-3 text-2xl font-semibold text-slate-950">Marketing approval</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600">Perubahan status di sini akan memperbarui timeline pengajuan, mencatat log, dan memberi notifikasi ke pelanggan.</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($reviewOptions as $value => $label)
                        <label class="rounded-[1.4rem] border border-slate-200 bg-slate-50 p-4 transition has-[:checked]:border-slate-900 has-[:checked]:bg-white">
                            <input type="radio" name="status" value="{{ $value }}" class="sr-only" @checked(old('status', 'review') === $value)>
                            <span class="block font-semibold text-slate-950">{{ $label }}</span>
                            <span class="mt-2 block text-sm text-slate-600">
                                {{ match ($value) {
                                    'pending' => 'Kembalikan ke antrean pending.',
                                    'review' => 'Lanjutkan tahap review dokumen.',
                                    'approved' => 'Setujui pengajuan pelanggan.',
                                    'rejected' => 'Tolak pengajuan dengan alasan jelas.',
                                } }}
                            </span>
                        </label>
                    @endforeach
                </div>
                <x-form-error name="status" />

                <div>
                    <label class="field-label" for="catatan">Catatan approval</label>
                    <textarea id="catatan" name="catatan" class="field-textarea" placeholder="Tambahkan alasan approval, pending, atau penolakan">{{ old('catatan', $pengajuan->catatan_status) }}</textarea>
                    <x-form-error name="catatan" />
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <button type="submit" class="btn-accent flex-1">Simpan Status</button>
                    <a href="{{ route('marketing.pengajuan.show', $pengajuan) }}" class="btn-secondary flex-1">Kembali ke Detail</a>
                </div>
            </form>
        </section>
    </div>
@endsection

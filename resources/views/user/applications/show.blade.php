@extends('layouts.user', [
    'title' => 'Detail Pengajuan',
    'heading' => 'Detail Pengajuan Kredit',
    'subheading' => 'Lihat progress pengajuan, rincian pembiayaan, dokumen, hingga pengiriman unit motor.',
])

@php
    $personal = $application->snapshot_data['personal'] ?? [];
    $timelineSteps = [
        ['key' => 'draft', 'title' => 'Draft dibuat', 'description' => 'Data dasar pembiayaan sudah tersimpan di sistem.'],
        ['key' => 'documents', 'title' => 'Dokumen lengkap', 'description' => 'Dokumen pendukung nasabah sudah diunggah.'],
        ['key' => 'verifikasi_dokumen', 'title' => 'Verifikasi dokumen', 'description' => 'Tim Credira sedang mengecek keabsahan berkas.'],
        ['key' => 'diproses', 'title' => 'Analisis kredit', 'description' => 'Pengajuan dinilai berdasarkan data finansial dan profil nasabah.'],
        ['key' => 'disetujui', 'title' => 'Approval pembiayaan', 'description' => 'Pengajuan memperoleh keputusan pembiayaan.'],
        ['key' => 'kontrak_aktif', 'title' => 'Kontrak aktif', 'description' => 'Jadwal angsuran aktif dan unit siap diproses ke tahap pengiriman.'],
    ];
    $statusOrder = ['draft', 'menunggu_konfirmasi', 'verifikasi_dokumen', 'survey', 'diproses', 'disetujui', 'kontrak_aktif', 'selesai'];
    $currentIndex = array_search($application->status_pengajuan, $statusOrder, true);
    $currentIndex = $currentIndex === false ? 0 : $currentIndex;
    $paidInstallments = $application->installments->where('status_pembayaran', 'sudah_bayar')->count();
@endphp

@section('content')
    <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <div class="app-panel-dark">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="app-kicker">Pengajuan Kredit</p>
                    <p class="mt-3 text-xs uppercase tracking-[0.28em] text-white/50">{{ $application->kode_pengajuan }}</p>
                    <h2 class="mt-3 text-3xl font-semibold leading-tight text-white">{{ $application->motor->nama_motor }}</h2>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-white/70">{{ $application->jenisCicilan->nama_cicilan }} dengan DP Rp {{ number_format($application->dp, 0, ',', '.') }}. Halaman ini menjadi pusat detail pengajuan sampai kontrak, pembayaran, dan pengiriman.</p>
                </div>
                <x-status-badge :status="$application->status_pengajuan" />
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Harga cash</p>
                    <p class="mt-3 text-xl font-semibold text-white">Rp {{ number_format($application->harga_cash, 0, ',', '.') }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Cicilan/bulan</p>
                    <p class="mt-3 text-xl font-semibold text-white">Rp {{ number_format($application->cicilan_perbulan, 0, ',', '.') }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Angsuran lunas</p>
                    <p class="mt-3 text-3xl font-semibold text-white">{{ $paidInstallments }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Dokumen</p>
                    <p class="mt-3 text-3xl font-semibold text-white">{{ $application->documents->count() }}</p>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                @if ($canEdit)
                    <a href="{{ route('user.applications.edit', $application) }}" class="btn-outline-light">Edit Data</a>
                    <a href="{{ route('user.applications.documents', $application) }}" class="btn-outline-light">Kelola Dokumen</a>
                @endif
                @if ($application->status_pengajuan === 'draft')
                    <form method="POST" action="{{ route('user.applications.submit', $application) }}">
                        @csrf
                        <button type="submit" class="btn-accent">Submit Final</button>
                    </form>
                @endif
                @if ($application->installments->isNotEmpty())
                    <a href="{{ route('user.installments.index') }}" class="btn-outline-light">Buka Angsuran</a>
                @endif
                @if (in_array($application->status_pengajuan, ['draft', 'menunggu_konfirmasi', 'verifikasi_dokumen'], true))
                    <x-modal title="Batalkan pengajuan" description="Pengajuan akan dihentikan dan tidak dilanjutkan ke proses approval.">
                        <x-slot:trigger>
                            <button type="button" class="btn-outline-light">Batalkan</button>
                        </x-slot:trigger>

                        <form method="POST" action="{{ route('user.applications.cancel', $application) }}" class="flex items-center justify-end gap-3">
                            @csrf
                            <button type="submit" class="btn-primary">Ya, batalkan</button>
                        </form>
                    </x-modal>
                @endif
            </div>
        </div>

        <div class="app-panel">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="app-kicker">Timeline Progress</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-950">Status pengajuan dari awal sampai kontrak aktif</h2>
                </div>
                <p class="text-sm text-slate-500">{{ $application->updated_at->translatedFormat('d M Y H:i') }}</p>
            </div>

            <div class="relative mt-8 grid gap-6">
                <div class="timeline-line"></div>
                @foreach ($timelineSteps as $index => $item)
                    @php
                        $isDocuments = $item['key'] === 'documents';
                        $documentsComplete = $application->documents->isNotEmpty();
                        $stepState = 'pending';

                        if ($isDocuments && $documentsComplete) {
                            $stepState = 'complete';
                        } elseif (! $isDocuments && array_search($item['key'], $statusOrder, true) !== false) {
                            $stepIndex = array_search($item['key'], $statusOrder, true);
                            if ($stepIndex < $currentIndex) {
                                $stepState = 'complete';
                            } elseif ($stepIndex === $currentIndex) {
                                $stepState = 'current';
                            }
                        } elseif ($item['key'] === 'draft') {
                            $stepState = 'complete';
                        }
                    @endphp

                    <div class="relative flex gap-4">
                        <div class="timeline-point {{ $stepState === 'complete' ? 'timeline-point-complete' : ($stepState === 'current' ? 'timeline-point-current' : 'timeline-point-pending') }}">
                            {{ $stepState === 'complete' ? 'OK' : ($stepState === 'current' ? 'ON' : $index + 1) }}
                        </div>
                        <div class="min-w-0 flex-1 rounded-[1.4rem] border {{ $stepState === 'current' ? 'border-orange-200 bg-orange-50/70' : 'border-slate-200 bg-slate-50' }} p-4">
                            <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                                <p class="font-semibold text-slate-900">{{ $item['title'] }}</p>
                                @if ($stepState === 'current')
                                    <x-status-badge :status="$application->status_pengajuan" />
                                @endif
                            </div>
                            <p class="mt-2 text-sm leading-7 text-slate-600">{{ $item['description'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-3">
        <div class="app-panel">
            <p class="app-kicker">Identitas Nasabah</p>
            <div class="mt-5 grid gap-3 text-sm leading-7 text-slate-600">
                <p><span class="font-semibold text-slate-950">Nama:</span> {{ $personal['nama_lengkap'] ?? '-' }}</p>
                <p><span class="font-semibold text-slate-950">No. KTP:</span> {{ $personal['no_ktp'] ?? '-' }}</p>
                <p><span class="font-semibold text-slate-950">Email:</span> {{ $personal['email'] ?? '-' }}</p>
                <p><span class="font-semibold text-slate-950">No. HP:</span> {{ $personal['nomor_hp'] ?? '-' }}</p>
                <p><span class="font-semibold text-slate-950">Alamat:</span> {{ $personal['alamat_lengkap'] ?? '-' }}</p>
            </div>
        </div>

        <div class="app-panel">
            <p class="app-kicker">Data Finansial</p>
            <div class="mt-5 grid gap-3 text-sm leading-7 text-slate-600">
                <p><span class="font-semibold text-slate-950">Pekerjaan:</span> {{ $application->financialDetail?->pekerjaan ?? '-' }}</p>
                <p><span class="font-semibold text-slate-950">Perusahaan:</span> {{ $application->financialDetail?->nama_perusahaan ?? '-' }}</p>
                <p><span class="font-semibold text-slate-950">Penghasilan:</span> Rp {{ number_format($application->financialDetail?->penghasilan_bulanan ?? 0, 0, ',', '.') }}</p>
                <p><span class="font-semibold text-slate-950">Pengeluaran:</span> Rp {{ number_format($application->financialDetail?->pengeluaran_bulanan ?? 0, 0, ',', '.') }}</p>
                <p><span class="font-semibold text-slate-950">Kontak darurat:</span> {{ $application->financialDetail?->kontak_darurat_nama ?? '-' }}</p>
            </div>
        </div>

        <div class="app-panel">
            <p class="app-kicker">Rincian Kontrak</p>
            <div class="mt-5 grid gap-4">
                <div class="app-metric-card">
                    <p class="text-sm text-slate-500">Biaya admin</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950">Rp {{ number_format($application->biaya_admin, 0, ',', '.') }}</p>
                </div>
                <div class="app-metric-card">
                    <p class="text-sm text-slate-500">Total bayar</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950">Rp {{ number_format($application->total_bayar, 0, ',', '.') }}</p>
                </div>
                <div class="app-metric-card">
                    <p class="text-sm text-slate-500">Angsuran terbentuk</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950">{{ $application->installments->count() }} jadwal</p>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <div class="app-panel">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="app-kicker">Dokumen Pengajuan</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-950">Berkas nasabah yang tersimpan</h2>
                </div>
                <a href="{{ route('user.applications.documents', $application) }}" class="btn-secondary">Kelola Dokumen</a>
            </div>

            <div class="mt-6 grid gap-3">
                @forelse ($application->documents as $document)
                    <a href="{{ $document->file_url }}" target="_blank" rel="noopener noreferrer" class="app-list-card-muted">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-semibold text-slate-950">{{ str($document->jenis_dokumen)->replace('_', ' ')->title() }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $document->nama_file }}</p>
                            </div>
                            <x-status-badge :status="$document->status_verifikasi" />
                        </div>
                    </a>
                @empty
                    <x-empty-state
                        title="Belum ada dokumen"
                        description="Lengkapi dokumen pendukung agar proses verifikasi bisa dilanjutkan oleh tim Credira."
                        action-label="Upload Dokumen"
                        action-href="{{ route('user.applications.documents', $application) }}"
                    />
                @endforelse
            </div>
        </div>

        <div class="app-panel">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="app-kicker">Riwayat Aktivitas</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-950">Catatan perubahan status pengajuan</h2>
                </div>
                <span class="rounded-full bg-[#fff3e9] px-3 py-1 text-xs font-semibold text-[#ff6425]">{{ $application->logs->count() }} log</span>
            </div>

            <div class="mt-6 grid gap-4">
                @forelse ($application->logs->take(6) as $log)
                    <div class="app-list-card-muted">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <p class="font-semibold text-slate-950">
                                {{ str($log->status_lama ?: 'draft')->replace('_', ' ')->title() }}
                                -
                                {{ str($log->status_baru)->replace('_', ' ')->title() }}
                            </p>
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ $log->created_at->translatedFormat('d M Y H:i') }}</p>
                        </div>
                        <p class="mt-2 text-sm leading-7 text-slate-600">{{ $log->catatan ?? 'Perubahan status pengajuan terekam di sistem.' }}</p>
                        <p class="mt-3 text-xs font-medium text-slate-500">Oleh: {{ $log->changedBy?->name ?? 'Sistem' }}</p>
                    </div>
                @empty
                    <p class="text-sm leading-7 text-slate-600">Belum ada aktivitas lanjutan yang tercatat selain pembuatan pengajuan.</p>
                @endforelse
            </div>
        </div>
    </section>

    @if ($application->delivery)
        <section class="mt-6 app-panel">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="app-kicker">Pengiriman Unit</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-950">Status pengiriman motor untuk kontrak ini</h2>
                </div>
                <x-status-badge :status="$application->delivery->status_kirim" />
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="app-metric-card">
                    <p class="text-sm text-slate-500">Invoice</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $application->delivery->invoice }}</p>
                </div>
                <div class="app-metric-card">
                    <p class="text-sm text-slate-500">Kurir</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $application->delivery->nama_kurir ?: '-' }}</p>
                </div>
                <div class="app-metric-card">
                    <p class="text-sm text-slate-500">Telepon</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $application->delivery->telpon_kurir ?: '-' }}</p>
                </div>
                <div class="app-metric-card">
                    <p class="text-sm text-slate-500">Estimasi tiba</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ optional($application->delivery->tgl_tiba)->translatedFormat('d F Y') ?? '-' }}</p>
                </div>
            </div>

            @if ($application->delivery->address)
                <div class="mt-6 rounded-[1.7rem] border border-[#ece4db] bg-[#fffaf6] p-5">
                    <p class="font-semibold text-slate-950">Alamat pengiriman</p>
                    <p class="mt-3 text-sm leading-7 text-slate-600">{{ $application->delivery->address->alamat_lengkap }}, {{ $application->delivery->address->kota }}, {{ $application->delivery->address->provinsi }} {{ $application->delivery->address->kode_pos }}</p>
                </div>
            @endif
        </section>
    @endif

    <div class="mobile-action-bar">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Status pengajuan</p>
                <div class="mt-2"><x-status-badge :status="$application->status_pengajuan" /></div>
            </div>
            <div class="flex gap-2">
                @if ($canEdit)
                    <a href="{{ route('user.applications.edit', $application) }}" class="btn-secondary">Edit</a>
                @endif
                @if ($application->installments->isNotEmpty())
                    <a href="{{ route('user.installments.index') }}" class="btn-primary">Angsuran</a>
                @endif
            </div>
        </div>
    </div>
@endsection

@extends('layouts.user', [
    'title' => 'Dokumen Pengajuan',
    'heading' => 'Dokumen Pengajuan Kredit',
    'subheading' => 'Unggah dan cek status verifikasi seluruh berkas pendukung untuk pengajuan motor Anda.',
])

@php
    $requiredDocuments = [
        'foto_ktp' => 'Foto KTP',
        'slip_gaji' => 'Slip gaji / bukti penghasilan',
        'foto_selfie_ktp' => 'Selfie dengan KTP',
        'kk' => 'Kartu keluarga',
        'npwp' => 'NPWP',
        'bukti_domisili' => 'Bukti domisili',
    ];
@endphp

@section('content')
    <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <div class="app-panel-dark">
            <p class="app-kicker">Berkas Nasabah</p>
            <p class="mt-3 text-xs uppercase tracking-[0.28em] text-white/50">{{ $application->kode_pengajuan }}</p>
            <h2 class="mt-3 text-3xl font-semibold text-white">{{ $application->motor->nama_motor }}</h2>
            <p class="mt-4 max-w-2xl text-sm leading-7 text-white/72">Lengkapi seluruh dokumen agar tim Credira dapat memverifikasi pengajuan dengan cepat. Setelah lengkap, Anda bisa melanjutkan submit final langsung dari halaman ini.</p>

            <div class="mt-8 grid gap-3">
                @foreach ($requiredDocuments as $key => $label)
                    <div class="app-metric-card-dark flex items-center justify-between gap-3">
                        <span class="text-sm text-white">{{ $label }}</span>
                        <span class="rounded-full border border-white/12 px-3 py-1 text-xs font-semibold text-white/74">
                            {{ $application->documents->firstWhere('jenis_dokumen', $key) ? 'Sudah ada' : 'Belum ada' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="app-panel">
            <p class="app-kicker">Upload Dokumen</p>
            <h2 class="mt-3 text-2xl font-semibold text-slate-950">Tambah atau perbarui dokumen pengajuan</h2>

            <form method="POST" action="{{ route('user.applications.documents.store', $application) }}" enctype="multipart/form-data" class="mt-6 grid gap-5 pb-28 lg:pb-0" data-upload-form id="application-documents-form">
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

                @foreach ($requiredDocuments as $key => $label)
                    <div class="rounded-[1.5rem] border border-[#ece4db] bg-[#fffaf6] p-5">
                        <label class="field-label">{{ $label }}</label>
                        <input type="file" name="documents[{{ $key }}]" class="field-input">
                    </div>
                @endforeach

                <button type="submit" class="btn-primary">Upload Dokumen</button>
            </form>

            @if ($application->status_pengajuan === 'draft')
                <form method="POST" action="{{ route('user.applications.submit', $application) }}" class="mt-4">
                    @csrf
                    <button type="submit" class="btn-secondary">Submit Pengajuan Final</button>
                </form>
            @endif
        </div>
    </section>

    <section class="mt-6 app-panel">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="app-kicker">Dokumen Tersimpan</p>
                <h2 class="mt-3 text-2xl font-semibold text-slate-950">Status verifikasi berkas yang sudah masuk</h2>
            </div>
            <a href="{{ route('user.applications.show', $application) }}" class="btn-secondary">Kembali ke Detail</a>
        </div>

        <div class="mt-6 grid gap-4">
            @forelse ($application->documents as $document)
                <a href="{{ $document->file_url }}" target="_blank" rel="noopener noreferrer" class="app-list-card">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="font-semibold text-slate-950">{{ str($document->jenis_dokumen)->replace('_', ' ')->title() }}</p>
                            <p class="mt-2 text-sm text-slate-500">{{ $document->nama_file }}</p>
                        </div>
                        <x-status-badge :status="$document->status_verifikasi" />
                    </div>
                </a>
            @empty
                <x-empty-state
                    title="Belum ada dokumen"
                    description="Upload berkas pertama Anda agar pengajuan dapat diproses."
                    action-label="Upload Sekarang"
                    action-href="{{ route('user.applications.documents', $application) }}"
                />
            @endforelse
        </div>
    </section>

    <div class="mobile-action-bar">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Dokumen</p>
                <p class="mt-1 text-sm font-semibold text-slate-900">Lengkapi berkas pengajuan</p>
            </div>
            <button type="submit" form="application-documents-form" class="btn-primary">Upload</button>
        </div>
    </div>
@endsection

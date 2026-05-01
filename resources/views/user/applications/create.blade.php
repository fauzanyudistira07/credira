@extends('layouts.user', [
    'title' => $application ? 'Edit Pengajuan' : 'Ajukan Kredit Motor',
    'heading' => $application ? 'Edit Pengajuan Kredit' : 'Ajukan Kredit Motor',
    'subheading' => 'Lengkapi pembiayaan, identitas, dan dokumen dengan alur yang lebih rapi, jelas, dan nyaman dipakai di mobile.',
])

@php
    $snapshotPersonal = $application->snapshot_data['personal'] ?? [];
    $financial = $application?->financialDetail;
    $addressMap = $addresses->mapWithKeys(fn ($address) => [$address->id => [
        'alamat_lengkap' => $address->alamat_lengkap,
        'kota' => $address->kota,
        'provinsi' => $address->provinsi,
        'kode_pos' => $address->kode_pos,
    ]])->all();

    $errorStep = 1;
    if ($errors->hasAny(['nama_lengkap', 'no_ktp', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'status_pernikahan', 'nomor_hp', 'email', 'alamat_pengiriman_id', 'alamat_lengkap', 'kota', 'provinsi', 'kode_pos'])) {
        $errorStep = 2;
    }
    if ($errors->hasAny(['pekerjaan', 'nama_perusahaan', 'alamat_kantor', 'lama_bekerja', 'penghasilan_bulanan', 'pengeluaran_bulanan', 'status_rumah', 'kontak_darurat_nama', 'kontak_darurat_nohp', 'kontak_darurat_hubungan'])) {
        $errorStep = 3;
    }
    if ($errors->hasAny(['documents.foto_ktp', 'documents.slip_gaji', 'documents.foto_selfie_ktp', 'documents.kk', 'documents.npwp', 'documents.bukti_domisili'])) {
        $errorStep = 4;
    }

    $steps = [
        1 => ['label' => 'Motor & Paket', 'caption' => 'Unit, tenor, DP, dan proteksi'],
        2 => ['label' => 'Data Pribadi', 'caption' => 'Identitas, kontak, dan alamat'],
        3 => ['label' => 'Pekerjaan', 'caption' => 'Finansial dan kontak darurat'],
        4 => ['label' => 'Dokumen', 'caption' => 'Upload berkas verifikasi'],
        5 => ['label' => 'Konfirmasi', 'caption' => 'Review akhir sebelum submit'],
    ];

    $documentFields = [
        'foto_ktp' => ['label' => 'Foto KTP', 'required' => true, 'hint' => 'Pastikan foto terang, teks terbaca, dan seluruh sisi kartu terlihat.'],
        'slip_gaji' => ['label' => 'Slip gaji / bukti penghasilan', 'required' => true, 'hint' => 'Bisa berupa slip gaji, rekening koran, atau dokumen pendapatan lain.'],
        'foto_selfie_ktp' => ['label' => 'Selfie dengan KTP', 'required' => true, 'hint' => 'Gunakan pencahayaan cukup agar wajah dan KTP sama-sama jelas.'],
        'kk' => ['label' => 'Kartu keluarga', 'required' => false, 'hint' => 'Membantu mempercepat verifikasi data keluarga bila dibutuhkan.'],
        'npwp' => ['label' => 'NPWP', 'required' => false, 'hint' => 'Opsional, tetapi bisa ditambahkan untuk melengkapi dokumen pendukung.'],
        'bukti_domisili' => ['label' => 'Bukti domisili', 'required' => false, 'hint' => 'Contoh: tagihan listrik, internet, atau surat domisili.'],
    ];

    $genderOptions = ['Laki-laki', 'Perempuan'];
    $maritalOptions = ['Belum Menikah', 'Menikah', 'Cerai'];
    $employmentDurationOptions = ['< 1 tahun', '1 - 2 tahun', '3 - 5 tahun', '> 5 tahun'];
    $housingOptions = ['Milik sendiri', 'Rumah keluarga', 'Kontrak', 'Kost', 'Rumah dinas'];

    $formId = $application ? 'application-edit-form' : 'application-create-form';
    $selectedMotorId = (string) old('motor_id', $application->motor_id ?? request('motor_id') ?? $motors->first()?->id);
    $selectedPlanId = (string) old('jenis_cicilan_id', $application->jenis_cicilan_id ?? $plans->first()?->id);
    $selectedInsuranceId = (string) old('asuransi_id', $application->asuransi_id ?? '');
    $currentGender = old('jenis_kelamin', $snapshotPersonal['jenis_kelamin'] ?? $pelanggan->jenis_kelamin);
    $currentMaritalStatus = old('status_pernikahan', $snapshotPersonal['status_pernikahan'] ?? $pelanggan->status_pernikahan);
    $currentEmploymentDuration = old('lama_bekerja', $financial->lama_bekerja ?? '');
    $currentHousingStatus = old('status_rumah', $financial->status_rumah ?? '');
    $existingDocuments = $application?->documents?->keyBy('jenis_dokumen') ?? collect();
@endphp

@section('content')
    <section
        class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]"
        x-data="applicationWizard({
            initialStep: {{ $errorStep }},
            storageKey: '{{ $application ? 'credira-application-'.$application->id : 'credira-application-draft' }}',
            applicationId: {{ $application?->id ?? 'null' }},
            hasOldInput: {{ $errors->any() ? 'true' : 'false' }},
            addresses: @js($addressMap),
        })"
    >
        <div class="space-y-6">
            <div class="wizard-hero">
                <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
                    <div class="max-w-3xl">
                        <p class="app-kicker">Form Pengajuan</p>
                        <h2 class="mt-3 text-3xl font-semibold leading-tight text-slate-950">
                            {{ $application ? 'Perbarui detail pengajuan dengan tampilan yang lebih rapi dan mudah ditinjau.' : 'Ajukan kredit motor dengan flow yang lebih modern, singkat, dan nyaman digunakan.' }}
                        </h2>
                        <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-600">
                            Setiap tahap dirancang supaya Anda bisa fokus pada satu jenis data dalam satu waktu. Draft tersimpan otomatis, dokumen bisa dicek ulang, dan submit final dilakukan setelah semua data siap.
                        </p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 xl:max-w-sm xl:grid-cols-1">
                        <div class="rounded-[1.5rem] border border-[#ece4db] bg-white/88 px-4 py-4">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-400">Draft Otomatis</p>
                            <p class="mt-2 text-sm font-semibold text-slate-950" x-text="lastSavedAt ? 'Tersimpan ' + lastSavedAt : 'Siap digunakan'"></p>
                            <p class="mt-1 text-sm leading-6 text-slate-500">Perubahan akan disimpan otomatis selama Anda masih mengisi formulir.</p>
                        </div>
                        <div class="rounded-[1.5rem] border border-[#ece4db] bg-[#fff7f2] px-4 py-4">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-orange-600">Proses Cepat</p>
                            <p class="mt-2 text-sm font-semibold text-slate-950">5 tahap terstruktur</p>
                            <p class="mt-1 text-sm leading-6 text-slate-500">Mulai dari pilih motor, isi data, unggah dokumen, lalu review sebelum submit.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 progress-rail">
                    <div class="progress-bar" :style="{ width: progress() }"></div>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                    @foreach ($steps as $index => $item)
                        <div
                            class="wizard-step-card"
                            :class="{
                                'wizard-step-card-active': isCurrent({{ $index }}),
                                'wizard-step-card-complete': isComplete({{ $index }})
                            }"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.26em]" :class="isCurrent({{ $index }}) ? 'text-white/64' : 'text-slate-400'">
                                        Step {{ $index }}
                                    </p>
                                    <p class="mt-2 text-sm font-semibold">{{ $item['label'] }}</p>
                                    <p class="mt-1 text-xs leading-5" :class="isCurrent({{ $index }}) ? 'text-white/64' : 'text-slate-500'">
                                        {{ $item['caption'] }}
                                    </p>
                                </div>
                                <div
                                    class="step-indicator !h-9 !w-9 !rounded-[0.9rem]"
                                    :class="{
                                        'step-indicator-complete': isComplete({{ $index }}),
                                        'step-indicator-current': isCurrent({{ $index }})
                                    }"
                                >
                                    {{ $index }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <form
                method="POST"
                enctype="multipart/form-data"
                action="{{ $application ? route('user.applications.update', $application) : route('user.applications.store') }}"
                class="grid gap-6 pb-28 lg:pb-0"
                x-ref="form"
                id="{{ $formId }}"
                data-upload-form
            >
                @csrf
                @if ($application)
                    @method('PUT')
                @endif

                <div class="hidden rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4" data-upload-progress>
                    <div class="flex items-center justify-between gap-4">
                        <p class="text-sm font-medium text-slate-700" data-upload-text>Mengunggah 0%</p>
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Upload</p>
                    </div>
                    <div class="progress-rail mt-3">
                        <div class="progress-bar" style="width: 0%" data-upload-bar></div>
                    </div>
                </div>

                <div x-show="step === 1" x-transition class="wizard-stage space-y-6" x-ref="step1">
                    <div class="wizard-stage__header">
                        <div>
                            <span class="wizard-stage__eyebrow">Step 1 • Motor & Paket</span>
                            <h3 class="wizard-stage__title">Tentukan unit motor dan skema pembiayaan yang paling sesuai.</h3>
                            <p class="wizard-stage__copy">
                                Pilih motor, tenor cicilan, besar DP, dan proteksi tambahan. Semua pilihan ini menjadi dasar simulasi dan analisis pengajuan.
                            </p>
                        </div>
                        <div class="wizard-stage__aside">
                            <p class="font-semibold text-slate-900">Tip cepat</p>
                            <p class="mt-1">DP yang lebih besar biasanya membantu membuat angsuran bulanan lebih ringan.</p>
                        </div>
                    </div>

                    <div class="wizard-section-card">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h4 class="wizard-section-heading">Pilih motor impian Anda</h4>
                                <p class="wizard-section-copy">Semua motor aktif ditampilkan sebagai pilihan visual agar lebih cepat dibanding dropdown biasa.</p>
                            </div>
                            <a href="{{ route('motors.index') }}" class="btn-ghost sm:self-start">Lihat katalog</a>
                        </div>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            @foreach ($motors as $motor)
                                <label class="cursor-pointer">
                                    <input
                                        type="radio"
                                        name="motor_id"
                                        value="{{ $motor->id }}"
                                        class="wizard-choice-input"
                                        data-step-required
                                        required
                                        @checked($selectedMotorId === (string) $motor->id)
                                    >
                                    <span class="wizard-choice-card">
                                        <span class="wizard-choice-card__eyebrow">{{ strtoupper($motor->merk) }}</span>
                                        <span class="wizard-choice-card__title">{{ $motor->nama_motor }}</span>
                                        <span class="wizard-choice-card__copy">
                                            {{ \Illuminate\Support\Str::limit($motor->deskripsi_motor ?? $motor->deskripsi, 104) }}
                                        </span>

                                        <span class="mt-4 flex flex-wrap gap-2">
                                            @if ($motor->kapasitas_mesin)
                                                <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">{{ $motor->kapasitas_mesin }}</span>
                                            @endif
                                            @if ($motor->transmisi)
                                                <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">{{ $motor->transmisi }}</span>
                                            @endif
                                            @if ($motor->warna)
                                                <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">{{ $motor->warna }}</span>
                                            @endif
                                        </span>

                                        <span class="wizard-choice-card__footer">
                                            <span>
                                                <span class="block text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">Harga OTR</span>
                                                <span class="mt-1 block text-lg font-semibold text-slate-950">Rp {{ number_format($motor->harga_jual, 0, ',', '.') }}</span>
                                            </span>
                                            <span class="rounded-full border border-orange-200 bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-600">Pilih</span>
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <x-form-error name="motor_id" />
                    </div>

                    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(0,0.9fr)]">
                        <div class="wizard-section-card">
                            <div>
                                <h4 class="wizard-section-heading">Pilih tenor cicilan</h4>
                                <p class="wizard-section-copy">Tiap paket menampilkan durasi, margin kredit, dan biaya administrasi.</p>
                            </div>

                            <div class="mt-5 grid gap-4 md:grid-cols-3">
                                @foreach ($plans as $plan)
                                    <label class="cursor-pointer">
                                        <input
                                            type="radio"
                                            name="jenis_cicilan_id"
                                            value="{{ $plan->id }}"
                                            class="wizard-choice-input"
                                            data-step-required
                                            required
                                            @checked($selectedPlanId === (string) $plan->id)
                                        >
                                        <span class="wizard-choice-card">
                                            <span class="wizard-choice-card__eyebrow">{{ $plan->durasi_bulan }} bulan</span>
                                            <span class="wizard-choice-card__title">{{ $plan->nama_cicilan }}</span>
                                            <span class="wizard-choice-card__copy">Biaya admin Rp {{ number_format($plan->biaya_admin, 0, ',', '.') }} dengan margin {{ number_format($plan->margin_kredit, 2, ',', '.') }}%.</span>
                                            <span class="wizard-choice-card__footer">
                                                <span>
                                                    <span class="block text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">Durasi</span>
                                                    <span class="mt-1 block text-base font-semibold text-slate-950">{{ $plan->durasi_bulan }} bulan</span>
                                                </span>
                                                <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">Tenor</span>
                                            </span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            <x-form-error name="jenis_cicilan_id" />
                        </div>

                        <div class="space-y-5">
                            <div class="wizard-section-card wizard-section-card--plain">
                                <p class="wizard-section-heading">Atur uang muka / DP</p>
                                <label class="field-label mt-4">Nominal DP</label>
                                <input
                                    type="number"
                                    name="dp"
                                    class="field-input"
                                    value="{{ old('dp', $application->dp ?? 6000000) }}"
                                    inputmode="numeric"
                                    placeholder="Contoh: 6000000"
                                    data-step-required
                                    required
                                >
                                <p class="field-help">Masukkan nominal tanpa titik atau koma agar perhitungan lebih stabil.</p>
                                <x-form-error name="dp" />
                            </div>

                            <div class="wizard-section-card">
                                <div>
                                    <h4 class="wizard-section-heading">Proteksi tambahan</h4>
                                    <p class="wizard-section-copy">Asuransi opsional untuk menambah rasa aman selama masa pembiayaan.</p>
                                </div>

                                <div class="mt-5 grid gap-4">
                                    <label class="cursor-pointer">
                                        <input
                                            type="radio"
                                            name="asuransi_id"
                                            value=""
                                            class="wizard-choice-input"
                                            @checked($selectedInsuranceId === '')
                                        >
                                        <span class="wizard-choice-card">
                                            <span class="wizard-choice-card__eyebrow">Opsional</span>
                                            <span class="wizard-choice-card__title">Tanpa asuransi tambahan</span>
                                            <span class="wizard-choice-card__copy">Cocok bila Anda ingin menjaga cicilan tetap sesederhana mungkin.</span>
                                        </span>
                                    </label>

                                    @foreach ($insurances as $insurance)
                                        <label class="cursor-pointer">
                                            <input
                                                type="radio"
                                                name="asuransi_id"
                                                value="{{ $insurance->id }}"
                                                class="wizard-choice-input"
                                                @checked($selectedInsuranceId === (string) $insurance->id)
                                            >
                                            <span class="wizard-choice-card">
                                                <span class="wizard-choice-card__eyebrow">{{ $insurance->nama_perusahaan_asuransi }}</span>
                                                <span class="wizard-choice-card__title">{{ $insurance->nama_asuransi }}</span>
                                                <span class="wizard-choice-card__copy">Margin asuransi {{ number_format($insurance->margin_asuransi, 2, ',', '.') }}% untuk perlindungan unit pembiayaan.</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                <x-form-error name="asuransi_id" />
                            </div>
                        </div>
                    </div>
                </div>

                <div x-show="step === 2" x-transition class="wizard-stage space-y-6" x-ref="step2">
                    <div class="wizard-stage__header">
                        <div>
                            <span class="wizard-stage__eyebrow">Step 2 • Data Pribadi</span>
                            <h3 class="wizard-stage__title">Lengkapi identitas nasabah dan alamat pengiriman.</h3>
                            <p class="wizard-stage__copy">
                                Data dasar dari profil aktif sudah dipakai sebagai titik awal. Anda tinggal cek, rapikan, lalu lanjut ke tahap finansial.
                            </p>
                        </div>
                        <div class="wizard-stage__aside">
                            <p class="font-semibold text-slate-900">Prefill aktif</p>
                            <p class="mt-1">Data akun Anda otomatis dimasukkan untuk mempercepat pengisian form.</p>
                        </div>
                    </div>

                    <div class="grid gap-5 xl:grid-cols-2">
                        <div class="wizard-section-card space-y-5">
                            <div>
                                <h4 class="wizard-section-heading">Identitas utama</h4>
                                <p class="wizard-section-copy">Pastikan data sesuai identitas resmi yang akan dipakai untuk verifikasi.</p>
                            </div>

                            <div class="grid gap-5 md:grid-cols-2">
                                <div class="md:col-span-2">
                                    <label class="field-label">Nama lengkap</label>
                                    <input type="text" name="nama_lengkap" class="field-input" value="{{ old('nama_lengkap', $snapshotPersonal['nama_lengkap'] ?? $pelanggan->nama_lengkap) }}" autocomplete="name" data-step-required required>
                                    <x-form-error name="nama_lengkap" />
                                </div>

                                <div>
                                    <label class="field-label">No. KTP</label>
                                    <input type="text" name="no_ktp" class="field-input" value="{{ old('no_ktp', $snapshotPersonal['no_ktp'] ?? $pelanggan->no_ktp) }}" inputmode="numeric" placeholder="16 digit nomor KTP" data-step-required required>
                                    <x-form-error name="no_ktp" />
                                </div>

                                <div>
                                    <label class="field-label">Tempat lahir</label>
                                    <input type="text" name="tempat_lahir" class="field-input" value="{{ old('tempat_lahir', $snapshotPersonal['tempat_lahir'] ?? $pelanggan->tempat_lahir) }}" placeholder="Contoh: Bandung">
                                    <x-form-error name="tempat_lahir" />
                                </div>

                                <div>
                                    <label class="field-label">Tanggal lahir</label>
                                    <input type="date" name="tanggal_lahir" class="field-input" value="{{ old('tanggal_lahir', $snapshotPersonal['tanggal_lahir'] ?? optional($pelanggan->tanggal_lahir)->format('Y-m-d')) }}" autocomplete="bday">
                                    <x-form-error name="tanggal_lahir" />
                                </div>

                                <div>
                                    <label class="field-label">Jenis kelamin</label>
                                    <select name="jenis_kelamin" class="field-select">
                                        <option value="">Pilih jenis kelamin</option>
                                        @if ($currentGender && ! in_array($currentGender, $genderOptions, true))
                                            <option value="{{ $currentGender }}" selected>{{ $currentGender }}</option>
                                        @endif
                                        @foreach ($genderOptions as $option)
                                            <option value="{{ $option }}" @selected($currentGender === $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                    <x-form-error name="jenis_kelamin" />
                                </div>

                                <div class="md:col-span-2">
                                    <label class="field-label">Status pernikahan</label>
                                    <select name="status_pernikahan" class="field-select">
                                        <option value="">Pilih status</option>
                                        @if ($currentMaritalStatus && ! in_array($currentMaritalStatus, $maritalOptions, true))
                                            <option value="{{ $currentMaritalStatus }}" selected>{{ $currentMaritalStatus }}</option>
                                        @endif
                                        @foreach ($maritalOptions as $option)
                                            <option value="{{ $option }}" @selected($currentMaritalStatus === $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                    <x-form-error name="status_pernikahan" />
                                </div>
                            </div>
                        </div>

                        <div class="wizard-section-card space-y-5">
                            <div>
                                <h4 class="wizard-section-heading">Kontak & alamat pengiriman</h4>
                                <p class="wizard-section-copy">Alamat ini akan dipakai sebagai referensi utama untuk pengiriman unit motor.</p>
                            </div>

                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label class="field-label">Nomor HP</label>
                                    <input type="text" name="nomor_hp" class="field-input" value="{{ old('nomor_hp', $snapshotPersonal['nomor_hp'] ?? $pelanggan->no_telp) }}" autocomplete="tel" inputmode="tel" data-step-required required>
                                    <x-form-error name="nomor_hp" />
                                </div>

                                <div>
                                    <label class="field-label">Email</label>
                                    <input type="email" name="email" class="field-input" value="{{ old('email', $snapshotPersonal['email'] ?? $pelanggan->email) }}" autocomplete="email" data-step-required required>
                                    <x-form-error name="email" />
                                </div>

                                <div class="md:col-span-2">
                                    <label class="field-label">Gunakan alamat tersimpan</label>
                                    <select name="alamat_pengiriman_id" class="field-select">
                                        <option value="">Pilih alamat atau isi manual di bawah</option>
                                        @foreach ($addresses as $address)
                                            <option value="{{ $address->id }}" @selected(old('alamat_pengiriman_id') == $address->id)>{{ $address->label_alamat }} - {{ $address->kota }}</option>
                                        @endforeach
                                    </select>
                                    <p class="field-help">Saat Anda memilih alamat tersimpan, kota, provinsi, dan kode pos akan terisi otomatis.</p>
                                    <x-form-error name="alamat_pengiriman_id" />
                                </div>

                                <div class="md:col-span-2">
                                    <label class="field-label">Alamat lengkap</label>
                                    <textarea name="alamat_lengkap" class="field-textarea" autocomplete="street-address" placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan">{{ old('alamat_lengkap', $snapshotPersonal['alamat_lengkap'] ?? '') }}</textarea>
                                    <x-form-error name="alamat_lengkap" />
                                </div>

                                <div>
                                    <label class="field-label">Kota</label>
                                    <input type="text" name="kota" class="field-input" value="{{ old('kota', $snapshotPersonal['kota'] ?? '') }}" autocomplete="address-level2" placeholder="Contoh: Surabaya">
                                    <x-form-error name="kota" />
                                </div>

                                <div>
                                    <label class="field-label">Provinsi</label>
                                    <input type="text" name="provinsi" class="field-input" value="{{ old('provinsi', $snapshotPersonal['provinsi'] ?? '') }}" autocomplete="address-level1" placeholder="Contoh: Jawa Timur">
                                    <x-form-error name="provinsi" />
                                </div>

                                <div class="md:col-span-2">
                                    <label class="field-label">Kode pos</label>
                                    <input type="text" name="kode_pos" class="field-input" value="{{ old('kode_pos', $snapshotPersonal['kode_pos'] ?? '') }}" autocomplete="postal-code" inputmode="numeric" placeholder="Contoh: 60231">
                                    <x-form-error name="kode_pos" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div x-show="step === 3" x-transition class="wizard-stage space-y-6" x-ref="step3">
                    <div class="wizard-stage__header">
                        <div>
                            <span class="wizard-stage__eyebrow">Step 3 • Pekerjaan & Finansial</span>
                            <h3 class="wizard-stage__title">Isi profil pekerjaan, penghasilan, dan kontak darurat.</h3>
                            <p class="wizard-stage__copy">
                                Informasi finansial digunakan untuk membantu proses penilaian kelayakan pembiayaan, sementara kontak darurat dipakai bila tim memerlukan verifikasi tambahan.
                            </p>
                        </div>
                        <div class="wizard-stage__aside">
                            <p class="font-semibold text-slate-900">Disarankan</p>
                            <p class="mt-1">Gunakan nominal bulanan terbaru agar hasil analisis pengajuan lebih akurat.</p>
                        </div>
                    </div>

                    <datalist id="job-options">
                        <option value="Karyawan Swasta"></option>
                        <option value="Pegawai Negeri"></option>
                        <option value="Wiraswasta"></option>
                        <option value="Freelancer"></option>
                        <option value="Pedagang"></option>
                    </datalist>

                    <div class="grid gap-5 xl:grid-cols-2">
                        <div class="wizard-section-card space-y-5">
                            <div>
                                <h4 class="wizard-section-heading">Profil pekerjaan</h4>
                                <p class="wizard-section-copy">Jelaskan pekerjaan utama dan tempat Anda bekerja saat ini.</p>
                            </div>

                            <div class="grid gap-5">
                                <div>
                                    <label class="field-label">Pekerjaan</label>
                                    <input type="text" name="pekerjaan" class="field-input" value="{{ old('pekerjaan', $financial->pekerjaan ?? $pelanggan->pekerjaan_default) }}" list="job-options" placeholder="Contoh: Karyawan Swasta" data-step-required required>
                                    <x-form-error name="pekerjaan" />
                                </div>

                                <div>
                                    <label class="field-label">Nama perusahaan / usaha</label>
                                    <input type="text" name="nama_perusahaan" class="field-input" value="{{ old('nama_perusahaan', $financial->nama_perusahaan ?? '') }}" autocomplete="organization" placeholder="Contoh: PT Nusantara Motor">
                                    <x-form-error name="nama_perusahaan" />
                                </div>

                                <div>
                                    <label class="field-label">Alamat kantor / usaha</label>
                                    <textarea name="alamat_kantor" class="field-textarea" placeholder="Alamat kantor atau lokasi usaha saat ini">{{ old('alamat_kantor', $financial->alamat_kantor ?? '') }}</textarea>
                                    <x-form-error name="alamat_kantor" />
                                </div>

                                <div class="grid gap-5 md:grid-cols-2">
                                    <div>
                                        <label class="field-label">Lama bekerja</label>
                                        <select name="lama_bekerja" class="field-select">
                                            <option value="">Pilih durasi</option>
                                            @if ($currentEmploymentDuration && ! in_array($currentEmploymentDuration, $employmentDurationOptions, true))
                                                <option value="{{ $currentEmploymentDuration }}" selected>{{ $currentEmploymentDuration }}</option>
                                            @endif
                                            @foreach ($employmentDurationOptions as $option)
                                                <option value="{{ $option }}" @selected($currentEmploymentDuration === $option)>{{ $option }}</option>
                                            @endforeach
                                        </select>
                                        <x-form-error name="lama_bekerja" />
                                    </div>

                                    <div>
                                        <label class="field-label">Status rumah</label>
                                        <select name="status_rumah" class="field-select">
                                            <option value="">Pilih status hunian</option>
                                            @if ($currentHousingStatus && ! in_array($currentHousingStatus, $housingOptions, true))
                                                <option value="{{ $currentHousingStatus }}" selected>{{ $currentHousingStatus }}</option>
                                            @endif
                                            @foreach ($housingOptions as $option)
                                                <option value="{{ $option }}" @selected($currentHousingStatus === $option)>{{ $option }}</option>
                                            @endforeach
                                        </select>
                                        <x-form-error name="status_rumah" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="wizard-section-card space-y-5">
                            <div>
                                <h4 class="wizard-section-heading">Kemampuan finansial & kontak darurat</h4>
                                <p class="wizard-section-copy">Masukkan penghasilan, pengeluaran, dan seseorang yang bisa dihubungi bila diperlukan.</p>
                            </div>

                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label class="field-label">Penghasilan per bulan</label>
                                    <input type="number" name="penghasilan_bulanan" class="field-input" value="{{ old('penghasilan_bulanan', $financial->penghasilan_bulanan ?? $pelanggan->penghasilan_default) }}" inputmode="numeric" placeholder="Contoh: 7000000" data-step-required required>
                                    <x-form-error name="penghasilan_bulanan" />
                                </div>

                                <div>
                                    <label class="field-label">Pengeluaran per bulan</label>
                                    <input type="number" name="pengeluaran_bulanan" class="field-input" value="{{ old('pengeluaran_bulanan', $financial->pengeluaran_bulanan ?? '') }}" inputmode="numeric" placeholder="Contoh: 3500000" data-step-required required>
                                    <x-form-error name="pengeluaran_bulanan" />
                                </div>

                                <div>
                                    <label class="field-label">Nama kontak darurat</label>
                                    <input type="text" name="kontak_darurat_nama" class="field-input" value="{{ old('kontak_darurat_nama', $financial->kontak_darurat_nama ?? '') }}" placeholder="Contoh: Budi Santoso" data-step-required required>
                                    <x-form-error name="kontak_darurat_nama" />
                                </div>

                                <div>
                                    <label class="field-label">No. kontak darurat</label>
                                    <input type="text" name="kontak_darurat_nohp" class="field-input" value="{{ old('kontak_darurat_nohp', $financial->kontak_darurat_nohp ?? '') }}" inputmode="tel" placeholder="Contoh: 081234567890" data-step-required required>
                                    <x-form-error name="kontak_darurat_nohp" />
                                </div>

                                <div class="md:col-span-2">
                                    <label class="field-label">Hubungan dengan kontak darurat</label>
                                    <input type="text" name="kontak_darurat_hubungan" class="field-input" value="{{ old('kontak_darurat_hubungan', $financial->kontak_darurat_hubungan ?? '') }}" placeholder="Contoh: Istri / Kakak / Orang tua" data-step-required required>
                                    <x-form-error name="kontak_darurat_hubungan" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div x-show="step === 4" x-transition class="wizard-stage space-y-6" x-ref="step4">
                    <div class="wizard-stage__header">
                        <div>
                            <span class="wizard-stage__eyebrow">Step 4 • Dokumen</span>
                            <h3 class="wizard-stage__title">Unggah dokumen pendukung dengan format yang jelas dan mudah diverifikasi.</h3>
                            <p class="wizard-stage__copy">
                                File dapat berupa JPG, PNG, atau PDF dengan ukuran maksimal 5 MB. Untuk pengajuan baru, minimal unggah KTP, bukti penghasilan, dan selfie dengan KTP.
                            </p>
                        </div>
                        <div class="wizard-stage__aside">
                            <p class="font-semibold text-slate-900">Format diterima</p>
                            <p class="mt-1">JPG, PNG, PDF. Usahakan hasil scan atau foto tajam dan tidak blur.</p>
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        @foreach ($documentFields as $key => $document)
                            @php
                                $existingDocument = $existingDocuments->get($key);
                                $requiresUpload = $document['required'] && ! $existingDocument;
                            @endphp

                            <div class="wizard-upload-card">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h4 class="wizard-section-heading !text-lg">{{ $document['label'] }}</h4>
                                        <p class="wizard-section-copy">{{ $document['hint'] }}</p>
                                    </div>
                                    <span class="rounded-full {{ $document['required'] ? 'border border-orange-200 bg-orange-50 text-orange-600' : 'border border-slate-200 bg-slate-50 text-slate-500' }} px-3 py-1 text-xs font-semibold">
                                        {{ $document['required'] ? 'Wajib' : 'Opsional' }}
                                    </span>
                                </div>

                                <div class="mt-4 rounded-[1.4rem] border border-[#ece4db] bg-[#fffaf6] p-4">
                                    <input
                                        type="file"
                                        name="documents[{{ $key }}]"
                                        class="block w-full text-sm text-slate-500 file:mr-4 file:rounded-full file:border-0 file:bg-slate-950 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-800"
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        data-file-preview-input
                                        data-preview-key="{{ $key }}"
                                        @if ($requiresUpload)
                                            data-step-required
                                            required
                                        @endif
                                    >
                                    <p class="mt-3 text-xs text-slate-500">Unggah file dengan ukuran maksimal 5 MB.</p>
                                </div>
                                <x-form-error name="documents.{{ $key }}" />

                                @if ($existingDocument)
                                    <div class="mt-4 rounded-[1.3rem] border border-emerald-200 bg-emerald-50/70 px-4 py-3 text-sm text-emerald-800">
                                        <p class="font-semibold">Dokumen saat ini: {{ $existingDocument->nama_file }}</p>
                                        <p class="mt-1 text-emerald-700">Status verifikasi: {{ str($existingDocument->status_verifikasi)->replace('_', ' ')->title() }}</p>
                                    </div>
                                @endif

                                <div class="mt-4" x-show="previews.{{ $key }}">
                                    <div class="rounded-[1.3rem] border border-slate-200 bg-white p-4">
                                        <p class="text-sm font-semibold text-slate-900" x-text="previews.{{ $key }}?.name"></p>
                                        <template x-if="previews.{{ $key }}?.previewUrl">
                                            <img :src="previews.{{ $key }}.previewUrl" alt="{{ $document['label'] }}" class="mt-3 h-36 w-full rounded-[1.15rem] object-cover">
                                        </template>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div x-show="step === 5" x-transition class="wizard-stage space-y-6" x-ref="step5">
                    <div class="wizard-stage__header">
                        <div>
                            <span class="wizard-stage__eyebrow">Step 5 • Konfirmasi</span>
                            <h3 class="wizard-stage__title">Periksa kembali seluruh data sebelum pengajuan dikirim final.</h3>
                            <p class="wizard-stage__copy">
                                Setelah submit final, tim Credira akan meninjau data, memverifikasi dokumen, lalu menghubungi Anda untuk proses lanjutan sampai pengiriman unit.
                            </p>
                        </div>
                        <div class="wizard-stage__aside">
                            <p class="font-semibold text-slate-900">Setelah submit</p>
                            <p class="mt-1">Status pengajuan akan berubah dari draft menjadi menunggu review.</p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="wizard-section-card">
                            <p class="text-sm font-semibold text-slate-950">1. Review data</p>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Periksa ulang motor, paket cicilan, dan identitas nasabah yang sudah diisi.</p>
                        </div>
                        <div class="wizard-section-card">
                            <p class="text-sm font-semibold text-slate-950">2. Verifikasi dokumen</p>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Pastikan dokumen wajib sudah diunggah dengan kualitas yang mudah dibaca.</p>
                        </div>
                        <div class="wizard-section-card">
                            <p class="text-sm font-semibold text-slate-950">3. Tunggu konfirmasi</p>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Tim Credira akan memberikan update melalui notifikasi akun setelah pengecekan selesai.</p>
                        </div>
                    </div>

                    <div class="grid gap-5 lg:grid-cols-[1.15fr_0.85fr]">
                        <div class="wizard-section-card">
                            <h4 class="wizard-section-heading">Checklist sebelum submit final</h4>
                            <div class="mt-4 grid gap-3 text-sm leading-6 text-slate-600">
                                <p>Motor, tenor, dan DP sudah dipilih sesuai kebutuhan.</p>
                                <p>Nomor HP, email, dan alamat pengiriman sudah bisa dihubungi / digunakan.</p>
                                <p>Penghasilan, pengeluaran, dan kontak darurat terisi dengan data terbaru.</p>
                                <p>Dokumen wajib minimal sudah disiapkan: KTP, bukti penghasilan, dan selfie dengan KTP.</p>
                            </div>
                        </div>

                        <div class="wizard-section-card wizard-section-card--plain">
                            <h4 class="wizard-section-heading">Privasi & akurasi data</h4>
                            <p class="mt-3 text-sm leading-7 text-slate-600">
                                Data digunakan hanya untuk proses analisis pembiayaan, verifikasi, serta komunikasi terkait pengajuan kredit motor Anda.
                            </p>
                        </div>
                    </div>

                    <label class="flex items-start gap-3 rounded-[1.6rem] border border-[#ece4db] bg-[#fffaf6] p-5 text-sm leading-6 text-slate-600">
                        <input type="checkbox" class="mt-1 rounded border-slate-300" data-step-required required>
                        <span>Saya menyatakan seluruh data yang saya isi adalah benar, dapat dipertanggungjawabkan, dan siap diproses lebih lanjut oleh tim Credira.</span>
                    </label>
                </div>

                <div class="hidden items-center justify-between gap-4 rounded-[1.75rem] border border-white/70 bg-white/92 p-4 shadow-[0_22px_64px_-44px_rgba(15,23,42,0.22)] lg:flex">
                    <div>
                        <p class="text-sm font-semibold text-slate-950">Progress pengajuan</p>
                        <p class="mt-1 text-sm text-slate-500" x-text="lastSavedAt ? 'Draft terakhir tersimpan ' + lastSavedAt : 'Perubahan akan tersimpan otomatis saat Anda mengisi form.'"></p>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="button" class="btn-secondary" @click="goPrev()" :disabled="step === 1" :class="step === 1 ? 'pointer-events-none opacity-50' : ''">Sebelumnya</button>
                        <button type="button" class="btn-secondary" @click="goNext()" x-show="step < totalSteps">Berikutnya</button>
                    </div>

                    <div class="flex items-center gap-3">
                        @if (! $application)
                            <button type="submit" name="action" value="draft" class="btn-secondary">Simpan Draft</button>
                            <button type="submit" name="action" value="submit" class="btn-accent">Submit Final</button>
                        @else
                            <button type="submit" class="btn-primary">Simpan Perubahan</button>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <aside class="space-y-4 xl:sticky xl:top-28 xl:self-start">
            <div class="wizard-sidebar-card">
                <p class="app-kicker">Ringkasan</p>
                <h3 class="mt-2 text-2xl font-semibold text-slate-950">Step <span x-text="step"></span> dari <span x-text="totalSteps"></span></h3>
                <p class="mt-3 text-sm leading-7 text-slate-600">Form ini dibuat bertahap agar pengisian terasa ringan di layar kecil sekalipun.</p>

                <div class="mt-5 rounded-[1.5rem] border border-[#ece4db] bg-[#fff7f2] p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-orange-600">Fokus saat ini</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950" x-text="{1: 'Motor & paket', 2: 'Data pribadi', 3: 'Pekerjaan', 4: 'Dokumen', 5: 'Konfirmasi'}[step]"></p>
                    <p class="mt-2 text-sm leading-6 text-slate-600" x-text="{1: 'Pilih unit, tenor, dan DP.', 2: 'Cek identitas dan alamat pengiriman.', 3: 'Lengkapi data finansial dengan akurat.', 4: 'Unggah dokumen penting dengan jelas.', 5: 'Review akhir sebelum submit final.'}[step]"></p>
                </div>
            </div>

            <div class="wizard-sidebar-card">
                <p class="app-kicker">Persiapan</p>
                <div class="mt-4 grid gap-3 text-sm leading-6 text-slate-600">
                    <p>Siapkan KTP, bukti penghasilan, dan selfie dengan KTP sebelum masuk tahap dokumen.</p>
                    <p>Gunakan alamat yang benar karena akan menjadi acuan pengiriman unit motor setelah kontrak aktif.</p>
                    <p>Jika Anda sudah pernah menyimpan alamat di profil, pilih dari dropdown agar pengisian lebih cepat.</p>
                </div>
            </div>

            <div class="wizard-sidebar-card">
                <p class="app-kicker">Aksi</p>
                <div class="mt-4 grid gap-3">
                    <a href="{{ route('user.applications.index') }}" class="btn-secondary w-full">Lihat Semua Pengajuan</a>
                    <a href="{{ route('user.profile.index') }}" class="btn-secondary w-full">Perbarui Profil</a>
                </div>
            </div>
        </aside>

        <div class="mobile-action-bar">
            <div class="grid gap-3">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Step <span x-text="step"></span> / <span x-text="totalSteps"></span></p>
                        <p class="mt-1 text-sm font-semibold text-slate-900" x-text="{1: 'Motor & paket', 2: 'Data pribadi', 3: 'Pekerjaan', 4: 'Dokumen', 5: 'Konfirmasi'}[step]"></p>
                    </div>
                    <p class="text-xs text-slate-500" x-text="lastSavedAt ? 'tersimpan ' + lastSavedAt : 'draft aktif'"></p>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <button type="button" class="btn-secondary" @click="goPrev()" :disabled="step === 1" :class="step === 1 ? 'pointer-events-none opacity-50' : ''">Sebelumnya</button>
                    <button type="button" class="btn-secondary" @click="goNext()" x-show="step < totalSteps">Berikutnya</button>
                    <div x-show="step === totalSteps" class="rounded-full border border-[#ece4db] bg-[#fffaf6] px-4 py-3 text-center text-sm font-semibold text-slate-600">Siap submit</div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    @if (! $application)
                        <button type="submit" form="{{ $formId }}" name="action" value="draft" class="btn-secondary">Draft</button>
                        <button type="submit" form="{{ $formId }}" name="action" value="submit" class="btn-primary">Submit</button>
                    @else
                        <button type="submit" form="{{ $formId }}" class="btn-primary col-span-2">Simpan Perubahan</button>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

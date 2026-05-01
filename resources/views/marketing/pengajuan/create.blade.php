@extends('layouts.dashboard', [
    'title' => 'Buat Pengajuan Kredit',
    'role' => 'marketing',
    'pageTitle' => 'Buat Pengajuan Kredit',
    'pageDescription' => 'Pilih pelanggan, tentukan motor dan tenor, lalu simpan pengajuan dengan detail finansial dan dokumen yang relevan.',
])

@section('content')
    <div class="marketing-page">
        <section class="marketing-hero">
            <p class="marketing-hero__eyebrow">Buat Pengajuan</p>
            <h2 class="marketing-hero__title">Susun aplikasi kredit yang siap direview dalam alur yang lebih jelas.</h2>
            <p class="marketing-hero__copy">Mulai dari pelanggan dan unit, lanjutkan ke detail finansial, lalu unggah dokumen inti dalam panel yang tetap nyaman dipakai di mobile.</p>
        </section>

        <form method="POST" action="{{ route('marketing.pengajuan.store') }}" enctype="multipart/form-data" class="space-y-6" data-upload-form>
            @csrf

            <section class="marketing-surface">
                <div class="dashboard-panel__head">
                    <div>
                        <p class="dashboard-kicker">Step 1</p>
                        <h3 class="marketing-section-title">Pelanggan & motor</h3>
                    </div>
                </div>

                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                    <div class="xl:col-span-2">
                        <label class="field-label">Pilih pelanggan milik Anda</label>
                        <select name="pelanggan_id" class="field-select" required>
                            <option value="">Pilih pelanggan</option>
                            @foreach ($pelanggan as $item)
                                <option value="{{ $item->id }}" @selected((string) old('pelanggan_id', $selectedPelangganId) === (string) $item->id)>{{ $item->display_name }} - {{ $item->no_telp }}</option>
                            @endforeach
                        </select>
                        <x-form-error name="pelanggan_id" />
                    </div>
                    <div class="xl:col-span-2">
                        <label class="field-label">Pilih motor</label>
                        <select name="motor_id" class="field-select" required>
                            <option value="">Pilih motor</option>
                            @foreach ($motors as $motor)
                                <option value="{{ $motor->id }}" @selected((string) old('motor_id', $selectedMotorId) === (string) $motor->id)>{{ $motor->nama_motor }} - {{ $motor->merk }} - Rp {{ number_format($motor->harga_jual, 0, ',', '.') }}</option>
                            @endforeach
                        </select>
                        <x-form-error name="motor_id" />
                    </div>
                    <div>
                        <label class="field-label">Tenor / jenis cicilan</label>
                        <select name="jenis_cicilan_id" class="field-select" required>
                            <option value="">Pilih tenor</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" @selected(old('jenis_cicilan_id') == $plan->id)>{{ $plan->nama_cicilan }} - {{ $plan->durasi_bulan }} bulan</option>
                            @endforeach
                        </select>
                        <x-form-error name="jenis_cicilan_id" />
                    </div>
                    <div>
                        <label class="field-label">Asuransi</label>
                        <select name="asuransi_id" class="field-select">
                            <option value="">Tanpa asuransi</option>
                            @foreach ($insurances as $insurance)
                                <option value="{{ $insurance->id }}" @selected(old('asuransi_id') == $insurance->id)>{{ $insurance->nama_asuransi }}</option>
                            @endforeach
                        </select>
                        <x-form-error name="asuransi_id" />
                    </div>
                    <div>
                        <label class="field-label">DP</label>
                        <input type="number" name="dp" class="field-input" value="{{ old('dp', 6000000) }}" min="0" required>
                        <p class="field-help">Perhitungan kredit utama akan memakai nilai ini.</p>
                        <x-form-error name="dp" />
                    </div>
                </div>
            </section>

            <section class="marketing-surface">
                <div class="dashboard-panel__head">
                    <div>
                        <p class="dashboard-kicker">Step 2</p>
                        <h3 class="marketing-section-title">Data finansial tambahan</h3>
                    </div>
                </div>

                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    <div>
                        <label class="field-label">Pekerjaan</label>
                        <input type="text" name="pekerjaan" class="field-input" value="{{ old('pekerjaan') }}">
                        <x-form-error name="pekerjaan" />
                    </div>
                    <div>
                        <label class="field-label">Nama perusahaan</label>
                        <input type="text" name="nama_perusahaan" class="field-input" value="{{ old('nama_perusahaan') }}">
                        <x-form-error name="nama_perusahaan" />
                    </div>
                    <div>
                        <label class="field-label">Lama bekerja</label>
                        <input type="text" name="lama_bekerja" class="field-input" value="{{ old('lama_bekerja') }}" placeholder="Contoh: 3 tahun">
                        <x-form-error name="lama_bekerja" />
                    </div>
                    <div class="md:col-span-2 xl:col-span-3">
                        <label class="field-label">Alamat kantor</label>
                        <textarea name="alamat_kantor" class="field-textarea">{{ old('alamat_kantor') }}</textarea>
                        <x-form-error name="alamat_kantor" />
                    </div>
                    <div>
                        <label class="field-label">Penghasilan bulanan</label>
                        <input type="number" name="penghasilan_bulanan" class="field-input" value="{{ old('penghasilan_bulanan') }}" min="0">
                        <x-form-error name="penghasilan_bulanan" />
                    </div>
                    <div>
                        <label class="field-label">Pengeluaran bulanan</label>
                        <input type="number" name="pengeluaran_bulanan" class="field-input" value="{{ old('pengeluaran_bulanan') }}" min="0">
                        <x-form-error name="pengeluaran_bulanan" />
                    </div>
                    <div>
                        <label class="field-label">Status rumah</label>
                        <select name="status_rumah" class="field-select">
                            <option value="">Pilih status</option>
                            @foreach (['Milik sendiri', 'Rumah keluarga', 'Kontrak', 'Kost', 'Rumah dinas'] as $status)
                                <option value="{{ $status }}" @selected(old('status_rumah') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                        <x-form-error name="status_rumah" />
                    </div>
                    <div>
                        <label class="field-label">Nama kontak darurat</label>
                        <input type="text" name="kontak_darurat_nama" class="field-input" value="{{ old('kontak_darurat_nama') }}">
                        <x-form-error name="kontak_darurat_nama" />
                    </div>
                    <div>
                        <label class="field-label">No. kontak darurat</label>
                        <input type="text" name="kontak_darurat_nohp" class="field-input" value="{{ old('kontak_darurat_nohp') }}">
                        <x-form-error name="kontak_darurat_nohp" />
                    </div>
                    <div>
                        <label class="field-label">Hubungan kontak darurat</label>
                        <input type="text" name="kontak_darurat_hubungan" class="field-input" value="{{ old('kontak_darurat_hubungan') }}">
                        <x-form-error name="kontak_darurat_hubungan" />
                    </div>
                </div>
            </section>

            <section class="marketing-surface">
                <div class="dashboard-panel__head">
                    <div>
                        <p class="dashboard-kicker">Step 3</p>
                        <h3 class="marketing-section-title">Upload dokumen pengajuan</h3>
                    </div>
                </div>

                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ([
                        'foto_ktp' => 'Foto KTP',
                        'foto_selfie_ktp' => 'Foto selfie dengan KTP',
                        'slip_gaji' => 'Slip gaji',
                        'npwp' => 'NPWP',
                        'kk' => 'Kartu keluarga',
                    ] as $key => $label)
                        <x-file-upload
                            :name="'documents['.$key.']'"
                            :label="$label"
                            accept=".jpg,.jpeg,.png,.pdf"
                            helper="Ukuran maksimal 5 MB per file."
                            :error="'documents.'.$key"
                        />
                    @endforeach
                </div>
            </section>

            <div class="hidden rounded-[1.6rem] border border-orange-100 bg-white/95 p-4 shadow-sm" data-upload-progress>
                <div class="flex items-center justify-between gap-3 text-sm font-semibold text-slate-700">
                    <span>Progress upload dokumen</span>
                    <span data-upload-text>Menyiapkan...</span>
                </div>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-[linear-gradient(90deg,#ff5f2f,#ff8d46)] transition-all duration-200" style="width: 0%" data-upload-bar></div>
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('marketing.pengajuan.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-accent" data-loading-text="Menyimpan pengajuan...">Simpan Pengajuan</button>
            </div>
        </form>
    </div>
@endsection

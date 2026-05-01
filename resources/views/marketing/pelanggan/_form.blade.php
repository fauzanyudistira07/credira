@php
    $record = $pelanggan ?? null;
    $user = $record?->user;
    $profileUrl = $record?->foto_profil_url;
    $ktpUrl = $record?->foto_ktp_url;
    $selfieUrl = $record?->foto_selfie_url;
@endphp

<div class="grid gap-6">
    <section class="marketing-surface">
        <div class="dashboard-panel__head">
            <div>
                <p class="dashboard-kicker">Identitas</p>
                <h3 class="marketing-section-title">Data utama pelanggan</h3>
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="field-label">Nama lengkap</label>
                <input type="text" name="nama_lengkap" class="field-input" value="{{ old('nama_lengkap', $record?->nama_lengkap) }}" required>
                <x-form-error name="nama_lengkap" />
            </div>
            <div>
                <label class="field-label">Email</label>
                <input type="email" name="email" class="field-input" value="{{ old('email', $record?->email ?? $user?->email) }}" required>
                <x-form-error name="email" />
            </div>
            <div>
                <label class="field-label">Kata sandi akun pelanggan</label>
                <input type="password" name="kata_sandi" class="field-input" placeholder="{{ $record ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter' }}" {{ $record ? '' : 'required' }}>
                <p class="field-help">{{ $record ? 'Hanya isi jika ingin mengganti password akun pelanggan.' : 'Password ini dipakai untuk akun login pelanggan.' }}</p>
                <x-form-error name="kata_sandi" />
            </div>
            <div>
                <label class="field-label">No. telepon</label>
                <input type="text" name="no_telp" class="field-input" value="{{ old('no_telp', $record?->no_telp) }}" required>
                <x-form-error name="no_telp" />
            </div>
            <div>
                <label class="field-label">No. KTP</label>
                <input type="text" name="no_ktp" class="field-input" value="{{ old('no_ktp', $record?->no_ktp) }}">
                <x-form-error name="no_ktp" />
            </div>
            <div>
                <label class="field-label">Tanggal lahir</label>
                <input type="date" name="tanggal_lahir" class="field-input" value="{{ old('tanggal_lahir', optional($record?->tanggal_lahir)->format('Y-m-d')) }}">
                <x-form-error name="tanggal_lahir" />
            </div>
            <div>
                <label class="field-label">Tempat lahir</label>
                <input type="text" name="tempat_lahir" class="field-input" value="{{ old('tempat_lahir', $record?->tempat_lahir) }}">
                <x-form-error name="tempat_lahir" />
            </div>
            <div>
                <label class="field-label">Jenis kelamin</label>
                <select name="jenis_kelamin" class="field-select">
                    <option value="">Pilih jenis kelamin</option>
                    @foreach (['Laki-laki', 'Perempuan'] as $option)
                        <option value="{{ $option }}" @selected(old('jenis_kelamin', $record?->jenis_kelamin) === $option)>{{ $option }}</option>
                    @endforeach
                </select>
                <x-form-error name="jenis_kelamin" />
            </div>
            <div>
                <label class="field-label">Status pernikahan</label>
                <select name="status_pernikahan" class="field-select">
                    <option value="">Pilih status</option>
                    @foreach (['Belum Menikah', 'Menikah', 'Cerai'] as $option)
                        <option value="{{ $option }}" @selected(old('status_pernikahan', $record?->status_pernikahan) === $option)>{{ $option }}</option>
                    @endforeach
                </select>
                <x-form-error name="status_pernikahan" />
            </div>
            <div>
                <label class="field-label">Pekerjaan default</label>
                <input type="text" name="pekerjaan_default" class="field-input" value="{{ old('pekerjaan_default', $record?->pekerjaan_default) }}">
                <x-form-error name="pekerjaan_default" />
            </div>
            <div>
                <label class="field-label">Penghasilan bulanan</label>
                <input type="number" name="penghasilan_default" class="field-input" value="{{ old('penghasilan_default', $record?->penghasilan_default) }}" min="0">
                <x-form-error name="penghasilan_default" />
            </div>
        </div>
    </section>

    <section class="marketing-surface">
        <div class="dashboard-panel__head">
            <div>
                <p class="dashboard-kicker">Alamat</p>
                <h3 class="marketing-section-title">Alamat utama pelanggan</h3>
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="field-label">Alamat lengkap</label>
                <textarea name="alamat1" class="field-textarea" placeholder="Alamat jalan, nomor rumah, patokan, dan informasi tambahan">{{ old('alamat1', $record?->alamat1) }}</textarea>
                <x-form-error name="alamat1" />
            </div>
            <div>
                <label class="field-label">Kota</label>
                <input type="text" name="kota1" class="field-input" value="{{ old('kota1', $record?->kota1) }}">
                <x-form-error name="kota1" />
            </div>
            <div>
                <label class="field-label">Provinsi</label>
                <input type="text" name="propinsi1" class="field-input" value="{{ old('propinsi1', $record?->propinsi1) }}">
                <x-form-error name="propinsi1" />
            </div>
            <div>
                <label class="field-label">Kode pos</label>
                <input type="text" name="kodepos1" class="field-input" value="{{ old('kodepos1', $record?->kodepos1) }}">
                <x-form-error name="kodepos1" />
            </div>
        </div>
    </section>

    <section class="marketing-surface">
        <div class="dashboard-panel__head">
            <div>
                <p class="dashboard-kicker">Dokumen</p>
                <h3 class="marketing-section-title">Upload foto dan identitas</h3>
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-3">
            <x-file-upload
                name="foto_profil"
                label="Foto profil"
                accept=".jpg,.jpeg,.png,.webp"
                helper="Gunakan foto yang jelas untuk identitas pelanggan."
                :existing-url="$profileUrl"
                existing-label="Foto profil saat ini"
                preview-type="image"
            />
            <x-file-upload
                name="foto_ktp"
                label="Foto KTP"
                accept=".jpg,.jpeg,.png,.pdf"
                helper="PDF atau gambar maksimal 5 MB."
                :existing-url="$ktpUrl"
                existing-label="Dokumen KTP saat ini"
            />
            <x-file-upload
                name="foto_selfie"
                label="Foto selfie"
                accept=".jpg,.jpeg,.png,.webp"
                helper="Selfie dengan pencahayaan yang cukup."
                :existing-url="$selfieUrl"
                existing-label="Foto selfie saat ini"
                preview-type="image"
            />
        </div>
    </section>

    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
        <a href="{{ $record ? route('marketing.pelanggan.show', $record) : route('marketing.pelanggan.index') }}" class="btn-secondary">Batal</a>
        <button type="submit" class="btn-accent" data-loading-text="Menyimpan...">{{ $submitLabel }}</button>
    </div>
</div>

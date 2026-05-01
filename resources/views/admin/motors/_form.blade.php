@php
    $editing = isset($motor);
    $normalizeStoredImage = static function (?string $path): ?string {
        if (! $path) {
            return null;
        }

        $normalizedPath = ltrim($path, '/');

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        if (str_starts_with($normalizedPath, 'storage/')) {
            return '/'.$normalizedPath;
        }

        if (str_starts_with($normalizedPath, 'public/')) {
            $normalizedPath = substr($normalizedPath, 7);
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($normalizedPath);
    };
@endphp

<div class="grid gap-6 xl:grid-cols-[1.08fr_0.92fr]">
    <section class="admin-detail-panel admin-form-shell space-y-6">
        <div>
            <p class="admin-eyebrow">Motor Form</p>
            <h2 class="mt-4 text-2xl font-semibold text-white">{{ $editing ? 'Edit master motor' : 'Tambah motor baru' }}</h2>
            <p class="mt-3 admin-copy">Gunakan schema existing `motors`, `jenis_motor`, dan `motor_images`. Upload foto utama tetap aman, sementara gallery tambahan akan ditambahkan tanpa menghapus data lama.</p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="field-label" for="jenis_motor_id">Jenis Motor</label>
                <select id="jenis_motor_id" name="jenis_motor_id" class="field-select">
                    <option value="">Pilih jenis motor</option>
                    @foreach ($jenisMotors as $jenis)
                        <option value="{{ $jenis->id }}" @selected((string) old('jenis_motor_id', $motor->jenis_motor_id ?? '') === (string) $jenis->id)>{{ $jenis->merk }} - {{ $jenis->jenis }}</option>
                    @endforeach
                </select>
                <x-form-error name="jenis_motor_id" />
            </div>

            <div>
                <label class="field-label" for="merk">Merk</label>
                <input id="merk" name="merk" type="text" class="field-input" value="{{ old('merk', $motor->merk ?? '') }}" placeholder="Honda, Yamaha, dll">
                <x-form-error name="merk" />
            </div>

            <div class="md:col-span-2">
                <label class="field-label" for="nama_motor">Nama Motor</label>
                <input id="nama_motor" name="nama_motor" type="text" class="field-input" value="{{ old('nama_motor', $motor->nama_motor ?? '') }}" placeholder="Contoh: Vario 160 ABS">
                <x-form-error name="nama_motor" />
            </div>

            <div>
                <label class="field-label" for="harga_jual">Harga Jual</label>
                <input id="harga_jual" name="harga_jual" type="number" min="0" class="field-input" value="{{ old('harga_jual', $motor->harga_jual ?? '') }}" placeholder="0">
                <x-form-error name="harga_jual" />
            </div>

            <div>
                <label class="field-label" for="stok">Stok</label>
                <input id="stok" name="stok" type="number" min="0" class="field-input" value="{{ old('stok', $motor->stok ?? 0) }}" placeholder="0">
                <x-form-error name="stok" />
            </div>

            <div>
                <label class="field-label" for="warna">Warna</label>
                <input id="warna" name="warna" type="text" class="field-input" value="{{ old('warna', $motor->warna ?? '') }}" placeholder="Matte Black, White, dll">
                <x-form-error name="warna" />
            </div>

            <div>
                <label class="field-label" for="kapasitas_mesin">Kapasitas Mesin</label>
                <input id="kapasitas_mesin" name="kapasitas_mesin" type="text" class="field-input" value="{{ old('kapasitas_mesin', $motor->kapasitas_mesin ?? '') }}" placeholder="160cc">
                <x-form-error name="kapasitas_mesin" />
            </div>

            <div>
                <label class="field-label" for="transmisi">Transmisi</label>
                <input id="transmisi" name="transmisi" type="text" class="field-input" value="{{ old('transmisi', $motor->transmisi ?? '') }}" placeholder="CVT / Manual">
                <x-form-error name="transmisi" />
            </div>

            <div>
                <label class="field-label" for="bahan_bakar">Bahan Bakar</label>
                <input id="bahan_bakar" name="bahan_bakar" type="text" class="field-input" value="{{ old('bahan_bakar', $motor->bahan_bakar ?? '') }}" placeholder="Bensin">
                <x-form-error name="bahan_bakar" />
            </div>

            <div>
                <label class="field-label" for="tahun_produksi">Tahun Produksi</label>
                <input id="tahun_produksi" name="tahun_produksi" type="number" min="1900" max="2100" class="field-input" value="{{ old('tahun_produksi', $motor->tahun_produksi ?? '') }}" placeholder="2026">
                <x-form-error name="tahun_produksi" />
            </div>

            <div>
                <label class="field-label" for="berat">Berat (kg)</label>
                <input id="berat" name="berat" type="number" min="0" class="field-input" value="{{ old('berat', $motor->berat ?? '') }}" placeholder="0">
                <x-form-error name="berat" />
            </div>

            <div class="md:col-span-2">
                <label class="field-label" for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" class="field-textarea" placeholder="Deskripsi singkat motor">{{ old('deskripsi', $motor->deskripsi ?? '') }}</textarea>
                <x-form-error name="deskripsi" />
            </div>

            <div class="md:col-span-2">
                <label class="field-label" for="deskripsi_motor">Deskripsi Motor / Teks Tambahan</label>
                <textarea id="deskripsi_motor" name="deskripsi_motor" class="field-textarea" placeholder="Teks detail tambahan bila digunakan oleh schema existing">{{ old('deskripsi_motor', $motor->deskripsi_motor ?? '') }}</textarea>
                <x-form-error name="deskripsi_motor" />
            </div>
        </div>
    </section>

    <aside class="space-y-6">
        <section class="admin-summary-card admin-form-shell space-y-5">
            <p class="admin-summary-card__label">Status dan visual</p>

            <label class="admin-check-row">
                <input type="hidden" name="status_aktif" value="0">
                <input type="checkbox" name="status_aktif" value="1" class="admin-checkbox" @checked((string) old('status_aktif', isset($motor) ? (int) $motor->status_aktif : 1) === '1')>
                <span>
                    <strong class="block text-white">Aktif</strong>
                    <span class="text-sm text-slate-300">Tampilkan unit di katalog aktif.</span>
                </span>
            </label>

            <label class="admin-check-row">
                <input type="hidden" name="is_featured" value="0">
                <input type="checkbox" name="is_featured" value="1" class="admin-checkbox" @checked((string) old('is_featured', isset($motor) ? (int) $motor->is_featured : 0) === '1')>
                <span>
                    <strong class="block text-white">Featured</strong>
                    <span class="text-sm text-slate-300">Tandai unit unggulan di area marketing/publik.</span>
                </span>
            </label>
        </section>

        <section class="admin-summary-card admin-form-shell space-y-4">
            <p class="admin-summary-card__label">Foto utama</p>
            @foreach (['foto1', 'foto2', 'foto3'] as $field)
                <x-file-upload
                    :name="$field"
                    :label="strtoupper($field)"
                    accept="image/*"
                    helper="Upload foto utama dengan rasio yang rapi."
                    :existing-url="$editing && filled($motor->{$field}) ? $normalizeStoredImage($motor->{$field}) : null"
                    :existing-label="'Foto '.strtoupper($field).' saat ini'"
                    preview-type="image"
                />
            @endforeach
        </section>

        <section class="admin-summary-card admin-form-shell space-y-4">
            <p class="admin-summary-card__label">Gallery tambahan</p>
            <x-file-upload
                name="gallery_images[]"
                label="Upload beberapa gambar"
                accept="image/*"
                helper="Gambar tambahan akan masuk ke tabel `motor_images` sebagai append yang aman."
                error="gallery_images"
                :multiple="true"
                preview-type="image"
            />
            <x-form-error name="gallery_images.*" />
        </section>

        <section class="admin-summary-card">
            <div class="flex flex-col gap-3">
                <button type="submit" class="btn-accent w-full" data-loading-text="Menyimpan motor...">{{ $editing ? 'Simpan Perubahan' : 'Simpan Motor' }}</button>
                <a href="{{ route('admin.motors.index') }}" class="btn-secondary w-full">Batal</a>
            </div>
        </section>
    </aside>
</div>

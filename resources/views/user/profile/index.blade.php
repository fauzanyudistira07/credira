@extends('layouts.user', [
    'title' => 'Profil Saya',
    'heading' => 'Profil Nasabah',
    'subheading' => 'Kelola identitas, keamanan akun, foto profil, dan alamat pengiriman motor Anda.',
])

@php
    $profilePhoto = $pelanggan->foto_profil_url;
    $initials = collect(preg_split('/\s+/', trim($pelanggan->nama_lengkap ?: auth()->user()->name)))
        ->filter()
        ->take(2)
        ->map(fn (string $part) => strtoupper(substr($part, 0, 1)))
        ->implode('');
@endphp

@section('content')
    <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
        <div class="app-panel-dark">
            <p class="app-kicker">Akun Nasabah</p>
            <div class="mt-6 flex items-center gap-4">
                @if ($profilePhoto)
                    <img src="{{ $profilePhoto }}" alt="{{ $pelanggan->nama_lengkap }}" class="h-20 w-20 rounded-[1.7rem] object-cover shadow-[0_20px_50px_-24px_rgba(0,0,0,0.45)]">
                @else
                    <div class="app-avatar !h-20 !w-20 !rounded-[1.7rem] !text-xl">{{ $initials }}</div>
                @endif
                <div class="min-w-0">
                    <h2 class="truncate text-2xl font-semibold text-white">{{ $pelanggan->nama_lengkap }}</h2>
                    <p class="mt-2 truncate text-sm text-white/68">{{ $pelanggan->email }}</p>
                    <p class="mt-1 truncate text-sm text-white/68">{{ $pelanggan->no_telp ?: 'Nomor HP belum diisi' }}</p>
                </div>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Alamat</p>
                    <p class="mt-3 text-3xl font-semibold text-white">{{ $addresses->count() }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Profil</p>
                    <p class="mt-3 text-base font-semibold text-white">Data nasabah aktif</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Login</p>
                    <p class="mt-3 text-base font-semibold text-white">Aman dan personal</p>
                </div>
            </div>

            <div class="mt-6 rounded-[1.7rem] border border-white/10 bg-white/7 p-5 text-sm leading-7 text-white/72">
                Halaman ini menyatukan identitas nasabah, data pekerjaan default untuk pengajuan baru, keamanan akun, dan daftar alamat pengiriman motor dalam satu pengalaman yang lebih mirip aplikasi pembiayaan profesional.
            </div>
        </div>

        <div class="app-panel-dark">
            <div class="mb-6">
                <p class="app-kicker">Data Profil</p>
                <h2 class="mt-3 text-2xl font-semibold text-slate-950">Perbarui identitas utama akun Anda</h2>
            </div>
            <form method="POST" action="{{ route('user.profile.update') }}" enctype="multipart/form-data" class="grid gap-5 md:grid-cols-2">
                @csrf
                @method('PUT')
                <div class="md:col-span-2">
                    <label class="field-label">Nama lengkap</label>
                    <input type="text" name="nama_lengkap" class="field-input" value="{{ old('nama_lengkap', $pelanggan->nama_lengkap) }}">
                    <x-form-error name="nama_lengkap" />
                </div>
                <div>
                    <label class="field-label">Email</label>
                    <input type="email" name="email" class="field-input" value="{{ old('email', $pelanggan->email) }}">
                    <x-form-error name="email" />
                </div>
                <div>
                    <label class="field-label">No. HP</label>
                    <input type="text" name="no_telp" class="field-input" value="{{ old('no_telp', $pelanggan->no_telp) }}">
                    <x-form-error name="no_telp" />
                </div>
                <div>
                    <label class="field-label">No. KTP</label>
                    <input type="text" name="no_ktp" class="field-input" value="{{ old('no_ktp', $pelanggan->no_ktp) }}">
                    <x-form-error name="no_ktp" />
                </div>
                <div>
                    <label class="field-label">Tempat lahir</label>
                    <input type="text" name="tempat_lahir" class="field-input" value="{{ old('tempat_lahir', $pelanggan->tempat_lahir) }}">
                    <x-form-error name="tempat_lahir" />
                </div>
                <div>
                    <label class="field-label">Tanggal lahir</label>
                    <input type="date" name="tanggal_lahir" class="field-input" value="{{ old('tanggal_lahir', optional($pelanggan->tanggal_lahir)->format('Y-m-d')) }}">
                    <x-form-error name="tanggal_lahir" />
                </div>
                <div>
                    <label class="field-label">Jenis kelamin</label>
                    <select name="jenis_kelamin" class="field-select">
                        <option value="">Pilih</option>
                        <option value="Laki-laki" @selected(old('jenis_kelamin', $pelanggan->jenis_kelamin) === 'Laki-laki')>Laki-laki</option>
                        <option value="Perempuan" @selected(old('jenis_kelamin', $pelanggan->jenis_kelamin) === 'Perempuan')>Perempuan</option>
                    </select>
                    <x-form-error name="jenis_kelamin" />
                </div>
                <div>
                    <label class="field-label">Status pernikahan</label>
                    <input type="text" name="status_pernikahan" class="field-input" value="{{ old('status_pernikahan', $pelanggan->status_pernikahan) }}">
                    <x-form-error name="status_pernikahan" />
                </div>
                <div>
                    <label class="field-label">Pekerjaan default</label>
                    <input type="text" name="pekerjaan_default" class="field-input" value="{{ old('pekerjaan_default', $pelanggan->pekerjaan_default) }}">
                    <x-form-error name="pekerjaan_default" />
                </div>
                <div>
                    <label class="field-label">Penghasilan default</label>
                    <input type="number" name="penghasilan_default" class="field-input" value="{{ old('penghasilan_default', $pelanggan->penghasilan_default) }}">
                    <x-form-error name="penghasilan_default" />
                </div>
                <div class="md:col-span-2">
                    <label class="field-label">Foto profil</label>
                    <input type="file" name="foto_profil" class="field-input">
                    <x-form-error name="foto_profil" />
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="btn-primary">Simpan Profil</button>
                </div>
            </form>
        </div>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-[0.92fr_1.08fr]">
        <div class="app-panel-dark">
            <div class="mb-6">
                <p class="app-kicker">Keamanan Akun</p>
                <h2 class="mt-3 text-2xl font-semibold text-slate-950">Ubah password login</h2>
            </div>
            <form method="POST" action="{{ route('user.profile.password') }}" class="grid gap-5">
                @csrf
                @method('PUT')
                <div>
                    <label class="field-label">Password saat ini</label>
                    <input type="password" name="current_password" class="field-input">
                    <x-form-error name="current_password" />
                </div>
                <div>
                    <label class="field-label">Password baru</label>
                    <input type="password" name="password" class="field-input">
                    <x-form-error name="password" />
                </div>
                <div>
                    <label class="field-label">Konfirmasi password baru</label>
                    <input type="password" name="password_confirmation" class="field-input">
                </div>
                <button type="submit" class="btn-primary">Ubah Password</button>
            </form>
        </div>

        <div class="app-panel-dark">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="app-kicker">Alamat Pengiriman</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-950">Kelola alamat untuk pengiriman motor</h2>
                </div>
            </div>

            <div class="mt-6 grid gap-4">
                @forelse ($addresses as $address)
                    <article class="app-list-card-muted !border-white/10 !bg-white/6">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <p class="font-semibold text-slate-950">{{ $address->label_alamat }}</p>
                                    @if ($address->is_primary)
                                        <x-status-badge status="utama" />
                                    @endif
                                </div>
                                <p class="mt-3 text-sm text-slate-600">{{ $address->penerima }} &middot; {{ $address->no_telp }}</p>
                                <p class="mt-2 text-sm leading-7 text-slate-600">{{ $address->alamat_lengkap }}, {{ $address->kota }}, {{ $address->provinsi }} {{ $address->kode_pos }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-modal title="Edit alamat" description="Perbarui detail alamat pengiriman untuk akun Anda.">
                                    <x-slot:trigger>
                                        <button type="button" class="btn-ghost">Edit</button>
                                    </x-slot:trigger>

                                    <form method="POST" action="{{ route('user.addresses.update', $address) }}" class="grid gap-4">
                                        @csrf
                                        @method('PUT')
                                        <div>
                                            <label class="field-label">Label alamat</label>
                                            <input type="text" name="label_alamat" class="field-input" value="{{ $address->label_alamat }}">
                                        </div>
                                        <div>
                                            <label class="field-label">Penerima</label>
                                            <input type="text" name="penerima" class="field-input" value="{{ $address->penerima }}">
                                        </div>
                                        <div>
                                            <label class="field-label">No. HP</label>
                                            <input type="text" name="no_telp" class="field-input" value="{{ $address->no_telp }}">
                                        </div>
                                        <div>
                                            <label class="field-label">Kode pos</label>
                                            <input type="text" name="kode_pos" class="field-input" value="{{ $address->kode_pos }}">
                                        </div>
                                        <div>
                                            <label class="field-label">Kota</label>
                                            <input type="text" name="kota" class="field-input" value="{{ $address->kota }}">
                                        </div>
                                        <div>
                                            <label class="field-label">Provinsi</label>
                                            <input type="text" name="provinsi" class="field-input" value="{{ $address->provinsi }}">
                                        </div>
                                        <div>
                                            <label class="field-label">Alamat lengkap</label>
                                            <textarea name="alamat_lengkap" class="field-textarea">{{ $address->alamat_lengkap }}</textarea>
                                        </div>
                                        <label class="flex items-center gap-3 text-sm text-slate-300">
                                            <input type="checkbox" name="is_primary" value="1" class="rounded border-slate-300" @checked($address->is_primary)>
                                            Jadikan alamat utama
                                        </label>
                                        <div class="flex justify-end">
                                            <button type="submit" class="btn-primary">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </x-modal>

                                <form method="POST" action="{{ route('user.addresses.destroy', $address) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-ghost">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <x-empty-state
                        title="Belum ada alamat"
                        description="Tambahkan alamat pengiriman untuk mempercepat proses pengajuan dan pengiriman motor."
                    />
                @endforelse
            </div>

                <div class="mt-6 rounded-[1.8rem] border border-white/10 bg-white/6 p-5">
                <p class="font-semibold text-white">Tambah alamat baru</p>
                <form method="POST" action="{{ route('user.addresses.store') }}" class="mt-5 grid gap-4 md:grid-cols-2">
                    @csrf
                    <div>
                        <label class="field-label">Label alamat</label>
                        <input type="text" name="label_alamat" class="field-input" placeholder="Rumah / Kantor">
                        <x-form-error name="label_alamat" />
                    </div>
                    <div>
                        <label class="field-label">Penerima</label>
                        <input type="text" name="penerima" class="field-input">
                        <x-form-error name="penerima" />
                    </div>
                    <div>
                        <label class="field-label">No. HP</label>
                        <input type="text" name="no_telp" class="field-input">
                        <x-form-error name="no_telp" />
                    </div>
                    <div>
                        <label class="field-label">Kode pos</label>
                        <input type="text" name="kode_pos" class="field-input">
                        <x-form-error name="kode_pos" />
                    </div>
                    <div>
                        <label class="field-label">Kota</label>
                        <input type="text" name="kota" class="field-input">
                        <x-form-error name="kota" />
                    </div>
                    <div>
                        <label class="field-label">Provinsi</label>
                        <input type="text" name="provinsi" class="field-input">
                        <x-form-error name="provinsi" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="field-label">Alamat lengkap</label>
                        <textarea name="alamat_lengkap" class="field-textarea"></textarea>
                        <x-form-error name="alamat_lengkap" />
                    </div>
                    <label class="flex items-center gap-3 text-sm text-slate-300 md:col-span-2">
                        <input type="checkbox" name="is_primary" value="1" class="rounded border-slate-300">
                        Jadikan alamat utama
                    </label>
                    <div class="md:col-span-2">
                        <button type="submit" class="btn-primary">Tambah Alamat</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

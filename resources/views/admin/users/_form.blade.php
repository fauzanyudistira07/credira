@php
    $editing = isset($user);
@endphp

<div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
    <section class="admin-detail-panel admin-form-shell space-y-6">
        <div>
            <p class="admin-eyebrow">User Form</p>
            <h2 class="mt-4 text-2xl font-semibold text-white">{{ $editing ? 'Edit data user' : 'Tambah user baru' }}</h2>
            <p class="mt-3 admin-copy">Admin dapat mengelola akun inti sistem dengan role yang dibatasi ke `admin`, `marketing`, dan `ceo`.</p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="field-label" for="name">Nama</label>
                <input id="name" name="name" type="text" class="field-input" value="{{ old('name', $user->name ?? '') }}" placeholder="Nama lengkap">
                <x-form-error name="name" />
            </div>

            <div class="md:col-span-2">
                <label class="field-label" for="email">Email</label>
                <input id="email" name="email" type="email" class="field-input" value="{{ old('email', $user->email ?? '') }}" placeholder="nama@credira.id">
                <x-form-error name="email" />
            </div>

            <div>
                <label class="field-label" for="password">Password {{ $editing ? '(opsional)' : '' }}</label>
                <input id="password" name="password" type="password" class="field-input" placeholder="{{ $editing ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter' }}">
                <x-form-error name="password" />
            </div>

            <div>
                <label class="field-label" for="password_confirmation">Konfirmasi Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="field-input" placeholder="Ulangi password">
            </div>

            <div>
                <label class="field-label" for="role">Role</label>
                <select id="role" name="role" class="field-select">
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" @selected(old('role', $user->role ?? '') === $role)>{{ strtoupper($role) }}</option>
                    @endforeach
                </select>
                <x-form-error name="role" />
            </div>

            <div class="admin-metric-card">
                <p class="admin-metric-card__label">Catatan keamanan</p>
                <p class="mt-2 text-sm leading-7 text-slate-300">Role akun yang sedang login tidak boleh diturunkan dari `admin` agar akses ke console tidak putus.</p>
            </div>
        </div>
    </section>

    <aside class="space-y-6">
        <section class="admin-summary-card">
            <p class="admin-summary-card__label">Role guide</p>
            <div class="mt-5 grid gap-3">
                <div class="admin-metric-card">
                    <p class="admin-metric-card__value">Admin</p>
                    <p class="mt-2 text-sm text-slate-300">Mengelola sistem inti, user, motor, dan review pengajuan.</p>
                </div>
                <div class="admin-metric-card">
                    <p class="admin-metric-card__value">Marketing</p>
                    <p class="mt-2 text-sm text-slate-300">Mengelola pelanggan miliknya dan membuat pengajuan baru.</p>
                </div>
                <div class="admin-metric-card">
                    <p class="admin-metric-card__value">CEO</p>
                    <p class="mt-2 text-sm text-slate-300">Membaca ringkasan bisnis tanpa area operasional detail.</p>
                </div>
            </div>
        </section>

        <section class="admin-summary-card">
            <p class="admin-summary-card__label">Actions</p>
            <div class="mt-5 flex flex-col gap-3">
                <button type="submit" class="btn-accent w-full">{{ $editing ? 'Simpan Perubahan' : 'Buat User' }}</button>
                <a href="{{ route('admin.users.index') }}" class="btn-secondary w-full">Batal</a>
            </div>
        </section>
    </aside>
</div>

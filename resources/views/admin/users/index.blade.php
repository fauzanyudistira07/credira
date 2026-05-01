@extends('layouts.admin', [
    'title' => 'Manajemen User',
    'heading' => 'Manajemen User',
    'subheading' => 'Kelola akun admin, marketing, dan ceo tanpa mengubah struktur sistem yang sudah ada.',
])

@section('content')
    <div class="space-y-6">
        <section class="admin-hero-panel">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <span class="admin-eyebrow">System Access</span>
                    <h2 class="mt-5 max-w-3xl text-3xl font-semibold tracking-[-0.04em] text-white sm:text-4xl">Area user management yang rapi, aman, dan tetap konsisten dengan flow Credira.</h2>
                    <p class="mt-4 max-w-2xl admin-copy">Filter cepat berdasarkan nama, email, dan role lalu masuk ke form edit tanpa mengganggu akun lain yang sudah aktif di sistem.</p>
                </div>
                <div class="admin-hero-actions">
                    <a href="{{ route('admin.users.create') }}" class="btn-accent">Tambah User</a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="admin-secondary-stat">
                <p class="admin-secondary-stat__label">Total users</p>
                <p class="admin-secondary-stat__value">{{ number_format($summary['total']) }}</p>
                <p class="admin-secondary-stat__copy">Seluruh akun yang terdaftar pada sistem.</p>
            </article>
            <article class="admin-secondary-stat">
                <p class="admin-secondary-stat__label">Admin</p>
                <p class="admin-secondary-stat__value">{{ number_format($summary['admin']) }}</p>
                <p class="admin-secondary-stat__copy">Pengelola console dan operasional inti.</p>
            </article>
            <article class="admin-secondary-stat">
                <p class="admin-secondary-stat__label">Marketing</p>
                <p class="admin-secondary-stat__value">{{ number_format($summary['marketing']) }}</p>
                <p class="admin-secondary-stat__copy">Owner pelanggan dan pembuat pengajuan.</p>
            </article>
            <article class="admin-secondary-stat">
                <p class="admin-secondary-stat__label">CEO</p>
                <p class="admin-secondary-stat__value">{{ number_format($summary['ceo']) }}</p>
                <p class="admin-secondary-stat__copy">Akses ringkasan strategis.</p>
            </article>
        </section>

        <x-filter-toolbar
            class="admin-filter-panel"
            title="Cari dan filter user"
            description="Filter tetap tersimpan saat berpindah halaman agar manajemen akses lebih cepat."
            :result-text="$users->total().' user ditemukan'"
            :active-filters="array_values(array_filter([
                filled($filters['q'] ?? null) ? 'Cari: '.($filters['q'] ?? '') : null,
                filled($filters['role'] ?? null) ? 'Role: '.strtoupper($filters['role']) : null,
            ]))"
            :reset-href="route('admin.users.index')"
        >
            <form method="GET" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_0.7fr_auto]">
                <div>
                    <label class="field-label">Cari nama atau email</label>
                    <input type="text" name="q" class="field-input" value="{{ $filters['q'] ?? '' }}" placeholder="Cari user" data-chip-label="Cari">
                </div>
                <div>
                    <label class="field-label">Filter role</label>
                    <select name="role" class="field-select" data-chip-label="Role">
                        <option value="">Semua role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" @selected(($filters['role'] ?? '') === $role)>{{ strtoupper($role) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-3">
                    <button type="submit" class="btn-accent">Filter</button>
                    @if (filled($filters['q'] ?? null) || filled($filters['role'] ?? null))
                        <a href="{{ route('admin.users.index') }}" class="btn-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </x-filter-toolbar>

        @if ($users->isEmpty())
            <x-empty-state
                title="Belum ada data user"
                description="User akan muncul di sini setelah dibuat. Gunakan tombol tambah user untuk mulai mengelola akses sistem."
                action-label="Tambah User"
                action-href="{{ route('admin.users.create') }}"
            />
        @else
            <x-table-shell class="admin-stream-panel" title="Daftar user" description="Role badge dan aksi dibuat lebih ringkas untuk audit akses.">
                <div class="admin-table-wrap overflow-x-auto">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Dibuat</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $item)
                                <tr>
                                    <td>
                                        <p class="font-semibold text-white">{{ $item->name }}</p>
                                        @if (auth()->id() === $item->id)
                                            <p class="mt-1 text-xs uppercase tracking-[0.2em] text-orange-200">Akun Anda</p>
                                        @endif
                                    </td>
                                    <td>{{ $item->email }}</td>
                                    <td><x-status-badge :status="$item->role" class="!border-white/10 !bg-white/8 !text-orange-100" /></td>
                                    <td>{{ $item->created_at?->format('d M Y H:i') }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.users.edit', $item) }}" class="admin-text-link">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-table-shell>

            <x-pagination :paginator="$users" surface-class="text-white" />
        @endif
    </div>
@endsection

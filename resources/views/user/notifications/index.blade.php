@extends('layouts.user', [
    'title' => 'Notifikasi',
    'heading' => 'Notifikasi & Aktivitas',
    'subheading' => 'Semua update pengajuan, pembayaran, dan pengiriman tersimpan sebagai pusat aktivitas akun Anda.',
])

@php($unreadCount = $notifications->getCollection()->where('is_read', false)->count())

@section('content')
    <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <div class="app-panel-dark">
            <p class="app-kicker">Notification Center</p>
            <h2 class="mt-4 text-3xl font-semibold leading-tight text-white sm:text-4xl">Perubahan status akun tampil seperti feed aktivitas aplikasi kredit motor.</h2>
            <p class="mt-4 max-w-2xl text-sm leading-7 text-white/72">Saat pengajuan bergerak, pembayaran diverifikasi, atau pengiriman diperbarui, seluruh notifikasi akan muncul di sini agar user tidak kehilangan informasi penting.</p>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Di halaman ini</p>
                    <p class="mt-3 text-3xl font-semibold text-white">{{ $notifications->count() }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Belum dibaca</p>
                    <p class="mt-3 text-3xl font-semibold text-white">{{ $unreadCount }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Aksi cepat</p>
                    <form method="POST" action="{{ route('user.notifications.read-all') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="btn-outline-light w-full">Tandai Semua</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="app-panel">
            <p class="app-kicker">Kategori Aktivitas</p>
            <div class="mt-6 grid gap-3">
                <div class="app-list-card-muted">
                    <p class="font-semibold text-slate-950">Pengajuan</p>
                    <p class="mt-2 text-sm leading-7 text-slate-600">Notifikasi verifikasi dokumen, review kredit, approval, dan kontrak aktif.</p>
                </div>
                <div class="app-list-card-muted">
                    <p class="font-semibold text-slate-950">Pembayaran</p>
                    <p class="mt-2 text-sm leading-7 text-slate-600">Status transfer pending, valid, atau membutuhkan perbaikan data.</p>
                </div>
                <div class="app-list-card-muted">
                    <p class="font-semibold text-slate-950">Pengiriman</p>
                    <p class="mt-2 text-sm leading-7 text-slate-600">Update jadwal antar, nama kurir, dan status unit sampai tujuan.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-6 app-panel">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="app-kicker">Feed Aktivitas</p>
                <h2 class="mt-3 text-2xl font-semibold text-slate-950">Riwayat perubahan status, pembayaran, dan pengiriman</h2>
            </div>
            <form method="POST" action="{{ route('user.notifications.read-all') }}">
                @csrf
                <button type="submit" class="btn-secondary">Tandai Semua Dibaca</button>
            </form>
        </div>

        <div class="mt-6 grid gap-4">
            @forelse ($notifications as $notification)
                <article class="app-list-card {{ $notification->is_read ? '' : 'border-slate-900' }}">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <p class="text-lg font-semibold text-slate-950">{{ $notification->title }}</p>
                                @unless ($notification->is_read)
                                    <span class="rounded-full bg-[#17181d] px-3 py-1 text-xs font-semibold text-white">Baru</span>
                                @endunless
                            </div>
                            <p class="mt-3 text-sm leading-7 text-slate-600">{{ $notification->message }}</p>
                            <p class="mt-3 text-xs uppercase tracking-[0.2em] text-slate-400">{{ $notification->created_at->translatedFormat('d M Y H:i') }}</p>
                        </div>
                        @unless ($notification->is_read)
                            <form method="POST" action="{{ route('user.notifications.read', $notification) }}">
                                @csrf
                                <button type="submit" class="btn-ghost">Tandai Dibaca</button>
                            </form>
                        @endunless
                    </div>
                </article>
            @empty
                <x-empty-state
                    title="Belum ada notifikasi"
                    description="Update aktivitas akun akan tampil di sini setelah pengajuan, pembayaran, atau pengiriman bergerak."
                />
            @endforelse
        </div>

        <div class="mt-6">{{ $notifications->links() }}</div>
    </section>
@endsection

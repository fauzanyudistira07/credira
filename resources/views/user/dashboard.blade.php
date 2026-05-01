@extends('layouts.user', [
    'title' => 'Beranda Credira',
    'heading' => 'Beranda Pembiayaan',
    'subheading' => 'Akses semua fitur kredit motor Anda dari pengajuan awal sampai motor diterima.',
])

@php
    $activeApplication = $dashboard['active_application'];
    $nextInstallment = $dashboard['next_installment'];
    $activeDelivery = $dashboard['active_delivery'];
@endphp

@section('content')
    <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <div class="app-panel-dark">
            <p class="app-kicker">Aplikasi Kredit Motor</p>
            <div class="mt-4 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <h2 class="text-3xl font-semibold leading-tight sm:text-4xl">Semua progres pembiayaan motor Anda kini terasa seperti satu aplikasi profesional.</h2>
                    <p class="mt-4 max-w-xl text-sm leading-7 text-white/72">Pantau pengajuan, cek tagihan bulanan, unggah bukti pembayaran, lacak pengiriman unit, dan kelola profil nasabah dari satu beranda yang terhubung langsung ke database MySQL.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('user.applications.create') }}" class="btn-accent">Ajukan Motor</a>
                    <a href="{{ route('user.payments.index') }}" class="btn-outline-light">Riwayat Pembayaran</a>
                </div>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Pengajuan aktif</p>
                    <p class="mt-3 text-3xl font-semibold text-white">{{ $dashboard['active_applications'] }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Tagihan berjalan</p>
                    <p class="mt-3 text-3xl font-semibold text-white">{{ $dashboard['pending_installments_count'] }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Belum dibayar</p>
                    <p class="mt-3 text-2xl font-semibold text-white">Rp {{ number_format($dashboard['unpaid_installment_total'], 0, ',', '.') }}</p>
                </div>
                <div class="app-metric-card-dark">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-white/46">Notifikasi baru</p>
                    <p class="mt-3 text-3xl font-semibold text-white">{{ $dashboard['unread_notifications'] }}</p>
                </div>
            </div>
        </div>

        <div class="app-panel">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="app-kicker">Kontrak Utama</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-950">Ringkasan kredit yang sedang berjalan</h2>
                </div>
                <x-status-badge :status="$activeApplication?->status_pengajuan ?? 'draft'" />
            </div>

            @if ($activeApplication)
                <div class="mt-6 rounded-[1.75rem] border border-[#ece4db] bg-[#fffaf6] p-5">
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-500">{{ $activeApplication->kode_pengajuan }}</p>
                    <h3 class="mt-3 text-xl font-semibold text-slate-950">{{ $activeApplication->motor->nama_motor }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $activeApplication->jenisCicilan->nama_cicilan ?? 'Skema pembiayaan aktif' }}</p>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="app-metric-card">
                        <p class="text-sm text-slate-500">DP</p>
                        <p class="mt-2 text-xl font-semibold text-slate-950">Rp {{ number_format($activeApplication->dp, 0, ',', '.') }}</p>
                    </div>
                    <div class="app-metric-card">
                        <p class="text-sm text-slate-500">Total pengajuan</p>
                        <p class="mt-2 text-xl font-semibold text-slate-950">Rp {{ number_format($activeApplication->total_bayar, 0, ',', '.') }}</p>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="{{ route('user.applications.show', $activeApplication) }}" class="btn-primary">Lihat Detail Pengajuan</a>
                    <a href="{{ route('user.installments.index') }}" class="btn-secondary">Buka Jadwal Angsuran</a>
                </div>
            @else
                <div class="mt-6">
                    <x-empty-state
                        title="Belum ada kredit aktif"
                        description="Mulai pengajuan pertama Anda untuk melihat simulasi, status verifikasi, angsuran, dan pengiriman motor di beranda ini."
                        action-label="Mulai Pengajuan"
                        action-href="{{ route('user.applications.create') }}"
                    />
                </div>
            @endif
        </div>
    </section>

    <section class="mt-6 app-panel">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="app-kicker">Fitur User</p>
                <h2 class="mt-3 text-2xl font-semibold text-slate-950">Semua menu penting kredit motor tampil langsung di beranda</h2>
            </div>
            <a href="{{ route('user.profile.index') }}" class="btn-secondary hidden md:inline-flex">Kelola Profil</a>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <a href="{{ route('user.applications.index') }}" class="app-quick-link">
                <p class="app-kicker">Pengajuan</p>
                <h3 class="mt-3 text-xl font-semibold text-slate-950">Pantau status kredit motor</h3>
                <p class="mt-2 text-sm leading-7 text-slate-600">Lihat draft, verifikasi dokumen, approval, hingga kontrak aktif.</p>
            </a>
            <a href="{{ route('user.installments.index') }}" class="app-quick-link">
                <p class="app-kicker">Angsuran</p>
                <h3 class="mt-3 text-xl font-semibold text-slate-950">Cek jatuh tempo bulanan</h3>
                <p class="mt-2 text-sm leading-7 text-slate-600">Tagihan berikutnya, status telat, dan histori pembayaran semuanya ada di sini.</p>
            </a>
            <a href="{{ route('user.payments.create') }}" class="app-quick-link">
                <p class="app-kicker">Pembayaran</p>
                <h3 class="mt-3 text-xl font-semibold text-slate-950">Upload bukti transfer</h3>
                <p class="mt-2 text-sm leading-7 text-slate-600">Kirim bukti pembayaran angsuran dan pantau status verifikasinya.</p>
            </a>
            <a href="{{ route('user.deliveries.index') }}" class="app-quick-link">
                <p class="app-kicker">Pengiriman</p>
                <h3 class="mt-3 text-xl font-semibold text-slate-950">Lacak motor yang dikirim</h3>
                <p class="mt-2 text-sm leading-7 text-slate-600">Lihat status kurir, invoice, dan estimasi motor sampai di alamat Anda.</p>
            </a>
            <a href="{{ route('user.notifications.index') }}" class="app-quick-link">
                <p class="app-kicker">Notifikasi</p>
                <h3 class="mt-3 text-xl font-semibold text-slate-950">Semua update akun tersimpan</h3>
                <p class="mt-2 text-sm leading-7 text-slate-600">Perubahan status pengajuan, pembayaran, dan pengiriman muncul real-time.</p>
            </a>
            <a href="{{ route('user.profile.index') }}" class="app-quick-link">
                <p class="app-kicker">Profil</p>
                <h3 class="mt-3 text-xl font-semibold text-slate-950">Data nasabah dan alamat</h3>
                <p class="mt-2 text-sm leading-7 text-slate-600">Kelola identitas, foto profil, password, dan alamat pengiriman motor.</p>
            </a>
        </div>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-3">
        <div class="app-panel">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="app-kicker">Tagihan Berikutnya</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-950">Angsuran yang perlu diprioritaskan</h2>
                </div>
                @if ($nextInstallment)
                    <x-status-badge :status="$nextInstallment->status_pembayaran" />
                @endif
            </div>

            @if ($nextInstallment)
                <div class="mt-6 app-list-card-muted">
                    <p class="text-sm text-slate-500">Angsuran ke-{{ $nextInstallment->angsuran_ke }}</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">Rp {{ number_format($nextInstallment->total_tagihan, 0, ',', '.') }}</p>
                    <p class="mt-3 text-sm text-slate-600">Jatuh tempo {{ $nextInstallment->tanggal_jatuh_tempo->translatedFormat('d F Y') }}</p>
                </div>
                <div class="mt-5 flex gap-3">
                    <a href="{{ route('user.payments.create', ['installment' => $nextInstallment->id]) }}" class="btn-primary flex-1">Bayar Sekarang</a>
                    <a href="{{ route('user.installments.show', $nextInstallment) }}" class="btn-secondary flex-1">Detail</a>
                </div>
            @else
                <p class="mt-6 text-sm leading-7 text-slate-600">Tidak ada angsuran aktif saat ini. Jika kontrak sudah berjalan, tagihan baru akan muncul otomatis sesuai jadwal.</p>
            @endif
        </div>

        <div class="app-panel">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="app-kicker">Pengiriman Motor</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-950">Status unit terbaru</h2>
                </div>
                <x-status-badge :status="$activeDelivery?->status_kirim ?? 'menunggu_pengiriman'" />
            </div>

            @if ($activeDelivery)
                <div class="mt-6 app-list-card-muted">
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-500">{{ $activeDelivery->invoice }}</p>
                    <h3 class="mt-3 text-xl font-semibold text-slate-950">{{ $activeDelivery->application->motor->nama_motor }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $activeDelivery->nama_kurir ?: 'Kurir belum ditentukan' }} @if($activeDelivery->telpon_kurir) &middot; {{ $activeDelivery->telpon_kurir }} @endif</p>
                    <p class="mt-3 text-sm text-slate-600">Estimasi tiba {{ optional($activeDelivery->tgl_tiba)->translatedFormat('d F Y') ?? 'menunggu jadwal' }}</p>
                </div>
                <a href="{{ route('user.deliveries.show', $activeDelivery) }}" class="btn-secondary mt-5 w-full">Lacak Pengiriman</a>
            @else
                <p class="mt-6 text-sm leading-7 text-slate-600">Belum ada pengiriman aktif. Informasi kurir dan jadwal antar akan muncul otomatis setelah kontrak masuk tahap pengiriman.</p>
            @endif
        </div>

        <div class="app-panel">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="app-kicker">Notifikasi</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-950">Update akun terbaru</h2>
                </div>
                <span class="rounded-full bg-[#fff3e9] px-3 py-1 text-xs font-semibold text-[#ff6425]">{{ $dashboard['unread_notifications'] }} unread</span>
            </div>
            <div class="mt-6 grid gap-3">
                @forelse ($dashboard['notifications'] as $notification)
                    <div class="app-list-card-muted">
                        <p class="font-semibold text-slate-950">{{ $notification->title }}</p>
                        <p class="mt-2 text-sm leading-7 text-slate-600">{{ $notification->message }}</p>
                    </div>
                @empty
                    <p class="text-sm leading-7 text-slate-600">Belum ada notifikasi baru.</p>
                @endforelse
            </div>
            <a href="{{ route('user.notifications.index') }}" class="btn-secondary mt-5 w-full">Buka Pusat Notifikasi</a>
        </div>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <div class="app-panel">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="app-kicker">Pengajuan Terbaru</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-950">Progress pembiayaan motor Anda</h2>
                </div>
                <a href="{{ route('user.applications.index') }}" class="btn-secondary">Lihat Semua</a>
            </div>

            <div class="mt-6 grid gap-4">
                @forelse ($dashboard['applications'] as $application)
                    <article class="app-list-card">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-[0.24em] text-slate-500">{{ $application->kode_pengajuan }}</p>
                                <h3 class="mt-3 text-xl font-semibold text-slate-950">{{ $application->motor->nama_motor }}</h3>
                                <p class="mt-2 text-sm text-slate-600">{{ $application->jenisCicilan->nama_cicilan ?? 'Skema pembiayaan' }} &middot; DP Rp {{ number_format($application->dp, 0, ',', '.') }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <x-status-badge :status="$application->status_pengajuan" />
                                <a href="{{ route('user.applications.show', $application) }}" class="btn-ghost">Detail</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <x-empty-state
                        title="Belum ada pengajuan"
                        description="Ajukan motor pertama Anda untuk mulai memantau proses verifikasi dan kontrak pembiayaan."
                        action-label="Ajukan Kredit"
                        action-href="{{ route('user.applications.create') }}"
                    />
                @endforelse
            </div>
        </div>

        <div class="app-panel">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="app-kicker">Pembayaran Terakhir</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-950">Upload dan verifikasi transfer</h2>
                </div>
                <a href="{{ route('user.payments.index') }}" class="btn-secondary">Lihat Semua</a>
            </div>

            <div class="mt-6 grid gap-4">
                @forelse ($dashboard['recent_payments'] as $payment)
                    <article class="app-list-card">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-[0.24em] text-slate-500">{{ $payment->kode_pembayaran }}</p>
                                <h3 class="mt-3 text-xl font-semibold text-slate-950">{{ $payment->installment->application->motor->nama_motor }}</h3>
                                <p class="mt-2 text-sm text-slate-600">Angsuran ke-{{ $payment->installment->angsuran_ke }} &middot; Rp {{ number_format($payment->nominal_bayar, 0, ',', '.') }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <x-status-badge :status="$payment->status_verifikasi" />
                                <a href="{{ route('user.payments.show', $payment) }}" class="btn-ghost">Detail</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <x-empty-state
                        title="Belum ada pembayaran"
                        description="Setelah Anda mengunggah bukti transfer, riwayat pembayaran akan muncul di sini."
                        action-label="Upload Pembayaran"
                        action-href="{{ route('user.payments.create') }}"
                    />
                @endforelse
            </div>
        </div>
    </section>
@endsection

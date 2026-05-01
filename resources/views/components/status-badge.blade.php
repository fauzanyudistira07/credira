@php
    $palette = match ($status ?? '') {
        'disetujui', 'kontrak_aktif', 'sudah_bayar', 'valid', 'sampai_tujuan', 'utama', 'tersedia', 'aktif', 'approved' => 'border-emerald-200/90 bg-emerald-50 text-emerald-700',
        'diproses', 'survey', 'dikirim', 'review' => 'border-sky-200/90 bg-sky-50 text-sky-700',
        'menunggu_verifikasi', 'pending', 'verifikasi_dokumen', 'menunggu_konfirmasi', 'belum_bayar', 'draft', 'menunggu_pengiriman', 'disiapkan' => 'border-amber-200/90 bg-amber-50 text-amber-700',
        'ditolak', 'dibatalkan_user', 'dibatalkan_admin', 'gagal_verifikasi', 'gagal_kirim', 'telat', 'habis', 'rejected' => 'border-rose-200/90 bg-rose-50 text-rose-700',
        'admin' => 'border-slate-300 bg-slate-900 text-slate-50',
        'marketing', 'featured' => 'border-orange-200/90 bg-orange-50 text-orange-700',
        'ceo' => 'border-violet-200/90 bg-violet-50 text-violet-700',
        'nonaktif' => 'border-slate-200 bg-slate-100 text-slate-600',
        default => 'bg-slate-100 text-slate-700 border-slate-200',
    };

    $label = match ($status ?? '') {
        'ceo' => 'CEO',
        'admin' => 'Admin',
        'marketing' => 'Marketing',
        'featured' => 'Featured',
        'nonaktif' => 'Nonaktif',
        default => str($status ?? '-')->replace('_', ' ')->title()->toString(),
    };

    $tone = match ($status ?? '') {
        'disetujui', 'kontrak_aktif', 'sudah_bayar', 'valid', 'sampai_tujuan', 'utama', 'tersedia', 'aktif', 'approved' => 'success',
        'diproses', 'survey', 'dikirim', 'review' => 'info',
        'menunggu_verifikasi', 'pending', 'verifikasi_dokumen', 'menunggu_konfirmasi', 'belum_bayar', 'draft', 'menunggu_pengiriman', 'disiapkan' => 'warning',
        'ditolak', 'dibatalkan_user', 'dibatalkan_admin', 'gagal_verifikasi', 'gagal_kirim', 'telat', 'habis', 'rejected' => 'danger',
        'marketing', 'featured' => 'accent',
        'ceo' => 'violet',
        default => 'neutral',
    };
@endphp

<span {{ $attributes->merge(['class' => 'status-badge inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold tracking-[0.02em] '.$palette, 'data-status-tone' => $tone]) }}>
    {{ $label }}
</span>

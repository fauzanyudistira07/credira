<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 | Credira</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-shell-body">
    <main class="shell flex min-h-screen items-center justify-center py-12">
        <section class="surface max-w-2xl p-8 text-center sm:p-10">
            <span class="section-kicker">404 Page Missing</span>
            <h1 class="mt-6 text-4xl font-semibold tracking-[-0.04em] text-slate-950 sm:text-5xl">Halaman yang Anda cari tidak ditemukan.</h1>
            <p class="mx-auto mt-4 max-w-xl text-sm leading-7 text-slate-500 sm:text-base">Link mungkin sudah berubah, data belum tersedia, atau URL tidak lagi aktif. Gunakan navigasi utama Credira untuk kembali ke alur yang benar.</p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <a href="{{ route('dashboard') }}" class="btn-accent">Dashboard</a>
                <a href="{{ route('home') }}" class="btn-secondary">Beranda</a>
            </div>
        </section>
    </main>
</body>
</html>

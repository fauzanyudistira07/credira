<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>419 | Credira</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-shell-body">
    <main class="shell flex min-h-screen items-center justify-center py-12">
        <section class="surface max-w-2xl p-8 text-center sm:p-10">
            <span class="section-kicker">419 Session Expired</span>
            <h1 class="mt-6 text-4xl font-semibold tracking-[-0.04em] text-slate-950 sm:text-5xl">Sesi Anda sudah berakhir.</h1>
            <p class="mx-auto mt-4 max-w-xl text-sm leading-7 text-slate-500 sm:text-base">Untuk menjaga keamanan workflow Credira, sesi yang terlalu lama akan direset. Muat ulang halaman atau masuk kembali sebelum mengirim data lagi.</p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <a href="{{ url()->previous() }}" class="btn-secondary">Kembali</a>
                <a href="{{ route('login') }}" class="btn-accent">Masuk Lagi</a>
            </div>
        </section>
    </main>
</body>
</html>

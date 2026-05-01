<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 | Credira</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-shell-body">
    <main class="shell flex min-h-screen items-center justify-center py-12">
        <section class="surface max-w-2xl p-8 text-center sm:p-10">
            <span class="section-kicker">403 Access Restricted</span>
            <h1 class="mt-6 text-4xl font-semibold tracking-[-0.04em] text-slate-950 sm:text-5xl">Akses ke area ini tidak tersedia untuk akun Anda.</h1>
            <p class="mx-auto mt-4 max-w-xl text-sm leading-7 text-slate-500 sm:text-base">Credira menjaga batas akses per role agar data operasional, marketing, dan executive tetap aman. Kembali ke workspace yang sesuai untuk melanjutkan.</p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <a href="{{ route('dashboard') }}" class="btn-accent">Kembali ke Dashboard</a>
                <a href="{{ route('home') }}" class="btn-secondary">Buka Landing Page</a>
            </div>
        </section>
    </main>
</body>
</html>

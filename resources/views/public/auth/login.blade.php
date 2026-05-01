@extends('layouts.auth', ['title' => 'Masuk Credira'])

@section('content')
    <div class="auth-panel">
        <div class="auth-panel__topline">
            <p class="auth-panel__helper">Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
        </div>

        <h1 class="auth-panel__title">Masuk ke workspace Credira.</h1>
        <p class="auth-panel__copy">Gunakan email dan password Anda untuk masuk ke area admin, marketing, CEO, atau akun nasabah sesuai role akun.</p>

        <form method="POST" action="{{ route('login.store') }}" class="auth-panel__form">
            @csrf

            <div>
                <label class="field-label" for="email">Email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="nama@credira.com"
                    class="field-input h-14 rounded-xl border-gray-200 placeholder:text-slate-400 @error('email') border-rose-300 bg-rose-50/60 ring-2 ring-rose-100 @enderror"
                    aria-invalid="@error('email') true @else false @enderror"
                >
                @error('email')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="field-label" for="password">Password</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="Masukkan password"
                    class="field-input h-14 rounded-xl border-gray-200 placeholder:text-slate-400 @error('password') border-rose-300 bg-rose-50/60 ring-2 ring-rose-100 @enderror"
                    aria-invalid="@error('password') true @else false @enderror"
                >
                @error('password')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <label class="auth-check">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                <span>Ingat saya pada perangkat ini</span>
            </label>

            <button type="submit" class="btn-accent auth-submit" data-loading-text="Memproses login...">
                Masuk ke Akun
            </button>
        </form>

        <div class="auth-panel__footer">
            <a href="{{ route('password.request') }}">Lupa password?</a>
            <a href="{{ route('home') }}">Kembali ke beranda</a>
        </div>
    </div>
@endsection

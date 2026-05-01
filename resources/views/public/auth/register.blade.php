@extends('layouts.auth', ['title' => 'Daftar Credira'])

@section('content')
    <div class="auth-panel">
        <div class="auth-panel__topline">
            <p class="auth-panel__helper">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
        </div>

        <h1 class="auth-panel__title">Buat akun Credira baru.</h1>
        <p class="auth-panel__copy">Lengkapi data dasar untuk membuka akses ke workspace Credira dengan alur yang rapi, cepat, dan aman.</p>

        <form method="POST" action="{{ route('register.store') }}" class="auth-panel__form md:grid-cols-2">
            @csrf

            <div class="md:col-span-2">
                <label class="field-label" for="name">Nama lengkap</label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Masukkan nama lengkap"
                    class="field-input h-14 rounded-xl border-gray-200 placeholder:text-slate-400 @error('name') border-rose-300 bg-rose-50/60 ring-2 ring-rose-100 @enderror"
                    aria-invalid="@error('name') true @else false @enderror"
                >
                @error('name')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="field-label" for="email">Email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="nama@email.com"
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
                    placeholder="Buat password"
                    class="field-input h-14 rounded-xl border-gray-200 placeholder:text-slate-400 @error('password') border-rose-300 bg-rose-50/60 ring-2 ring-rose-100 @enderror"
                    aria-invalid="@error('password') true @else false @enderror"
                >
                @error('password')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="field-label" for="password_confirmation">Konfirmasi password</label>
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    placeholder="Ulangi password"
                    class="field-input h-14 rounded-xl border-gray-200 placeholder:text-slate-400"
                >
            </div>

            <label class="auth-check md:col-span-2">
                <input type="checkbox" name="agree" value="1" @checked(old('agree'))>
                <span>Saya menyetujui syarat dan ketentuan penggunaan layanan Credira.</span>
            </label>
            @error('agree')
                <p class="field-error md:col-span-2 -mt-2">{{ $message }}</p>
            @enderror

            <div class="md:col-span-2">
                <button type="submit" class="btn-accent auth-submit" data-loading-text="Membuat akun...">
                    Buat Akun
                </button>
            </div>
        </form>

        <div class="auth-panel__footer">
            <a href="{{ route('home') }}">Kembali ke beranda</a>
            <a href="{{ route('login') }}">Masuk ke akun</a>
        </div>
    </div>
@endsection

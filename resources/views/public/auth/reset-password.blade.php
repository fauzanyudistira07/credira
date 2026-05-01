@extends('layouts.public', ['title' => 'Atur Ulang Password'])

@section('content')
    <section class="shell py-16">
        <div class="auth-shell">
            <div class="auth-card max-w-xl">
                <span class="section-kicker section-kicker-dark">Atur ulang password</span>
                <h1 class="mt-4 text-3xl font-semibold text-white">Atur password baru untuk akun Credira Anda.</h1>
                <form method="POST" action="{{ route('password.update') }}" class="mt-8 grid gap-5">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div>
                        <label class="field-label">Email</label>
                        <input type="email" name="email" class="field-input" value="{{ old('email', $email) }}">
                    </div>
                    <div>
                        <label class="field-label">Password baru</label>
                        <input type="password" name="password" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Konfirmasi password</label>
                        <input type="password" name="password_confirmation" class="field-input">
                    </div>
                    <button type="submit" class="btn-accent">Simpan password baru</button>
                </form>
            </div>
        </div>
    </section>
@endsection

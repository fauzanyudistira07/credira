@extends('layouts.public', ['title' => 'Lupa Password'])

@section('content')
    <section class="shell py-16">
        <div class="auth-shell">
            <div class="auth-card max-w-xl">
                <span class="section-kicker section-kicker-dark">Lupa password</span>
                <h1 class="mt-4 text-3xl font-semibold text-white">Masukkan email akun untuk menerima tautan pengaturan ulang password.</h1>
                <form method="POST" action="{{ route('password.email') }}" class="mt-8 grid gap-5">
                    @csrf
                    <div>
                        <label class="field-label">Email</label>
                        <input type="email" name="email" class="field-input" value="{{ old('email') }}">
                    </div>
                    <button type="submit" class="btn-accent">Kirim tautan reset</button>
                </form>
            </div>
        </div>
    </section>
@endsection

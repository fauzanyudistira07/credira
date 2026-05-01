@extends('layouts.dashboard', [
    'title' => 'Profil',
    'role' => auth()->user()->role,
    'pageTitle' => 'Profil Akun',
    'pageDescription' => 'Informasi dasar user yang sedang login.',
])

@php
    $role = auth()->user()->role;
    $panelClass = match ($role) {
        'marketing' => 'marketing-surface',
        'ceo' => 'ceo-panel',
        default => 'dashboard-panel dashboard-panel--profile',
    };
    $infoCardClass = match ($role) {
        'marketing', 'ceo' => 'dashboard-info-card dashboard-info-card--dark',
        default => 'dashboard-info-card',
    };
@endphp

@section('content')
    <section class="{{ $panelClass }} max-w-3xl">
        <div class="dashboard-panel__head">
            <div>
                <p class="dashboard-kicker">User Info</p>
                <h3 class="dashboard-panel__title">Informasi akun</h3>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="{{ $infoCardClass }}">
                <p class="dashboard-stat-label">Nama</p>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ auth()->user()->name }}</p>
            </div>
            <div class="{{ $infoCardClass }}">
                <p class="dashboard-stat-label">Email</p>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ auth()->user()->email }}</p>
            </div>
            <div class="{{ $infoCardClass }}">
                <p class="dashboard-stat-label">Role</p>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ strtoupper(auth()->user()->role) }}</p>
            </div>
            <div class="{{ $infoCardClass }}">
                <p class="dashboard-stat-label">Bergabung</p>
                <p class="mt-2 text-lg font-semibold text-slate-900">{{ optional(auth()->user()->created_at)->translatedFormat('d F Y') ?? '-' }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-6">
            @csrf
            <button type="submit" class="dashboard-logout-button">Logout</button>
        </form>
    </section>
@endsection

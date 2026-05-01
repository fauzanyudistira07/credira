@extends('layouts.dashboard', [
    'title' => $title,
    'role' => $role,
    'pageTitle' => $title,
    'pageDescription' => $description,
])

@section('content')
    @if (($role ?? null) === 'marketing')
        <div class="marketing-page">
            <section class="marketing-hero">
                <p class="marketing-hero__eyebrow">Marketing Workspace</p>
                <h2 class="marketing-hero__title">{{ $title }}</h2>
                <p class="marketing-hero__copy">{{ $description }}</p>
            </section>

            <section class="marketing-surface max-w-5xl">
                <div class="dashboard-panel__head">
                    <div>
                        <p class="dashboard-kicker">Coming Soon</p>
                        <h3 class="marketing-section-title">Simulasi kredit sedang disiapkan</h3>
                    </div>
                </div>

                <div class="marketing-inline-note">
                    <p class="text-base font-semibold text-slate-950">{{ $page }}</p>
                    <p class="mt-3 text-sm leading-7 text-slate-600">{{ $description }}</p>
                    <p class="mt-4 text-sm leading-7 text-slate-600">Halaman ini belum berisi kalkulasi interaktif. Struktur visualnya sudah disamakan dengan workspace marketing agar warna teks dan surface tetap konsisten.</p>
                </div>
            </section>
        </div>
    @else
        <section class="dashboard-panel max-w-4xl">
            <div class="dashboard-panel__head">
                <div>
                    <p class="dashboard-kicker">Placeholder</p>
                    <h3 class="dashboard-panel__title">{{ $title }}</h3>
                </div>
            </div>

            <div class="dashboard-empty-state">
                <p class="text-lg font-semibold text-slate-900">{{ $page }}</p>
                <p class="mt-3 text-sm leading-7 text-slate-500">{{ $description }}</p>
            </div>
        </section>
    @endif
@endsection

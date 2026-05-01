@extends('layouts.dashboard', [
    'title' => 'Dashboard CEO',
    'role' => 'ceo',
    'pageTitle' => 'Dashboard CEO',
    'pageDescription' => 'Ringkasan eksekutif untuk memantau volume pengajuan dan performa marketing.',
])

@section('content')
    <div class="dashboard-grid">
        <section class="dashboard-hero-card">
            <div>
                <p class="dashboard-kicker">Executive Summary</p>
                <h2 class="dashboard-hero-title">Lihat performa bisnis Credira dengan cepat tanpa template admin generik.</h2>
                <p class="dashboard-hero-copy">Dashboard CEO menonjolkan volume pengajuan, approval, rejection, dan distribusi performa marketing dalam panel yang ringan.</p>
            </div>
            <div class="dashboard-hero-actions">
                <a href="{{ route('ceo.laporan.index') }}" class="btn-accent">Buka Laporan</a>
                <a href="{{ route('ceo.marketing.index') }}" class="btn-secondary">Performa Marketing</a>
            </div>
        </section>

        <section class="dashboard-stats-grid">
            <article class="dashboard-stat-card">
                <p class="dashboard-stat-label">Total Pengajuan</p>
                <p class="dashboard-stat-value">{{ number_format($stats['total_pengajuan']) }}</p>
            </article>
            <article class="dashboard-stat-card">
                <p class="dashboard-stat-label">Approved</p>
                <p class="dashboard-stat-value">{{ number_format($stats['approved_pengajuan']) }}</p>
            </article>
            <article class="dashboard-stat-card">
                <p class="dashboard-stat-label">Rejected</p>
                <p class="dashboard-stat-value">{{ number_format($stats['rejected_pengajuan']) }}</p>
            </article>
            <article class="dashboard-stat-card">
                <p class="dashboard-stat-label">Total Marketing</p>
                <p class="dashboard-stat-value">{{ number_format($stats['total_marketing']) }}</p>
            </article>
        </section>

        <section class="dashboard-panel">
            <div class="dashboard-panel__head">
                <div>
                    <p class="dashboard-kicker">Performa</p>
                    <h3 class="dashboard-panel__title">Top marketing</h3>
                </div>
            </div>
            <div class="space-y-3">
                @forelse ($performance as $item)
                    <div class="dashboard-list-row">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $item->name }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ number_format($item->total_pengajuan) }} pengajuan • {{ number_format($item->approved_pengajuan) }} approved</p>
                        </div>
                        <span class="dashboard-badge">Topline</span>
                    </div>
                @empty
                    <div class="dashboard-empty-state">
                        Belum ada data performa marketing yang bisa dirangkum.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="dashboard-panel">
            <div class="dashboard-panel__head">
                <div>
                    <p class="dashboard-kicker">Laporan</p>
                    <h3 class="dashboard-panel__title">Area chart placeholder</h3>
                </div>
            </div>
            <div class="dashboard-chart-placeholder">
                <div class="dashboard-chart-bars">
                    <span style="height: 44%"></span>
                    <span style="height: 62%"></span>
                    <span style="height: 38%"></span>
                    <span style="height: 79%"></span>
                    <span style="height: 56%"></span>
                    <span style="height: 84%"></span>
                </div>
                <p class="mt-5 text-sm leading-7 text-slate-500">Panel ini disiapkan untuk visual laporan dan chart pada tahap berikutnya tanpa menambah kompleksitas bisnis di tahap auth.</p>
            </div>
        </section>
    </div>
@endsection

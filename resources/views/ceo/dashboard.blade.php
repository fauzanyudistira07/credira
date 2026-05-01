@extends('layouts.dashboard', [
    'title' => 'Dashboard CEO',
    'role' => 'ceo',
    'pageTitle' => 'Executive Dashboard',
    'pageDescription' => 'Ringkasan bisnis Credira untuk menangkap kondisi pipeline, performa tim, dan momentum pertumbuhan dalam beberapa detik.',
])

@php
    $formatMetric = function (array $item) {
        if (! empty($item['currency'])) {
            return 'Rp '.number_format((int) $item['value'], 0, ',', '.');
        }

        if (! empty($item['suffix'])) {
            return number_format((float) $item['value'], 1, ',', '.').$item['suffix'];
        }

        return number_format((int) $item['value'], 0, ',', '.');
    };

    $maxStatus = max(1, collect($statusDistribution)->max('value'));
@endphp

@section('content')
    <div class="ceo-page">
        <section class="ceo-hero" data-reveal>
            <div class="ceo-hero__main">
                <span class="ceo-chip">CEO Workspace</span>
                <h2 class="ceo-hero__title">Kondisi bisnis Credira terlihat cepat, ringkas, dan tetap detail saat dibutuhkan.</h2>
                <p class="ceo-hero__copy">Fokus utama area ini adalah monitoring volume pengajuan, kualitas approval, performa marketing, popularitas produk, dan aktivitas terbaru tanpa membuka area operasional.</p>
            </div>
            <div class="ceo-hero__aside">
                <div class="ceo-hero-stat">
                    <p class="ceo-hero-stat__label">Periode aktif</p>
                    <p class="ceo-hero-stat__value">{{ $periodLabel }}</p>
                </div>
                <div class="ceo-hero-stat">
                    <p class="ceo-hero-stat__label">Approval rate</p>
                    <p class="ceo-hero-stat__value">{{ number_format((float) collect($kpis)->firstWhere('label', 'Approval Rate')['value'], 1, ',', '.') }}%</p>
                </div>
                <div class="ceo-hero__actions">
                    <a href="{{ route('ceo.reports.index') }}" class="btn-accent">Buka Laporan</a>
                    <a href="{{ route('ceo.marketing.index') }}" class="btn-outline-light">Lihat Marketing</a>
                </div>
            </div>
        </section>

        <section class="ceo-kpi-grid" data-reveal>
            @foreach ($kpis as $item)
                <article class="ceo-kpi-card ceo-kpi-card--{{ $item['tone'] }}">
                    <p class="ceo-kpi-card__label">{{ $item['label'] }}</p>
                    <p class="ceo-kpi-card__value">{{ $formatMetric($item) }}</p>
                    <p class="ceo-kpi-card__copy">{{ $item['caption'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="ceo-grid ceo-grid--split" data-reveal>
            <article class="ceo-panel">
                <div class="ceo-panel__head">
                    <div>
                        <span class="ceo-chip ceo-chip--soft">Growth</span>
                        <h3 class="ceo-panel__title">Trend bulan berjalan</h3>
                    </div>
                </div>
                <div class="ceo-trend-grid">
                    @foreach ($trends as $trend)
                        <div class="ceo-trend-card">
                            <p class="ceo-trend-card__label">{{ $trend['label'] }}</p>
                            <p class="ceo-trend-card__value">{{ is_float($trend['value']) ? number_format($trend['value'], 1, ',', '.') : number_format($trend['value'], 0, ',', '.') }}</p>
                            <p class="ceo-trend-card__delta ceo-trend-card__delta--{{ $trend['comparison']['direction'] }}">
                                {{ $trend['comparison']['label'] }} vs periode sebelumnya
                            </p>
                        </div>
                    @endforeach
                </div>

                <div class="ceo-chart mt-6">
                    <div class="ceo-chart__head">
                        <div>
                            <p class="ceo-panel__eyebrow">Line snapshot</p>
                            <p class="ceo-chart__title">Pengajuan per bulan</p>
                        </div>
                    </div>
                    <div class="ceo-line-chart">
                        @foreach ($monthlyTrend as $point)
                            <div class="ceo-line-chart__item">
                                <div class="ceo-line-chart__spark">
                                    <span style="height: {{ $point['height'] }}%"></span>
                                </div>
                                <p class="ceo-line-chart__value">{{ number_format($point['value']) }}</p>
                                <p class="ceo-line-chart__label">{{ $point['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </article>

            <article class="ceo-panel">
                <div class="ceo-panel__head">
                    <div>
                        <span class="ceo-chip ceo-chip--soft">Status</span>
                        <h3 class="ceo-panel__title">Distribusi status pengajuan</h3>
                    </div>
                </div>

                <div class="space-y-4">
                    @foreach ($statusDistribution as $status)
                        <div class="ceo-progress">
                            <div class="ceo-progress__meta">
                                <span>{{ $status['label'] }}</span>
                                <strong>{{ number_format($status['value']) }}</strong>
                            </div>
                            <div class="ceo-progress__track">
                                <span class="ceo-progress__bar ceo-progress__bar--{{ $status['tone'] }}" style="width: {{ max(8, round(($status['value'] / $maxStatus) * 100)) }}%"></span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="ceo-inline-panel mt-6">
                    <p class="ceo-inline-panel__label">Tim marketing teratas bulan ini</p>
                    @forelse ($topMarketing as $item)
                        <div class="ceo-inline-row">
                            <div>
                                <p class="font-semibold text-slate-950">{{ $item->name }}</p>
                                <p class="text-sm text-slate-500">{{ number_format($item->approved_count) }} approved dari {{ number_format($item->total_pengajuan) }} pengajuan</p>
                            </div>
                            <span class="ceo-rank-badge">Topline</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada data marketing pada periode ini.</p>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="ceo-grid ceo-grid--split" data-reveal>
            <article class="ceo-panel">
                <div class="ceo-panel__head">
                    <div>
                        <span class="ceo-chip ceo-chip--soft">Quick Insight</span>
                        <h3 class="ceo-panel__title">Executive notes</h3>
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach ($insights as $insight)
                        <article class="ceo-insight ceo-insight--{{ $insight['tone'] }}">
                            <h4 class="ceo-insight__title">{{ $insight['title'] }}</h4>
                            <p class="ceo-insight__copy">{{ $insight['description'] }}</p>
                        </article>
                    @endforeach
                </div>
            </article>

            <article class="ceo-panel">
                <div class="ceo-panel__head">
                    <div>
                        <span class="ceo-chip ceo-chip--soft">Recent Activity</span>
                        <h3 class="ceo-panel__title">Aktivitas terbaru</h3>
                    </div>
                </div>

                <div class="ceo-activity-list">
                    @forelse ($recentActivity as $activity)
                        <article class="ceo-activity ceo-activity--{{ $activity['tone'] }}">
                            <div class="ceo-activity__meta">
                                <span>{{ $activity['type'] }}</span>
                                <time>{{ $activity['time']?->diffForHumans() }}</time>
                            </div>
                            <h4 class="ceo-activity__title">{{ $activity['title'] }}</h4>
                            <p class="ceo-activity__copy">{{ $activity['description'] }}</p>
                        </article>
                    @empty
                        <div class="dashboard-empty-state">
                            Aktivitas terbaru belum tersedia.
                        </div>
                    @endforelse
                </div>
            </article>
        </section>
    </div>
@endsection

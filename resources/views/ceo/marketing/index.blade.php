@extends('layouts.dashboard', [
    'title' => 'Performa Marketing',
    'role' => 'ceo',
    'pageTitle' => 'Performa Marketing',
    'pageDescription' => 'Ranking performa marketing untuk membaca produktivitas, kualitas approval, dan beban pending tiap owner.',
])

@section('content')
    <div class="ceo-page">
        <section class="ceo-panel ceo-spotlight" data-reveal>
            <div>
                <span class="ceo-chip">Periode {{ $periodLabel }}</span>
                <h2 class="ceo-spotlight__title">Tim marketing yang paling banyak menghasilkan pipeline berkualitas.</h2>
                <p class="ceo-spotlight__copy">CEO hanya melihat performa. Tidak ada aksi operasional atau approval dari halaman ini.</p>
            </div>
            <form method="GET" class="ceo-period-form">
                <div>
                    <label class="field-label">Bulan</label>
                    <input type="month" name="period" class="field-input" value="{{ $filters['period'] ?? now()->format('Y-m') }}">
                </div>
                <a href="{{ route('ceo.marketing.index', array_filter(array_merge($filters, ['export' => 'csv']))) }}" class="btn-secondary">Export CSV</a>
                <button type="submit" class="btn-accent">Terapkan</button>
            </form>
        </section>

        <section class="ceo-summary-grid" data-reveal>
            <article class="ceo-summary-card"><p class="ceo-summary-card__label">Jumlah marketing</p><p class="ceo-summary-card__value">{{ number_format($summary['marketing_count']) }}</p></article>
            <article class="ceo-summary-card"><p class="ceo-summary-card__label">Total pengajuan</p><p class="ceo-summary-card__value">{{ number_format($summary['applications']) }}</p></article>
            <article class="ceo-summary-card ceo-summary-card--success"><p class="ceo-summary-card__label">Total approved</p><p class="ceo-summary-card__value">{{ number_format($summary['approved']) }}</p></article>
            <article class="ceo-summary-card"><p class="ceo-summary-card__label">Rata approval rate</p><p class="ceo-summary-card__value">{{ number_format((float) $summary['average_rate'], 1, ',', '.') }}%</p></article>
            <article class="ceo-summary-card"><p class="ceo-summary-card__label">Nilai pipeline</p><p class="ceo-summary-card__value">Rp {{ number_format((int) $summary['total_value'], 0, ',', '.') }}</p></article>
            <article class="ceo-summary-card ceo-summary-card--success"><p class="ceo-summary-card__label">Nilai approved</p><p class="ceo-summary-card__value">Rp {{ number_format((int) $summary['approved_value'], 0, ',', '.') }}</p></article>
        </section>

        <section class="ceo-grid ceo-grid--split" data-reveal>
            <article class="ceo-panel">
                <div class="ceo-panel__head">
                    <div>
                        <span class="ceo-chip ceo-chip--soft">Top Performer</span>
                        <h3 class="ceo-panel__title">{{ $topPerformer?->name ?? 'Belum ada data' }}</h3>
                    </div>
                </div>
                @if ($topPerformer)
                    <div class="ceo-feature-list">
                        <div class="ceo-feature-list__item"><span>Pelanggan</span><strong>{{ number_format($topPerformer->total_pelanggan) }}</strong></div>
                        <div class="ceo-feature-list__item"><span>Pengajuan</span><strong>{{ number_format($topPerformer->total_pengajuan) }}</strong></div>
                        <div class="ceo-feature-list__item"><span>Approved</span><strong>{{ number_format($topPerformer->approved_count) }}</strong></div>
                        <div class="ceo-feature-list__item"><span>Approval rate</span><strong>{{ number_format((float) $topPerformer->approval_rate, 1, ',', '.') }}%</strong></div>
                        <div class="ceo-feature-list__item"><span>Nilai pipeline</span><strong>Rp {{ number_format((int) $topPerformer->total_value, 0, ',', '.') }}</strong></div>
                    </div>
                @else
                    <div class="dashboard-empty-state">Belum ada data performa pada periode ini.</div>
                @endif
            </article>

            <article class="ceo-panel">
                <div class="ceo-panel__head">
                    <div>
                        <span class="ceo-chip ceo-chip--soft">Most Active</span>
                        <h3 class="ceo-panel__title">{{ $mostActive?->name ?? 'Belum ada data' }}</h3>
                    </div>
                </div>
                @if ($mostActive)
                    <div class="ceo-feature-list">
                        <div class="ceo-feature-list__item"><span>Total pengajuan</span><strong>{{ number_format($mostActive->total_pengajuan) }}</strong></div>
                        <div class="ceo-feature-list__item"><span>Pending</span><strong>{{ number_format($mostActive->pending_count) }}</strong></div>
                        <div class="ceo-feature-list__item"><span>Rejected</span><strong>{{ number_format($mostActive->rejected_count) }}</strong></div>
                        <div class="ceo-feature-list__item"><span>Review</span><strong>{{ number_format($mostActive->review_count) }}</strong></div>
                        <div class="ceo-feature-list__item"><span>Email</span><strong>{{ $mostActive->email }}</strong></div>
                    </div>
                @else
                    <div class="dashboard-empty-state">Belum ada data aktivitas marketing.</div>
                @endif
            </article>
        </section>

        <section class="ceo-panel" data-reveal>
            <div class="ceo-table-wrap">
                <table class="ceo-table">
                    <thead>
                        <tr>
                            <th>Ranking</th>
                            <th>Marketing</th>
                            <th>Pelanggan</th>
                            <th>Pengajuan</th>
                            <th>Approved</th>
                            <th>Rejected</th>
                            <th>Pending</th>
                            <th>Review</th>
                            <th>Approval Rate</th>
                            <th>Nilai Pipeline</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $index => $row)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <span class="ceo-rank-index">#{{ $index + 1 }}</span>
                                        @if ($index === 0)
                                            <span class="ceo-rank-badge">Top Performer</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <p class="font-semibold text-slate-950">{{ $row->name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $row->email }}</p>
                                </td>
                                <td>{{ number_format($row->total_pelanggan) }}</td>
                                <td>{{ number_format($row->total_pengajuan) }}</td>
                                <td>{{ number_format($row->approved_count) }}</td>
                                <td>{{ number_format($row->rejected_count) }}</td>
                                <td>{{ number_format($row->pending_count) }}</td>
                                <td>{{ number_format($row->review_count) }}</td>
                                <td>{{ number_format((float) $row->approval_rate, 1, ',', '.') }}%</td>
                                <td>Rp {{ number_format((int) $row->total_value, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10">
                                    <div class="dashboard-empty-state">Belum ada data performa marketing.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

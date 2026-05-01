@extends('layouts.dashboard', [
    'title' => 'Statistik Motor',
    'role' => 'ceo',
    'pageTitle' => 'Statistik Motor / Produk',
    'pageDescription' => 'Membaca unit paling diminati, paling sering approved, dan brand yang memimpin volume pembiayaan.',
])

@section('content')
    <div class="ceo-page">
        <section class="ceo-panel ceo-product-hero" data-reveal>
            <div class="ceo-product-hero__content">
                <span class="ceo-chip">Product Analytics</span>
                <h2 class="ceo-product-hero__title">Lihat motor dan brand yang paling kuat mendorong volume, approval, dan nilai pembiayaan.</h2>
                <p class="ceo-product-hero__copy">Semua insight produk diringkas agar CEO bisa langsung membaca unit unggulan, momentum brand, dan posisi performa katalog tanpa masuk ke area operasional.</p>

                <div class="ceo-product-hero__meta">
                    <span class="ceo-product-pill">{{ $rows->total() }} motor masuk analisis</span>
                    <span class="ceo-product-pill ceo-product-pill--soft">Periode {{ $periodLabel }}</span>
                </div>
            </div>

            <div class="ceo-product-hero__aside">
                <div class="ceo-product-hero__stat">
                    <p class="ceo-product-hero__stat-label">Motor aktif</p>
                    <p class="ceo-product-hero__stat-value">{{ number_format($summary['motor_active']) }}</p>
                </div>
                <div class="ceo-product-hero__stat">
                    <p class="ceo-product-hero__stat-label">Approved</p>
                    <p class="ceo-product-hero__stat-value">{{ number_format($summary['approved']) }}</p>
                </div>
                <a href="{{ route('ceo.products.index', array_filter(array_merge($filters, ['export' => 'csv']))) }}" class="btn-secondary w-full">Export CSV</a>
            </div>
        </section>

        <x-filter-toolbar
            class="ceo-panel"
            title="Filter Produk"
            description="Cari motor atau brand tertentu dan fokuskan analisis ke periode yang sedang dibahas."
            :active-filters="array_values(array_filter([
                filled($filters['period'] ?? null) ? 'Periode: '.($periodLabel ?? '') : null,
                filled($filters['q'] ?? null) ? 'Cari: '.($filters['q'] ?? '') : null,
            ]))"
            :reset-href="route('ceo.products.index')"
            data-reveal
        >
            <form method="GET" class="ceo-filter-grid ceo-filter-grid--compact">
                <div>
                    <label class="field-label">Cari motor / brand</label>
                    <input type="text" name="q" class="field-input" value="{{ $filters['q'] ?? '' }}" placeholder="Cari motor atau brand">
                </div>
                <div>
                    <label class="field-label">Bulan</label>
                    <input type="month" name="period" class="field-input" value="{{ $filters['period'] ?? now()->format('Y-m') }}">
                </div>
                <div class="ceo-filter-actions">
                    <button type="submit" class="btn-accent">Terapkan</button>
                    <a href="{{ route('ceo.products.index') }}" class="btn-secondary">Reset</a>
                </div>
            </form>
        </x-filter-toolbar>

        <section class="ceo-summary-grid ceo-summary-grid--balanced" data-reveal>
            <article class="ceo-summary-card">
                <p class="ceo-summary-card__label">Motor aktif</p>
                <p class="ceo-summary-card__value">{{ number_format($summary['motor_active']) }}</p>
                <p class="ceo-summary-card__caption">Jumlah unit aktif yang masih bisa dibaca di katalog.</p>
            </article>
            <article class="ceo-summary-card">
                <p class="ceo-summary-card__label">Pengajuan periode ini</p>
                <p class="ceo-summary-card__value">{{ number_format($summary['applications']) }}</p>
                <p class="ceo-summary-card__caption">Total permintaan pembiayaan yang masuk pada periode aktif.</p>
            </article>
            <article class="ceo-summary-card ceo-summary-card--success">
                <p class="ceo-summary-card__label">Approved</p>
                <p class="ceo-summary-card__value">{{ number_format($summary['approved']) }}</p>
                <p class="ceo-summary-card__caption">Pengajuan yang berhasil dikonversi ke approval.</p>
            </article>
            <article class="ceo-summary-card ceo-summary-card--danger">
                <p class="ceo-summary-card__label">Rejected</p>
                <p class="ceo-summary-card__value">{{ number_format($summary['rejected']) }}</p>
                <p class="ceo-summary-card__caption">Pengajuan yang ditolak atau dibatalkan.</p>
            </article>
            <article class="ceo-summary-card">
                <p class="ceo-summary-card__label">Nilai pembiayaan</p>
                <p class="ceo-summary-card__value">Rp {{ number_format((int) $summary['total_pembiayaan'], 0, ',', '.') }}</p>
                <p class="ceo-summary-card__caption">Akumulasi nilai pembiayaan dari seluruh hasil analisis.</p>
            </article>
        </section>

        <section class="ceo-panel" data-reveal>
            <div class="ceo-panel__head">
                <div>
                    <span class="ceo-chip ceo-chip--soft">Product Spotlight</span>
                    <h3 class="ceo-panel__title">Motor unggulan dan konversi terbaik</h3>
                </div>
                <p class="ceo-panel__eyebrow">Dua unit utama yang paling berpengaruh terhadap volume dan kualitas approval.</p>
            </div>

            <div class="ceo-product-spotlight-grid">
                <article class="ceo-product-card">
                    <div class="ceo-product-card__head">
                        <div>
                            <span class="ceo-chip ceo-chip--soft">Motor Paling Diminati</span>
                            <h3 class="ceo-product-card__title">{{ $topMotor?->nama_motor ?? 'Belum ada data' }}</h3>
                        </div>
                        <div class="ceo-product-card__badge">#1</div>
                    </div>

                    @if ($topMotor)
                        <div class="ceo-product-card__submeta">
                            <span>{{ $topMotor->merk }}</span>
                            <span>{{ $topMotor->jenis_motor ?? 'Jenis belum tersedia' }}</span>
                        </div>
                        <div class="ceo-product-card__stats">
                            <div class="ceo-product-card__stat">
                                <span class="ceo-product-card__stat-label">Pengajuan</span>
                                <strong>{{ number_format($topMotor->total_pengajuan) }}</strong>
                            </div>
                            <div class="ceo-product-card__stat">
                                <span class="ceo-product-card__stat-label">Approved</span>
                                <strong>{{ number_format($topMotor->approved_count) }}</strong>
                            </div>
                            <div class="ceo-product-card__stat">
                                <span class="ceo-product-card__stat-label">Rejected</span>
                                <strong>{{ number_format($topMotor->rejected_count) }}</strong>
                            </div>
                            <div class="ceo-product-card__stat">
                                <span class="ceo-product-card__stat-label">Harga jual</span>
                                <strong>Rp {{ number_format((int) $topMotor->harga_jual, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    @else
                        <div class="dashboard-empty-state">Belum ada motor yang menonjol pada periode ini.</div>
                    @endif
                </article>

                <article class="ceo-product-card">
                    <div class="ceo-product-card__head">
                        <div>
                            <span class="ceo-chip ceo-chip--soft">Motor Paling Sering Approved</span>
                            <h3 class="ceo-product-card__title">{{ $topApprovedMotor?->nama_motor ?? 'Belum ada data' }}</h3>
                        </div>
                        <div class="ceo-product-card__badge ceo-product-card__badge--success">Top</div>
                    </div>

                    @if ($topApprovedMotor)
                        <div class="ceo-product-card__submeta">
                            <span>{{ $topApprovedMotor->merk }}</span>
                            <span>{{ $topApprovedMotor->jenis_motor ?? 'Jenis belum tersedia' }}</span>
                        </div>
                        <div class="ceo-product-card__stats">
                            <div class="ceo-product-card__stat">
                                <span class="ceo-product-card__stat-label">Approved</span>
                                <strong>{{ number_format($topApprovedMotor->approved_count) }}</strong>
                            </div>
                            <div class="ceo-product-card__stat">
                                <span class="ceo-product-card__stat-label">Pengajuan</span>
                                <strong>{{ number_format($topApprovedMotor->total_pengajuan) }}</strong>
                            </div>
                            <div class="ceo-product-card__stat ceo-product-card__stat--wide">
                                <span class="ceo-product-card__stat-label">Total pembiayaan</span>
                                <strong>Rp {{ number_format((int) $topApprovedMotor->total_pembiayaan, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    @else
                        <div class="dashboard-empty-state">Belum ada data approval motor pada periode ini.</div>
                    @endif
                </article>
            </div>
        </section>

        <section class="ceo-grid ceo-grid--split" data-reveal>
            <article class="ceo-panel">
                <div class="ceo-panel__head">
                    <div>
                        <span class="ceo-chip ceo-chip--soft">Brand Paling Laku</span>
                        <h3 class="ceo-panel__title">Leaderboard brand {{ $periodLabel }}</h3>
                    </div>
                </div>
                <div class="space-y-3">
                    @forelse ($brandStats as $brand)
                        <div class="ceo-inline-row">
                            <div>
                                <p class="font-semibold text-slate-950">{{ $brand->merk }}</p>
                                <p class="text-sm text-slate-500">{{ number_format($brand->total_pengajuan) }} pengajuan</p>
                            </div>
                            <span class="ceo-rank-index">#{{ $loop->iteration }}</span>
                        </div>
                    @empty
                        <div class="dashboard-empty-state">Belum ada brand yang tercatat pada periode ini.</div>
                    @endforelse
                </div>
            </article>

            <article class="ceo-panel">
                <div class="ceo-panel__head">
                    <div>
                        <span class="ceo-chip ceo-chip--soft">Executive Note</span>
                        <h3 class="ceo-panel__title">Apa yang perlu diperhatikan</h3>
                    </div>
                </div>
                <div class="space-y-3">
                    <article class="ceo-insight ceo-insight--default">
                        <h4 class="ceo-insight__title">Produk unggulan periode ini</h4>
                        <p class="ceo-insight__copy">{{ $topMotor?->nama_motor ?? 'Belum ada data' }} memimpin minat pasar untuk {{ $periodLabel }}.</p>
                    </article>
                    <article class="ceo-insight ceo-insight--success">
                        <h4 class="ceo-insight__title">Konversi terbaik</h4>
                        <p class="ceo-insight__copy">{{ $topApprovedMotor?->nama_motor ?? 'Belum ada data' }} menjadi motor dengan jumlah approved tertinggi.</p>
                    </article>
                </div>
            </article>
        </section>

        <section class="ceo-panel" data-reveal>
            <div class="ceo-panel__head">
                <div>
                    <span class="ceo-chip ceo-chip--soft">Product Leaderboard</span>
                    <h3 class="ceo-panel__title">Daftar detail performa motor</h3>
                </div>
                <p class="ceo-panel__eyebrow">Urutan motor berdasarkan volume pengajuan, approval, dan total pembiayaan.</p>
            </div>

            <div class="ceo-table-wrap">
                <table class="ceo-table">
                    <thead>
                        <tr>
                            <th>Motor</th>
                            <th>Merk</th>
                            <th>Jenis</th>
                            <th>Pengajuan</th>
                            <th>Approved</th>
                            <th>Rejected</th>
                            <th>Total Pembiayaan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td class="font-semibold text-slate-950">{{ $row->nama_motor }}</td>
                                <td>{{ $row->merk }}</td>
                                <td>{{ $row->jenis_motor ?? '-' }}</td>
                                <td>{{ number_format($row->total_pengajuan) }}</td>
                                <td>{{ number_format($row->approved_count) }}</td>
                                <td>{{ number_format($row->rejected_count) }}</td>
                                <td>Rp {{ number_format((int) $row->total_pembiayaan, 0, ',', '.') }}</td>
                                <td><x-status-badge :status="$row->status_aktif ? 'tersedia' : 'nonaktif'" /></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="dashboard-empty-state">Belum ada data motor untuk ditampilkan.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5">
                <x-pagination :paginator="$rows" />
            </div>
        </section>
    </div>
@endsection

@extends('layouts.dashboard', [
    'title' => 'Monitoring Pelanggan',
    'role' => 'ceo',
    'pageTitle' => 'Monitoring Pelanggan',
    'pageDescription' => 'Membaca pertumbuhan nasabah, siapa yang paling aktif, dan hubungan pelanggan dengan owner marketing.',
])

@section('content')
    <div class="ceo-page">
        <section class="ceo-panel ceo-customer-hero" data-reveal>
            <div class="ceo-customer-hero__content">
                <span class="ceo-chip">Customer Monitoring</span>
                <h2 class="ceo-customer-hero__title">Peta pelanggan yang aktif, tumbuh, dan paling bernilai untuk evaluasi CEO.</h2>
                <p class="ceo-customer-hero__copy">Halaman ini merangkum pertumbuhan pelanggan, hubungan ke owner marketing, dan pola pengajuan terakhir dalam satu tempat yang lebih mudah dibaca.</p>

                <div class="ceo-customer-hero__meta">
                    <span class="ceo-customer-pill">{{ $rows->total() }} pelanggan ditemukan</span>
                    <span class="ceo-customer-pill ceo-customer-pill--soft">Periode {{ $periodLabel }}</span>
                </div>
            </div>

            <div class="ceo-customer-hero__aside">
                <div class="ceo-customer-hero__stat">
                    <p class="ceo-customer-hero__stat-label">Pelanggan aktif</p>
                    <p class="ceo-customer-hero__stat-value">{{ number_format($summary['active_customers']) }}</p>
                </div>
                <div class="ceo-customer-hero__stat">
                    <p class="ceo-customer-hero__stat-label">Repeat customer</p>
                    <p class="ceo-customer-hero__stat-value">{{ number_format($summary['repeat_customers']) }}</p>
                </div>
                <a href="{{ route('ceo.customers.index', array_filter(array_merge($filters, ['export' => 'csv']))) }}" class="btn-secondary w-full">Export CSV</a>
            </div>
        </section>

        <x-filter-toolbar
            class="ceo-panel"
            title="Filter Monitoring"
            description="Cari pelanggan tertentu, fokus ke owner marketing, atau sempitkan data ke periode tertentu."
            :active-filters="array_values(array_filter([
                filled($filters['period'] ?? null) ? 'Periode: '.($periodLabel ?? '') : null,
                filled($filters['q'] ?? null) ? 'Cari: '.($filters['q'] ?? '') : null,
                filled($filters['marketing_id'] ?? null) ? 'Marketing terpilih' : null,
            ]))"
            :reset-href="route('ceo.customers.index')"
            data-reveal
        >
            <form method="GET" class="ceo-filter-grid ceo-filter-grid--customer">
                <div>
                    <label class="field-label">Cari pelanggan</label>
                    <input type="text" name="q" class="field-input" value="{{ $filters['q'] ?? '' }}" placeholder="Nama, email, atau no telp">
                </div>
                <div>
                    <label class="field-label">Marketing</label>
                    <select name="marketing_id" class="field-select">
                        <option value="">Semua marketing</option>
                        @foreach ($marketingUsers as $marketing)
                            <option value="{{ $marketing->id }}" @selected((string) ($filters['marketing_id'] ?? '') === (string) $marketing->id)>{{ $marketing->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label">Bulan</label>
                    <input type="month" name="period" class="field-input" value="{{ $filters['period'] ?? now()->format('Y-m') }}">
                </div>
                <div class="ceo-filter-actions">
                    <button type="submit" class="btn-accent">Terapkan</button>
                    <a href="{{ route('ceo.customers.index') }}" class="btn-secondary">Reset</a>
                </div>
            </form>
        </x-filter-toolbar>

        <section class="ceo-summary-grid ceo-summary-grid--balanced" data-reveal>
            <article class="ceo-summary-card">
                <p class="ceo-summary-card__label">Total pelanggan</p>
                <p class="ceo-summary-card__value">{{ number_format($summary['total']) }}</p>
                <p class="ceo-summary-card__caption">Seluruh pelanggan yang masuk ke hasil monitoring saat ini.</p>
            </article>
            <article class="ceo-summary-card">
                <p class="ceo-summary-card__label">Pelanggan baru</p>
                <p class="ceo-summary-card__value">{{ number_format($summary['new_customers']) }}</p>
                <p class="ceo-summary-card__caption">Pelanggan yang dibuat pada periode aktif.</p>
            </article>
            <article class="ceo-summary-card ceo-summary-card--success">
                <p class="ceo-summary-card__label">Pelanggan aktif</p>
                <p class="ceo-summary-card__value">{{ number_format($summary['active_customers']) }}</p>
                <p class="ceo-summary-card__caption">Sudah memiliki minimal satu pengajuan pada periode ini.</p>
            </article>
            <article class="ceo-summary-card">
                <p class="ceo-summary-card__label">Repeat customer</p>
                <p class="ceo-summary-card__value">{{ number_format($summary['repeat_customers']) }}</p>
                <p class="ceo-summary-card__caption">Pelanggan dengan dua pengajuan atau lebih.</p>
            </article>
            <article class="ceo-summary-card">
                <p class="ceo-summary-card__label">Total nilai pengajuan</p>
                <p class="ceo-summary-card__value">Rp {{ number_format((int) $summary['total_value'], 0, ',', '.') }}</p>
                <p class="ceo-summary-card__caption">Akumulasi nilai pengajuan dari pelanggan yang terfilter.</p>
            </article>
        </section>

        <section class="ceo-panel" data-reveal>
            <div class="ceo-panel__head">
                <div>
                    <span class="ceo-chip ceo-chip--soft">Spotlight Customer</span>
                    <h3 class="ceo-panel__title">Pelanggan paling aktif dan paling bernilai</h3>
                </div>
                <p class="ceo-panel__eyebrow">Disusun dari jumlah pengajuan dan nilai pembiayaan tertinggi.</p>
            </div>

            <div class="ceo-customer-spotlight-grid">
                @forelse ($spotlight as $customer)
                    <article class="ceo-customer-card">
                        <div class="ceo-customer-card__head">
                            <div>
                                <span class="ceo-chip ceo-chip--soft">Top Customer #{{ $loop->iteration }}</span>
                                <h3 class="ceo-customer-card__title">{{ $customer->display_name }}</h3>
                            </div>
                            <div class="ceo-customer-card__avatar">
                                {{ strtoupper(mb_substr($customer->display_name, 0, 1)) }}
                            </div>
                        </div>

                        <div class="ceo-customer-card__submeta">
                            <span>{{ $customer->marketingOwner?->name ?? 'Belum ada owner' }}</span>
                            <span>{{ $customer->email ?? ($customer->no_telp ?? '-') }}</span>
                        </div>

                        <div class="ceo-customer-card__stats">
                            <div class="ceo-customer-card__stat">
                                <span class="ceo-customer-card__stat-label">Total pengajuan</span>
                                <strong>{{ number_format($customer->total_pengajuan) }}</strong>
                            </div>
                            <div class="ceo-customer-card__stat">
                                <span class="ceo-customer-card__stat-label">Status terakhir</span>
                                <strong>{{ $customer->last_status ? str($customer->last_status)->replace('_', ' ')->title() : 'Belum ada' }}</strong>
                            </div>
                            <div class="ceo-customer-card__stat ceo-customer-card__stat--wide">
                                <span class="ceo-customer-card__stat-label">Total nilai</span>
                                <strong>Rp {{ number_format((int) $customer->total_nilai_pengajuan, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="dashboard-empty-state">
                        Belum ada pelanggan aktif pada periode ini.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="ceo-panel" data-reveal>
            <div class="ceo-panel__head">
                <div>
                    <span class="ceo-chip ceo-chip--soft">Customer List</span>
                    <h3 class="ceo-panel__title">Daftar monitoring pelanggan</h3>
                </div>
                <p class="ceo-panel__eyebrow">Gunakan tabel ini untuk membaca owner, status terakhir, dan intensitas aktivitas.</p>
            </div>

            <div class="ceo-table-wrap">
                <table class="ceo-table">
                    <thead>
                        <tr>
                            <th>Pelanggan</th>
                            <th>Marketing</th>
                            <th>Jumlah Pengajuan</th>
                            <th>Status Terakhir</th>
                            <th>Aktivitas Terakhir</th>
                            <th>Total Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td>
                                    <p class="font-semibold text-slate-950">{{ $row->display_name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $row->no_telp ?? $row->email ?? '-' }}</p>
                                </td>
                                <td>{{ $row->marketingOwner?->name ?? '-' }}</td>
                                <td>{{ number_format($row->total_pengajuan) }}</td>
                                <td>
                                    @if ($row->last_status)
                                        <x-status-badge :status="$row->last_status" />
                                    @else
                                        <span class="text-sm text-slate-500">Belum ada pengajuan</span>
                                    @endif
                                </td>
                                <td>{{ $row->last_application_at ? \Carbon\Carbon::parse($row->last_application_at)->format('d M Y H:i') : '-' }}</td>
                                <td>Rp {{ number_format((int) $row->total_nilai_pengajuan, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="dashboard-empty-state">Belum ada data pelanggan untuk ditampilkan.</div>
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

<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\PaymentVerificationStatus;
use App\Models\Motor;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\PengajuanKredit;
use App\Models\PengajuanLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CeoAnalyticsService
{
    public function getDashboardData(): array
    {
        return Cache::remember('ceo.dashboard.v4', now()->addMinutes(5), function (): array {
            $now = now();
            $thisMonthStart = $now->copy()->startOfMonth();
            $thisMonthEnd = $now->copy()->endOfMonth();
            $lastMonthStart = $now->copy()->subMonthNoOverflow()->startOfMonth();
            $lastMonthEnd = $now->copy()->subMonthNoOverflow()->endOfMonth();

            $totalPengajuan = PengajuanKredit::count();
            $totalPending = PengajuanKredit::pending()->count();
            $totalReview = PengajuanKredit::review()->count();
            $totalApproved = PengajuanKredit::approved()->count();
            $totalRejected = PengajuanKredit::rejected()->count();

            $approvalRate = $totalPengajuan > 0
                ? round(($totalApproved / $totalPengajuan) * 100, 1)
                : 0.0;

            $pengajuanBulanIni = $this->applicationsWithinRange($thisMonthStart, $thisMonthEnd)->count();
            $pengajuanBulanLalu = $this->applicationsWithinRange($lastMonthStart, $lastMonthEnd)->count();
            $approvalBulanIni = $this->applicationsWithinRange($thisMonthStart, $thisMonthEnd)->approved()->count();
            $approvalBulanLalu = $this->applicationsWithinRange($lastMonthStart, $lastMonthEnd)->approved()->count();
            $customerBaruBulanIni = Pelanggan::query()->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])->count();
            $customerBaruBulanLalu = Pelanggan::query()->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();

            $monthlyTrend = $this->buildMonthlyTrend($now);
            $statusDistribution = [
                ['label' => 'Pending', 'value' => $totalPending, 'tone' => 'warning'],
                ['label' => 'Review', 'value' => $totalReview, 'tone' => 'info'],
                ['label' => 'Approved', 'value' => $totalApproved, 'tone' => 'success'],
                ['label' => 'Rejected', 'value' => $totalRejected, 'tone' => 'danger'],
            ];

            $topMarketingRows = $this->topMarketingForPeriod($thisMonthStart, $thisMonthEnd)->take(5);
            $topMarketingThisMonth = $topMarketingRows->first();
            $topMotorThisMonth = $this->topMotorForPeriod($thisMonthStart, $thisMonthEnd)->first();
            $bestApprovalThisMonth = $this->bestApprovalMarketingForPeriod($thisMonthStart, $thisMonthEnd)->first();

            $pendingRatio = $totalPengajuan > 0
                ? round((($totalPending + $totalReview) / $totalPengajuan) * 100, 1)
                : 0.0;

            return [
                'kpis' => [
                    ['label' => 'Total Pengajuan', 'value' => $totalPengajuan, 'caption' => 'Seluruh pipeline pengajuan pada sistem.', 'tone' => 'default'],
                    ['label' => 'Total Pending', 'value' => $totalPending, 'caption' => 'Belum masuk tahap review lanjutan.', 'tone' => 'warning'],
                    ['label' => 'Total Approved', 'value' => $totalApproved, 'caption' => 'Status disetujui, aktif, dan selesai.', 'tone' => 'success'],
                    ['label' => 'Total Rejected', 'value' => $totalRejected, 'caption' => 'Pengajuan yang ditolak atau dibatalkan.', 'tone' => 'danger'],
                    ['label' => 'Approval Rate', 'value' => $approvalRate, 'caption' => 'Approved dibanding total pengajuan.', 'tone' => 'default', 'suffix' => '%'],
                    [
                        'label' => 'Marketing Aktif',
                        'value' => User::query()->where('role', User::ROLE_MARKETING)->whereHas('assignedPengajuan')->count(),
                        'caption' => 'Marketing yang sudah memiliki pipeline.',
                        'tone' => 'default',
                    ],
                    ['label' => 'Total Pelanggan', 'value' => Pelanggan::count(), 'caption' => 'Pelanggan yang tercatat pada database.', 'tone' => 'default'],
                    ['label' => 'Motor Aktif', 'value' => Motor::active()->count(), 'caption' => 'Unit aktif di katalog Credira.', 'tone' => 'default'],
                    [
                        'label' => 'Nilai Pembiayaan',
                        'value' => (int) PengajuanKredit::sum('total_bayar'),
                        'caption' => 'Akumulasi total pembiayaan seluruh pengajuan.',
                        'tone' => 'default',
                        'currency' => true,
                    ],
                    [
                        'label' => 'Pembayaran Bulan Ini',
                        'value' => (int) Pembayaran::query()
                            ->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])
                            ->where('status_verifikasi', PaymentVerificationStatus::Valid->value)
                            ->sum('nominal_bayar'),
                        'caption' => 'Pembayaran tervalidasi bulan berjalan.',
                        'tone' => 'success',
                        'currency' => true,
                    ],
                ],
                'trends' => [
                    ['label' => 'Pengajuan bulan ini', 'value' => $pengajuanBulanIni, 'comparison' => $this->compare($pengajuanBulanIni, $pengajuanBulanLalu)],
                    ['label' => 'Approval bulan ini', 'value' => $approvalBulanIni, 'comparison' => $this->compare($approvalBulanIni, $approvalBulanLalu)],
                    ['label' => 'Customer baru bulan ini', 'value' => $customerBaruBulanIni, 'comparison' => $this->compare($customerBaruBulanIni, $customerBaruBulanLalu)],
                    ['label' => 'Growth approval rate', 'value' => $approvalRate, 'comparison' => $this->compare($approvalBulanIni, $approvalBulanLalu)],
                ],
                'monthlyTrend' => $monthlyTrend,
                'statusDistribution' => $statusDistribution,
                'insights' => array_values(array_filter([
                    $topMarketingThisMonth ? [
                        'tone' => 'success',
                        'title' => 'Marketing paling aktif bulan ini',
                        'description' => $topMarketingThisMonth->name.' memimpin dengan '.number_format($topMarketingThisMonth->total_pengajuan).' pengajuan.',
                    ] : null,
                    $topMotorThisMonth ? [
                        'tone' => 'default',
                        'title' => 'Motor paling banyak diajukan',
                        'description' => $topMotorThisMonth->nama_motor.' menjadi unit paling diminati dengan '.number_format($topMotorThisMonth->total_pengajuan).' pengajuan.',
                    ] : null,
                    $bestApprovalThisMonth ? [
                        'tone' => 'success',
                        'title' => 'Approval rate tertinggi bulan ini',
                        'description' => $bestApprovalThisMonth->name.' mencatat approval rate '.number_format((float) $bestApprovalThisMonth->approval_rate, 1).'%.',
                    ] : null,
                    $pendingRatio >= 35 ? [
                        'tone' => 'warning',
                        'title' => 'Pending pipeline relatif tinggi',
                        'description' => number_format($pendingRatio, 1).'% pengajuan masih berada di area pending dan review.',
                    ] : null,
                    $pengajuanBulanIni < $pengajuanBulanLalu ? [
                        'tone' => 'danger',
                        'title' => 'Volume pengajuan menurun',
                        'description' => 'Pengajuan bulan ini turun '.number_format(abs($this->compare($pengajuanBulanIni, $pengajuanBulanLalu)['delta']), 1).'% dibanding bulan lalu.',
                    ] : [
                        'tone' => 'success',
                        'title' => 'Momentum akuisisi masih terjaga',
                        'description' => 'Pengajuan bulan ini stabil atau naik dibanding bulan lalu.',
                    ],
                ])),
                'recentActivity' => $this->recentActivity(),
                'topMarketing' => $topMarketingRows,
                'periodLabel' => $now->translatedFormat('F Y'),
            ];
        });
    }

    public function getReportData(array $filters): array
    {
        $query = $this->buildReportQuery($filters);

        /** @var LengthAwarePaginator $reports */
        $reports = (clone $query)->paginate(12)->withQueryString();
        $summaryQuery = $this->buildReportQuery($filters);

        return [
            'reports' => $reports,
            'filters' => $filters,
            'summary' => [
                'total' => (clone $summaryQuery)->count(),
                'approved' => (clone $summaryQuery)->approved()->count(),
                'rejected' => (clone $summaryQuery)->rejected()->count(),
                'pending' => (clone $summaryQuery)->pending()->count() + (clone $summaryQuery)->review()->count(),
            ],
            'marketingUsers' => User::query()->where('role', User::ROLE_MARKETING)->orderBy('name')->get(),
            'statusOptions' => $this->statusOptions(),
        ];
    }

    public function exportReportsCsv(array $filters): StreamedResponse
    {
        $rows = $this->buildReportQuery($filters)->get();

        return $this->streamCsvDownload(
            'laporan-pengajuan-'.$this->buildDateRangeFilenameSuffix($filters).'.csv',
            [
                'Kode Pengajuan',
                'Tanggal Pengajuan',
                'Status Pengajuan',
                'Nama Pelanggan',
                'Email Pelanggan',
                'No Telp Pelanggan',
                'Marketing',
                'Email Marketing',
                'Motor',
                'Merk',
                'Tenor',
                'Asuransi',
                'Harga Cash',
                'DP',
                'Pokok Kredit',
                'Harga Kredit',
                'Margin Kredit',
                'Biaya Admin',
                'Biaya Asuransi',
                'Total Bayar',
                'Approved At',
                'Rejected At',
                'Created At',
                'Updated At',
            ],
            $rows->map(function (PengajuanKredit $item): array {
                return [
                    $item->kode_pengajuan,
                    optional($item->tgl_pengajuan)->format('Y-m-d') ?: optional($item->created_at)->format('Y-m-d'),
                    $item->status_pengajuan,
                    $item->pelanggan?->display_name,
                    $item->pelanggan?->email,
                    $item->pelanggan?->no_telp,
                    $item->marketingOwner?->name,
                    $item->marketingOwner?->email,
                    $item->motor?->nama_motor,
                    $item->motor?->merk,
                    $item->jenisCicilan?->durasi_bulan ? $item->jenisCicilan->durasi_bulan.' bulan' : null,
                    $item->asuransi?->nama_asuransi,
                    (int) $item->harga_cash,
                    (int) $item->dp,
                    (int) $item->pokok_kredit,
                    (float) $item->harga_kredit,
                    (float) $item->margin_kredit,
                    (int) $item->biaya_admin,
                    (int) $item->biaya_asuransi,
                    (int) $item->total_bayar,
                    optional($item->approved_at)->format('Y-m-d H:i:s'),
                    optional($item->rejected_at)->format('Y-m-d H:i:s'),
                    optional($item->created_at)->format('Y-m-d H:i:s'),
                    optional($item->updated_at)->format('Y-m-d H:i:s'),
                ];
            })
        );
    }

    public function getMarketingPerformanceData(array $filters): array
    {
        [$start, $end, $label, $slug] = $this->resolvePeriod($filters['period'] ?? null);
        $rows = $this->buildMarketingPerformanceRows($start, $end);
        $topPerformer = $rows->sortByDesc(fn ($row) => ((float) $row->approval_rate * 1000000) + (int) $row->approved_count)->first();
        $mostActive = $rows->sortByDesc(fn ($row) => ((int) $row->total_pengajuan * 1000000) + (int) $row->approved_count)->first();

        return [
            'filters' => $filters,
            'periodLabel' => $label,
            'periodSlug' => $slug,
            'rows' => $rows,
            'summary' => [
                'marketing_count' => $rows->count(),
                'applications' => (int) $rows->sum('total_pengajuan'),
                'approved' => (int) $rows->sum('approved_count'),
                'review' => (int) $rows->sum('review_count'),
                'average_rate' => round((float) ($rows->where('total_pengajuan', '>', 0)->avg('approval_rate') ?? 0), 1),
                'total_value' => (int) $rows->sum('total_value'),
                'approved_value' => (int) $rows->sum('approved_value'),
            ],
            'topPerformer' => $topPerformer,
            'mostActive' => $mostActive,
        ];
    }

    public function exportMarketingPerformanceCsv(array $filters): StreamedResponse
    {
        $data = $this->getMarketingPerformanceData($filters);

        return $this->streamCsvDownload(
            'performa-marketing-'.$data['periodSlug'].'.csv',
            [
                'Periode',
                'Nama Marketing',
                'Email Marketing',
                'Jumlah Pelanggan',
                'Jumlah Pengajuan',
                'Approved',
                'Rejected',
                'Pending',
                'Review',
                'Approval Rate',
                'Total Nilai Pengajuan',
                'Total Nilai Approved',
            ],
            $data['rows']->map(fn ($row) => [
                $data['periodLabel'],
                $row->name,
                $row->email,
                (int) $row->total_pelanggan,
                (int) $row->total_pengajuan,
                (int) $row->approved_count,
                (int) $row->rejected_count,
                (int) $row->pending_count,
                (int) $row->review_count,
                (float) $row->approval_rate,
                (int) $row->total_value,
                (int) $row->approved_value,
            ])
        );
    }

    public function getProductAnalyticsData(array $filters): array
    {
        [$start, $end, $label, $slug] = $this->resolvePeriod($filters['period'] ?? null);

        $rowsQuery = $this->applyProductSearch($this->buildProductAnalyticsQuery($start, $end), $filters);

        /** @var LengthAwarePaginator $rows */
        $rows = $rowsQuery->paginate(10)->withQueryString();

        $brandStats = PengajuanKredit::query()
            ->join('motors', 'motors.id', '=', 'pengajuan_kredit.motor_id')
            ->selectRaw('motors.merk')
            ->selectRaw('COUNT(*) as total_pengajuan')
            ->tap(fn (Builder $query) => $this->applyApplicationPeriod($query, $start, $end))
            ->groupBy('motors.merk')
            ->orderByDesc('total_pengajuan')
            ->limit(5)
            ->get();

        return [
            'filters' => $filters,
            'periodLabel' => $label,
            'periodSlug' => $slug,
            'rows' => $rows,
            'summary' => [
                'motor_active' => Motor::active()->count(),
                'applications' => (int) $this->applicationsWithinRange($start, $end)->count(),
                'approved' => (int) $this->applicationsWithinRange($start, $end)->approved()->count(),
                'rejected' => (int) $this->applicationsWithinRange($start, $end)->rejected()->count(),
                'total_pembiayaan' => (int) $this->applicationsWithinRange($start, $end)->sum('total_bayar'),
            ],
            'topMotor' => $rows->getCollection()->first(),
            'topApprovedMotor' => $rows->getCollection()->sortByDesc(fn ($row) => ((int) $row->approved_count * 1000000000) + (int) $row->total_pembiayaan)->first(),
            'brandStats' => $brandStats,
        ];
    }

    public function exportProductAnalyticsCsv(array $filters): StreamedResponse
    {
        [$start, $end, $label, $slug] = $this->resolvePeriod($filters['period'] ?? null);
        $rows = $this->applyProductSearch($this->buildProductAnalyticsQuery($start, $end), $filters)->get();

        return $this->streamCsvDownload(
            'statistik-motor-'.$slug.'.csv',
            [
                'Periode',
                'Nama Motor',
                'Merk',
                'Jenis Motor',
                'Harga Jual',
                'Jumlah Pengajuan',
                'Jumlah Approved',
                'Jumlah Rejected',
                'Total Nilai Pembiayaan',
                'Status Aktif',
            ],
            $rows->map(fn ($row) => [
                $label,
                $row->nama_motor,
                $row->merk,
                $row->jenis_motor,
                (int) $row->harga_jual,
                (int) $row->total_pengajuan,
                (int) $row->approved_count,
                (int) $row->rejected_count,
                (int) $row->total_pembiayaan,
                $row->status_aktif ? 'Aktif' : 'Nonaktif',
            ])
        );
    }

    public function getCustomerAnalyticsData(array $filters): array
    {
        [$start, $end, $label, $slug] = $this->resolvePeriod($filters['period'] ?? null);
        $baseQuery = $this->buildCustomerAnalyticsQuery($filters, $start, $end);

        /** @var LengthAwarePaginator $rows */
        $rows = (clone $baseQuery)->paginate(12)->withQueryString();
        $spotlight = (clone $baseQuery)
            ->whereRaw('COALESCE(application_stats.total_pengajuan, 0) > 0')
            ->limit(3)
            ->get();

        $summaryBase = $this->buildCustomerSummaryBaseQuery($filters, $start, $end);

        return [
            'filters' => $filters,
            'periodLabel' => $label,
            'periodSlug' => $slug,
            'rows' => $rows,
            'marketingUsers' => User::query()->where('role', User::ROLE_MARKETING)->orderBy('name')->get(),
            'summary' => [
                'total' => (clone $summaryBase)->count(),
                'new_customers' => (clone $summaryBase)->whereBetween('pelanggan.created_at', [$start, $end])->count(),
                'active_customers' => (clone $summaryBase)->whereRaw('COALESCE(application_stats.total_pengajuan, 0) > 0')->count(),
                'repeat_customers' => (clone $summaryBase)->whereRaw('COALESCE(application_stats.total_pengajuan, 0) >= 2')->count(),
                'total_value' => (int) ((clone $summaryBase)->sum(DB::raw('COALESCE(application_stats.total_nilai_pengajuan, 0)')) ?: 0),
            ],
            'spotlight' => $spotlight,
        ];
    }

    public function exportCustomerAnalyticsCsv(array $filters): StreamedResponse
    {
        [$start, $end, $label, $slug] = $this->resolvePeriod($filters['period'] ?? null);
        $rows = $this->buildCustomerAnalyticsQuery($filters, $start, $end)->get();

        return $this->streamCsvDownload(
            'monitoring-pelanggan-'.$slug.'.csv',
            [
                'Periode',
                'Nama Pelanggan',
                'Email',
                'No Telp',
                'Marketing Penanggung Jawab',
                'Jumlah Pengajuan',
                'Status Pengajuan Terakhir',
                'Tanggal Pengajuan Terakhir',
                'Total Nilai Pengajuan',
                'Created At',
            ],
            $rows->map(fn ($row) => [
                $label,
                $row->display_name,
                $row->email,
                $row->no_telp,
                $row->marketingOwner?->name,
                (int) $row->total_pengajuan,
                $row->last_status,
                $row->last_application_at ? Carbon::parse($row->last_application_at)->format('Y-m-d H:i:s') : null,
                (int) $row->total_nilai_pengajuan,
                optional($row->created_at)->format('Y-m-d H:i:s'),
            ])
        );
    }

    private function buildMarketingPerformanceRows(?Carbon $start, ?Carbon $end): Collection
    {
        $customerStats = Pelanggan::query()
            ->selectRaw('marketing_user_id, COUNT(*) as total_pelanggan')
            ->whereNotNull('marketing_user_id')
            ->when($start && $end, fn (Builder $query) => $query->whereBetween('created_at', [$start, $end]))
            ->groupBy('marketing_user_id');

        $applicationStats = PengajuanKredit::query()
            ->selectRaw('marketing_user_id')
            ->selectRaw('COUNT(*) as total_pengajuan')
            ->selectRaw("SUM(CASE WHEN status_pengajuan IN ('".ApplicationStatus::Disetujui->value."', '".ApplicationStatus::KontrakAktif->value."', '".ApplicationStatus::Selesai->value."') THEN 1 ELSE 0 END) as approved_count")
            ->selectRaw("SUM(CASE WHEN status_pengajuan IN ('".ApplicationStatus::Ditolak->value."', '".ApplicationStatus::DibatalkanAdmin->value."', '".ApplicationStatus::DibatalkanUser->value."') THEN 1 ELSE 0 END) as rejected_count")
            ->selectRaw("SUM(CASE WHEN status_pengajuan IN ('".ApplicationStatus::Draft->value."', '".ApplicationStatus::MenungguKonfirmasi->value."') THEN 1 ELSE 0 END) as pending_count")
            ->selectRaw("SUM(CASE WHEN status_pengajuan IN ('".ApplicationStatus::VerifikasiDokumen->value."', '".ApplicationStatus::Diproses->value."', '".ApplicationStatus::Survey->value."') THEN 1 ELSE 0 END) as review_count")
            ->selectRaw('SUM(total_bayar) as total_value')
            ->selectRaw("SUM(CASE WHEN status_pengajuan IN ('".ApplicationStatus::Disetujui->value."', '".ApplicationStatus::KontrakAktif->value."', '".ApplicationStatus::Selesai->value."') THEN total_bayar ELSE 0 END) as approved_value")
            ->whereNotNull('marketing_user_id')
            ->tap(fn (Builder $query) => $this->applyApplicationPeriod($query, $start, $end))
            ->groupBy('marketing_user_id');

        return User::query()
            ->where('users.role', User::ROLE_MARKETING)
            ->leftJoinSub($customerStats, 'customer_stats', fn ($join) => $join->on('customer_stats.marketing_user_id', '=', 'users.id'))
            ->leftJoinSub($applicationStats, 'application_stats', fn ($join) => $join->on('application_stats.marketing_user_id', '=', 'users.id'))
            ->select('users.id', 'users.name', 'users.email')
            ->selectRaw('COALESCE(customer_stats.total_pelanggan, 0) as total_pelanggan')
            ->selectRaw('COALESCE(application_stats.total_pengajuan, 0) as total_pengajuan')
            ->selectRaw('COALESCE(application_stats.approved_count, 0) as approved_count')
            ->selectRaw('COALESCE(application_stats.rejected_count, 0) as rejected_count')
            ->selectRaw('COALESCE(application_stats.pending_count, 0) as pending_count')
            ->selectRaw('COALESCE(application_stats.review_count, 0) as review_count')
            ->selectRaw('COALESCE(application_stats.total_value, 0) as total_value')
            ->selectRaw('COALESCE(application_stats.approved_value, 0) as approved_value')
            ->selectRaw('CASE WHEN COALESCE(application_stats.total_pengajuan, 0) > 0 THEN ROUND((COALESCE(application_stats.approved_count, 0) / application_stats.total_pengajuan) * 100, 1) ELSE 0 END as approval_rate')
            ->orderByDesc('total_pengajuan')
            ->orderByDesc('approved_count')
            ->orderBy('users.name')
            ->get()
            ->values();
    }

    private function buildProductAnalyticsQuery(?Carbon $start, ?Carbon $end): Builder
    {
        $applicationStats = PengajuanKredit::query()
            ->selectRaw('motor_id')
            ->selectRaw('COUNT(*) as total_pengajuan')
            ->selectRaw("SUM(CASE WHEN status_pengajuan IN ('".ApplicationStatus::Disetujui->value."', '".ApplicationStatus::KontrakAktif->value."', '".ApplicationStatus::Selesai->value."') THEN 1 ELSE 0 END) as approved_count")
            ->selectRaw("SUM(CASE WHEN status_pengajuan IN ('".ApplicationStatus::Ditolak->value."', '".ApplicationStatus::DibatalkanAdmin->value."', '".ApplicationStatus::DibatalkanUser->value."') THEN 1 ELSE 0 END) as rejected_count")
            ->selectRaw('SUM(total_bayar) as total_pembiayaan')
            ->whereNotNull('motor_id')
            ->tap(fn (Builder $query) => $this->applyApplicationPeriod($query, $start, $end))
            ->groupBy('motor_id');

        return Motor::query()
            ->leftJoinSub($applicationStats, 'application_stats', fn ($join) => $join->on('application_stats.motor_id', '=', 'motors.id'))
            ->leftJoin('jenis_motor', 'jenis_motor.id', '=', 'motors.jenis_motor_id')
            ->select('motors.id', 'motors.nama_motor', 'motors.merk', 'motors.status_aktif', 'motors.harga_jual', 'jenis_motor.jenis as jenis_motor')
            ->selectRaw('COALESCE(application_stats.total_pengajuan, 0) as total_pengajuan')
            ->selectRaw('COALESCE(application_stats.approved_count, 0) as approved_count')
            ->selectRaw('COALESCE(application_stats.rejected_count, 0) as rejected_count')
            ->selectRaw('COALESCE(application_stats.total_pembiayaan, 0) as total_pembiayaan')
            ->orderByDesc('total_pengajuan')
            ->orderByDesc('approved_count')
            ->orderBy('motors.nama_motor');
    }

    private function buildCustomerAnalyticsQuery(array $filters, ?Carbon $start, ?Carbon $end): Builder
    {
        $applicationStats = PengajuanKredit::query()
            ->selectRaw('pelanggan_id')
            ->selectRaw('COUNT(*) as total_pengajuan')
            ->selectRaw('SUM(total_bayar) as total_nilai_pengajuan')
            ->selectRaw('MAX('.$this->applicationDateExpression().') as last_application_at')
            ->tap(fn (Builder $query) => $this->applyApplicationPeriod($query, $start, $end))
            ->groupBy('pelanggan_id');

        $lastStatusSub = PengajuanKredit::query()
            ->select('status_pengajuan')
            ->whereColumn('pengajuan_kredit.pelanggan_id', 'pelanggan.id')
            ->tap(fn (Builder $query) => $this->applyApplicationPeriod($query, $start, $end))
            ->orderByRaw($this->applicationDateExpression().' DESC')
            ->orderByDesc('id')
            ->limit(1);

        return Pelanggan::query()
            ->with('marketingOwner')
            ->leftJoinSub($applicationStats, 'application_stats', fn ($join) => $join->on('application_stats.pelanggan_id', '=', 'pelanggan.id'))
            ->select('pelanggan.*')
            ->selectRaw('COALESCE(application_stats.total_pengajuan, 0) as total_pengajuan')
            ->selectRaw('COALESCE(application_stats.total_nilai_pengajuan, 0) as total_nilai_pengajuan')
            ->selectRaw('application_stats.last_application_at as last_application_at')
            ->selectSub($lastStatusSub, 'last_status')
            ->when(filled($filters['q'] ?? null), function (Builder $query) use ($filters): void {
                $keyword = trim((string) $filters['q']);
                $query->where(function (Builder $inner) use ($keyword): void {
                    $inner
                        ->where('pelanggan.nama_lengkap', 'like', '%'.$keyword.'%')
                        ->orWhere('pelanggan.nama_pelanggan', 'like', '%'.$keyword.'%')
                        ->orWhere('pelanggan.email', 'like', '%'.$keyword.'%')
                        ->orWhere('pelanggan.no_telp', 'like', '%'.$keyword.'%');
                });
            })
            ->when(filled($filters['marketing_id'] ?? null), fn (Builder $query) => $query->where('pelanggan.marketing_user_id', (int) $filters['marketing_id']))
            ->orderByRaw('COALESCE(application_stats.total_pengajuan, 0) DESC')
            ->orderByRaw('COALESCE(application_stats.last_application_at, pelanggan.created_at) DESC')
            ->orderByDesc('pelanggan.id');
    }

    private function buildCustomerSummaryBaseQuery(array $filters, ?Carbon $start, ?Carbon $end): Builder
    {
        $applicationStats = PengajuanKredit::query()
            ->selectRaw('pelanggan_id')
            ->selectRaw('COUNT(*) as total_pengajuan')
            ->selectRaw('SUM(total_bayar) as total_nilai_pengajuan')
            ->tap(fn (Builder $query) => $this->applyApplicationPeriod($query, $start, $end))
            ->groupBy('pelanggan_id');

        return Pelanggan::query()
            ->leftJoinSub($applicationStats, 'application_stats', fn ($join) => $join->on('application_stats.pelanggan_id', '=', 'pelanggan.id'))
            ->select('pelanggan.id')
            ->when(filled($filters['q'] ?? null), function (Builder $query) use ($filters): void {
                $keyword = trim((string) $filters['q']);
                $query->where(function (Builder $inner) use ($keyword): void {
                    $inner
                        ->where('pelanggan.nama_lengkap', 'like', '%'.$keyword.'%')
                        ->orWhere('pelanggan.nama_pelanggan', 'like', '%'.$keyword.'%')
                        ->orWhere('pelanggan.email', 'like', '%'.$keyword.'%')
                        ->orWhere('pelanggan.no_telp', 'like', '%'.$keyword.'%');
                });
            })
            ->when(filled($filters['marketing_id'] ?? null), fn (Builder $query) => $query->where('pelanggan.marketing_user_id', (int) $filters['marketing_id']));
    }

    private function buildReportQuery(array $filters): Builder
    {
        [$dateFrom, $dateTo] = $this->resolveDateRangeFilters($filters);

        return PengajuanKredit::query()
            ->with(['pelanggan', 'marketingOwner', 'motor', 'jenisCicilan', 'asuransi', 'financialDetail'])
            ->when(filled($filters['q'] ?? null), function (Builder $query) use ($filters): void {
                $keyword = trim((string) $filters['q']);
                $query->where(function (Builder $builder) use ($keyword): void {
                    $builder
                        ->where('kode_pengajuan', 'like', '%'.$keyword.'%')
                        ->orWhereHas('pelanggan', fn (Builder $pelangganQuery) => $pelangganQuery
                            ->where('nama_lengkap', 'like', '%'.$keyword.'%')
                            ->orWhere('nama_pelanggan', 'like', '%'.$keyword.'%')
                            ->orWhere('email', 'like', '%'.$keyword.'%')
                            ->orWhere('no_telp', 'like', '%'.$keyword.'%'));
                });
            })
            ->when(filled($filters['status'] ?? null), function (Builder $query) use ($filters): void {
                match ($filters['status']) {
                    'pending' => $query->pending(),
                    'review' => $query->review(),
                    'approved' => $query->approved(),
                    'rejected' => $query->rejected(),
                    default => $query,
                };
            })
            ->when(filled($filters['marketing_id'] ?? null), fn (Builder $query) => $query->where('marketing_user_id', (int) $filters['marketing_id']))
            ->tap(fn (Builder $query) => $this->applyApplicationPeriod($query, $dateFrom, $dateTo))
            ->orderByRaw($this->applicationDateExpression().' DESC')
            ->orderByDesc('id');
    }

    private function applyProductSearch(Builder $query, array $filters): Builder
    {
        return $query->when(filled($filters['q'] ?? null), function (Builder $builder) use ($filters): void {
            $keyword = trim((string) $filters['q']);
            $builder->where(function (Builder $inner) use ($keyword): void {
                $inner
                    ->where('motors.nama_motor', 'like', '%'.$keyword.'%')
                    ->orWhere('motors.merk', 'like', '%'.$keyword.'%')
                    ->orWhere('jenis_motor.jenis', 'like', '%'.$keyword.'%');
            });
        });
    }

    private function applicationsWithinRange(?Carbon $start, ?Carbon $end): Builder
    {
        return PengajuanKredit::query()->tap(fn (Builder $query) => $this->applyApplicationPeriod($query, $start, $end));
    }

    private function buildMonthlyTrend(Carbon $now): Collection
    {
        $start = $now->copy()->subMonths(5)->startOfMonth();

        $rows = DB::table('pengajuan_kredit')
            ->selectRaw("DATE_FORMAT(".$this->applicationDateExpression().", '%Y-%m') as month_key")
            ->selectRaw('COUNT(*) as total_pengajuan')
            ->selectRaw("SUM(CASE WHEN status_pengajuan IN ('".ApplicationStatus::Disetujui->value."', '".ApplicationStatus::KontrakAktif->value."', '".ApplicationStatus::Selesai->value."') THEN 1 ELSE 0 END) as approved_count")
            ->whereRaw($this->applicationDateExpression().' >= ?', [$start])
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->get()
            ->keyBy('month_key');

        $maxValue = max(1, (int) $rows->max('total_pengajuan'));

        return collect(range(0, 5))->map(function (int $offset) use ($start, $rows, $maxValue): array {
            $month = $start->copy()->addMonths($offset);
            $key = $month->format('Y-m');
            $row = $rows->get($key);
            $value = (int) ($row->total_pengajuan ?? 0);

            return [
                'label' => $month->translatedFormat('M'),
                'full_label' => $month->translatedFormat('F Y'),
                'value' => $value,
                'approved' => (int) ($row->approved_count ?? 0),
                'height' => max(16, (int) round(($value / $maxValue) * 100)),
            ];
        });
    }

    private function topMarketingForPeriod(Carbon $start, Carbon $end): Collection
    {
        return User::query()
            ->where('users.role', User::ROLE_MARKETING)
            ->join('pengajuan_kredit', 'pengajuan_kredit.marketing_user_id', '=', 'users.id')
            ->whereRaw($this->applicationDateExpression().' BETWEEN ? AND ?', [$start, $end])
            ->select('users.id', 'users.name')
            ->selectRaw('COUNT(pengajuan_kredit.id) as total_pengajuan')
            ->selectRaw("SUM(CASE WHEN status_pengajuan IN ('".ApplicationStatus::Disetujui->value."', '".ApplicationStatus::KontrakAktif->value."', '".ApplicationStatus::Selesai->value."') THEN 1 ELSE 0 END) as approved_count")
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_pengajuan')
            ->orderByDesc('approved_count')
            ->get();
    }

    private function bestApprovalMarketingForPeriod(Carbon $start, Carbon $end): Collection
    {
        return User::query()
            ->where('users.role', User::ROLE_MARKETING)
            ->join('pengajuan_kredit', 'pengajuan_kredit.marketing_user_id', '=', 'users.id')
            ->whereRaw($this->applicationDateExpression().' BETWEEN ? AND ?', [$start, $end])
            ->select('users.id', 'users.name')
            ->selectRaw('COUNT(pengajuan_kredit.id) as total_pengajuan')
            ->selectRaw("SUM(CASE WHEN status_pengajuan IN ('".ApplicationStatus::Disetujui->value."', '".ApplicationStatus::KontrakAktif->value."', '".ApplicationStatus::Selesai->value."') THEN 1 ELSE 0 END) as approved_count")
            ->selectRaw("ROUND((SUM(CASE WHEN status_pengajuan IN ('".ApplicationStatus::Disetujui->value."', '".ApplicationStatus::KontrakAktif->value."', '".ApplicationStatus::Selesai->value."') THEN 1 ELSE 0 END) / COUNT(pengajuan_kredit.id)) * 100, 1) as approval_rate")
            ->groupBy('users.id', 'users.name')
            ->havingRaw('COUNT(pengajuan_kredit.id) > 0')
            ->orderByDesc('approval_rate')
            ->orderByDesc('approved_count')
            ->get();
    }

    private function topMotorForPeriod(Carbon $start, Carbon $end): Collection
    {
        return Motor::query()
            ->join('pengajuan_kredit', 'pengajuan_kredit.motor_id', '=', 'motors.id')
            ->whereRaw($this->applicationDateExpression().' BETWEEN ? AND ?', [$start, $end])
            ->select('motors.id', 'motors.nama_motor', 'motors.merk')
            ->selectRaw('COUNT(pengajuan_kredit.id) as total_pengajuan')
            ->groupBy('motors.id', 'motors.nama_motor', 'motors.merk')
            ->orderByDesc('total_pengajuan')
            ->get();
    }

    private function recentActivity(): Collection
    {
        $applicationEvents = PengajuanKredit::query()
            ->with(['pelanggan', 'motor'])
            ->orderByRaw($this->applicationDateExpression().' DESC')
            ->take(4)
            ->get()
            ->map(fn (PengajuanKredit $item) => [
                'type' => 'Pengajuan Baru',
                'tone' => 'default',
                'title' => $item->kode_pengajuan.' masuk ke pipeline',
                'description' => ($item->pelanggan?->display_name ?? 'Pelanggan').' mengajukan '.($item->motor?->nama_motor ?? 'unit motor').'.',
                'time' => $item->created_at,
            ]);

        $logEvents = PengajuanLog::query()
            ->with('pengajuan.pelanggan')
            ->latest()
            ->take(4)
            ->get()
            ->map(fn (PengajuanLog $item) => [
                'type' => 'Status Update',
                'tone' => $item->status_baru === ApplicationStatus::Ditolak->value ? 'danger' : 'success',
                'title' => ($item->pengajuan?->kode_pengajuan ?? 'Pengajuan').' berubah ke '.str($item->status_baru)->replace('_', ' ')->title(),
                'description' => $item->catatan ?: ($item->pengajuan?->pelanggan?->display_name ?? 'Status pengajuan diperbarui.'),
                'time' => $item->created_at,
            ]);

        $paymentEvents = Pembayaran::query()
            ->with('pelanggan')
            ->where('status_verifikasi', PaymentVerificationStatus::Valid->value)
            ->latest()
            ->take(4)
            ->get()
            ->map(fn (Pembayaran $item) => [
                'type' => 'Pembayaran',
                'tone' => 'success',
                'title' => 'Pembayaran '.$item->kode_pembayaran.' diterima',
                'description' => ($item->pelanggan?->display_name ?? 'Pelanggan').' membayar Rp '.number_format((int) $item->nominal_bayar, 0, ',', '.').'.',
                'time' => $item->created_at,
            ]);

        return $applicationEvents
            ->concat($logEvents)
            ->concat($paymentEvents)
            ->sortByDesc('time')
            ->take(8)
            ->values();
    }

    private function resolvePeriod(?string $period): array
    {
        try {
            $date = $period
                ? Carbon::createFromFormat('Y-m', $period)->startOfMonth()
                : now()->startOfMonth();
        } catch (\Throwable) {
            $date = now()->startOfMonth();
        }

        return [
            $date->copy()->startOfMonth(),
            $date->copy()->endOfMonth(),
            $date->translatedFormat('F Y'),
            $date->format('Y-m'),
        ];
    }

    private function resolveDateRangeFilters(array $filters): array
    {
        $dateFrom = $this->safeParseDate($filters['date_from'] ?? null, true);
        $dateTo = $this->safeParseDate($filters['date_to'] ?? null, false);

        if ($dateFrom && $dateTo && $dateFrom->gt($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo->copy()->startOfDay(), $dateFrom->copy()->endOfDay()];
        }

        return [$dateFrom, $dateTo];
    }

    private function safeParseDate(?string $value, bool $startOfDay): ?Carbon
    {
        if (! filled($value)) {
            return null;
        }

        try {
            $date = Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }

        return $startOfDay ? $date->startOfDay() : $date->endOfDay();
    }

    private function applyApplicationPeriod(Builder $query, ?Carbon $start, ?Carbon $end, string $table = 'pengajuan_kredit'): Builder
    {
        $expression = $this->applicationDateExpression($table);

        return $query
            ->when($start && $end, fn (Builder $inner) => $inner->whereRaw($expression.' BETWEEN ? AND ?', [$start, $end]))
            ->when($start && ! $end, fn (Builder $inner) => $inner->whereRaw($expression.' >= ?', [$start]))
            ->when(! $start && $end, fn (Builder $inner) => $inner->whereRaw($expression.' <= ?', [$end]));
    }

    private function applicationDateExpression(string $table = 'pengajuan_kredit'): string
    {
        return 'COALESCE('.$table.'.tgl_pengajuan, '.$table.'.created_at)';
    }

    private function buildDateRangeFilenameSuffix(array $filters): string
    {
        if (filled($filters['date_from'] ?? null) || filled($filters['date_to'] ?? null)) {
            $from = filled($filters['date_from'] ?? null) ? str_replace('/', '-', (string) $filters['date_from']) : 'awal';
            $to = filled($filters['date_to'] ?? null) ? str_replace('/', '-', (string) $filters['date_to']) : 'akhir';

            return $from.'_'.$to;
        }

        return now()->format('Y-m');
    }

    private function statusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'review' => 'Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ];
    }

    private function compare(int|float $current, int|float $previous, string $unit = '%'): array
    {
        if ((float) $previous === 0.0) {
            return [
                'delta' => $current > 0 ? 100.0 : 0.0,
                'direction' => $current > 0 ? 'up' : 'flat',
                'label' => $current > 0 ? '+100'.$unit : '0'.$unit,
            ];
        }

        $delta = round((($current - $previous) / $previous) * 100, 1);

        return [
            'delta' => $delta,
            'direction' => $delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'flat'),
            'label' => ($delta > 0 ? '+' : '').$delta.$unit,
        ];
    }

    private function streamCsvDownload(string $filename, array $headers, Collection $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'w');

            fwrite($handle, chr(239).chr(187).chr(191));
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}

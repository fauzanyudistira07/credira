<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Motor;
use App\Models\Notification;
use App\Models\Pelanggan;
use App\Models\PengajuanKredit;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): View
    {
        $recentPengajuan = PengajuanKredit::query()
            ->with(['pelanggan', 'motor', 'marketingOwner', 'jenisCicilan'])
            ->latest()
            ->take(6)
            ->get();

        $statusDistribution = [
            ['label' => 'Pending', 'value' => PengajuanKredit::pending()->count()],
            ['label' => 'Review', 'value' => PengajuanKredit::review()->count()],
            ['label' => 'Approved', 'value' => PengajuanKredit::approved()->count()],
            ['label' => 'Rejected', 'value' => PengajuanKredit::rejected()->count()],
        ];

        $marketingPerformance = User::query()
            ->select('users.id', 'users.name')
            ->selectRaw('COUNT(pengajuan_kredit.id) as total_pengajuan')
            ->leftJoin('pengajuan_kredit', 'pengajuan_kredit.marketing_user_id', '=', 'users.id')
            ->where('users.role', User::ROLE_MARKETING)
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_pengajuan')
            ->limit(5)
            ->get();

        $applicationStatuses = [
            ApplicationStatus::MenungguKonfirmasi->value,
            ApplicationStatus::VerifikasiDokumen->value,
            ApplicationStatus::Diproses->value,
            ApplicationStatus::Survey->value,
        ];

        return view('admin.dashboard', [
            'stats' => [
                'total_users' => User::count(),
                'total_marketing' => User::where('role', User::ROLE_MARKETING)->count(),
                'total_motors' => Motor::count(),
                'total_pelanggan' => Pelanggan::count(),
                'total_pengajuan' => PengajuanKredit::count(),
                'total_pending' => PengajuanKredit::pending()->count(),
                'total_approved' => PengajuanKredit::approved()->count(),
                'total_rejected' => PengajuanKredit::rejected()->count(),
                'active_marketing' => PengajuanKredit::query()->distinct('marketing_user_id')->count('marketing_user_id'),
                'pending_ratio' => PengajuanKredit::count() > 0
                    ? (int) round((PengajuanKredit::pending()->count() / max(PengajuanKredit::count(), 1)) * 100)
                    : 0,
            ],
            'recentPengajuan' => $recentPengajuan,
            'statusDistribution' => $statusDistribution,
            'marketingPerformance' => $marketingPerformance,
            'recentNotifications' => Notification::query()
                ->latest()
                ->take(5)
                ->get(),
            'queueSummary' => [
                'queue' => PengajuanKredit::whereIn('status_pengajuan', $applicationStatuses)->count(),
                'value' => (int) PengajuanKredit::sum('total_bayar'),
                'average_dp' => (int) DB::table('pengajuan_kredit')->avg('dp'),
            ],
        ]);
    }
}

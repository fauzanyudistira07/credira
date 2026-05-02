<?php

namespace App\Http\Controllers\Web\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\PengajuanKredit;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $query = PengajuanKredit::query();

        return view('dashboard.marketing', [
            'stats' => [
                'total_pelanggan' => Pelanggan::query()->count(),
                'total_pengajuan' => (clone $query)->count(),
                'pending_pengajuan' => (clone $query)->pending()->count(),
                'approved_pengajuan' => (clone $query)->approved()->count(),
                'review_pengajuan' => (clone $query)->review()->count(),
            ],
            'recentApplications' => $query
                ->with(['pelanggan', 'motor', 'jenisCicilan'])
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }
}

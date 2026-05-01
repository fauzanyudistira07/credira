<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {
    }

    public function index(): View
    {
        return view('user.dashboard', [
            'dashboard' => $this->dashboardService->build($this->currentPelanggan()->load('user')),
        ]);
    }
}

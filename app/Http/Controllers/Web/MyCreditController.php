<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Contracts\View\View;

class MyCreditController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {
    }

    public function index(): View
    {
        return view('user.my-credit.index', [
            'dashboard' => $this->dashboardService->build($this->currentPelanggan()->load('user')),
        ]);
    }
}

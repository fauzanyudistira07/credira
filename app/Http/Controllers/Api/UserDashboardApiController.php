<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;

class UserDashboardApiController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {
    }

    public function index()
    {
        return $this->apiResponse(
            $this->dashboardService->build($this->currentPelanggan()->load('user')),
            'Dashboard berhasil dimuat.'
        );
    }
}

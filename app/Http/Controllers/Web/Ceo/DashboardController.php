<?php

namespace App\Http\Controllers\Web\Ceo;

use App\Http\Controllers\Controller;
use App\Services\CeoAnalyticsService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly CeoAnalyticsService $analyticsService,
    ) {
    }

    public function index(): View
    {
        return view('ceo.dashboard', $this->analyticsService->getDashboardData());
    }
}

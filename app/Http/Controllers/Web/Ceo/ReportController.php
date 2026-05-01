<?php

namespace App\Http\Controllers\Web\Ceo;

use App\Http\Controllers\Controller;
use App\Services\CeoAnalyticsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        private readonly CeoAnalyticsService $analyticsService,
    ) {
    }

    public function index(Request $request): View|StreamedResponse
    {
        $filters = $request->only(['q', 'status', 'marketing_id', 'date_from', 'date_to']);

        if ($request->string('export')->toString() === 'csv') {
            return $this->analyticsService->exportReportsCsv($filters);
        }

        return view('ceo.reports.index', $this->analyticsService->getReportData($filters));
    }
}

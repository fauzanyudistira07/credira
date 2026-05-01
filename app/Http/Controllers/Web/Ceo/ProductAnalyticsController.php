<?php

namespace App\Http\Controllers\Web\Ceo;

use App\Http\Controllers\Controller;
use App\Services\CeoAnalyticsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductAnalyticsController extends Controller
{
    public function __construct(
        private readonly CeoAnalyticsService $analyticsService,
    ) {
    }

    public function index(Request $request): View|StreamedResponse
    {
        $filters = $request->only(['period', 'q']);

        if ($request->string('export')->toString() === 'csv') {
            return $this->analyticsService->exportProductAnalyticsCsv($filters);
        }

        return view('ceo.products.index', $this->analyticsService->getProductAnalyticsData($filters));
    }
}

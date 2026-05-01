<?php

namespace App\Http\Controllers\Web\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Asuransi;
use App\Models\JenisCicilan;
use App\Models\Motor;
use App\Services\CreditSimulationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SimulationController extends Controller
{
    public function __construct(
        private readonly CreditSimulationService $simulationService,
    ) {
    }

    public function index(Request $request): View
    {
        $motors = Motor::query()
            ->with('jenisMotor')
            ->active()
            ->orderBy('nama_motor')
            ->get();
        $plans = JenisCicilan::query()->orderBy('durasi_bulan')->get();
        $insurances = Asuransi::query()->orderBy('nama_asuransi')->get();

        $selectedMotor = $motors->firstWhere('id', $request->integer('motor_id')) ?? $motors->first();
        $selectedPlan = $plans->firstWhere('id', $request->integer('jenis_cicilan_id')) ?? $plans->first();
        $selectedInsurance = $request->filled('asuransi_id')
            ? $insurances->firstWhere('id', $request->integer('asuransi_id'))
            : null;

        $defaultDp = $selectedMotor ? (int) round($selectedMotor->harga_jual * 0.2) : 0;
        $dp = max(0, $request->integer('dp', $defaultDp));

        $simulation = null;
        $simulationError = null;

        if ($selectedMotor && $selectedPlan) {
            try {
                $simulation = $this->simulationService->calculate($selectedMotor, $selectedPlan, $selectedInsurance, $dp);
            } catch (ValidationException $exception) {
                $simulationError = collect($exception->errors())->flatten()->first() ?: 'Simulasi belum dapat diproses.';
            }
        }

        return view('marketing.simulasi.index', [
            'motors' => $motors,
            'plans' => $plans,
            'insurances' => $insurances,
            'selectedMotor' => $selectedMotor,
            'selectedPlan' => $selectedPlan,
            'selectedInsurance' => $selectedInsurance,
            'dp' => $dp,
            'defaultDp' => $defaultDp,
            'simulation' => $simulation,
            'simulationError' => $simulationError,
        ]);
    }
}

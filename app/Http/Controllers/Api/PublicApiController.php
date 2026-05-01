<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asuransi;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\JenisCicilan;
use App\Models\Motor;
use App\Services\CreditSimulationService;
use Illuminate\Http\Request;

class PublicApiController extends Controller
{
    public function __construct(
        private readonly CreditSimulationService $simulationService,
    ) {
    }

    public function motors(Request $request)
    {
        $query = Motor::with(['jenisMotor', 'images'])
            ->where('status_aktif', true)
            ->when($request->filled('search'), function ($builder) use ($request) {
                $search = $request->string('search')->toString();
                $builder->where(function ($inner) use ($search) {
                    $inner->where('nama_motor', 'like', '%'.$search.'%')
                        ->orWhere('merk', 'like', '%'.$search.'%');
                });
            })
            ->when($request->filled('merk'), fn ($builder) => $builder->where('merk', $request->string('merk')->toString()))
            ->when($request->filled('jenis_motor_id'), fn ($builder) => $builder->where('jenis_motor_id', $request->integer('jenis_motor_id')))
            ->when($request->filled('min_price'), fn ($builder) => $builder->where('harga_jual', '>=', $request->integer('min_price')))
            ->when($request->filled('max_price'), fn ($builder) => $builder->where('harga_jual', '<=', $request->integer('max_price')))
            ->when($request->filled('warna'), fn ($builder) => $builder->where('warna', 'like', '%'.$request->string('warna')->toString().'%'));

        match ($request->string('sort')->toString()) {
            'termurah' => $query->orderBy('harga_jual'),
            'termahal' => $query->orderByDesc('harga_jual'),
            'terbaru' => $query->latest(),
            default => $query->orderBy('nama_motor'),
        };

        $motors = $query->paginate(9);
        $motors->setCollection($motors->getCollection()->map(fn (Motor $motor) => $this->formatMotor($motor)));

        return response()->json($motors);
    }

    public function featuredMotors()
    {
        $motors = Motor::with(['jenisMotor', 'images'])
            ->where('status_aktif', true)
            ->where('is_featured', true)
            ->take(8)
            ->get()
            ->map(fn (Motor $motor) => $this->formatMotor($motor));

        return response()->json($motors);
    }

    public function showMotor(Motor $motor)
    {
        abort_unless($motor->status_aktif, 404);

        $motor->load(['jenisMotor', 'images']);

        return response()->json([
            ...$motor->toArray(),
            'starting_installment' => $this->formatMotor($motor)['starting_installment'],
        ]);
    }

    public function installmentOptions()
    {
        return response()->json(JenisCicilan::orderBy('durasi_bulan')->get());
    }

    public function insuranceOptions()
    {
        return response()->json(Asuransi::orderBy('nama_asuransi')->get());
    }

    public function simulation(Request $request)
    {
        $validated = $request->validate([
            'motor_id' => ['required', 'exists:motors,id'],
            'dp' => ['required', 'integer', 'min:0'],
            'jenis_cicilan_id' => ['required', 'exists:jenis_cicilan,id'],
            'asuransi_id' => ['nullable', 'exists:asuransi,id'],
        ]);

        $motor = Motor::findOrFail($validated['motor_id']);
        $plan = JenisCicilan::findOrFail($validated['jenis_cicilan_id']);
        $insurance = ! empty($validated['asuransi_id']) ? Asuransi::findOrFail($validated['asuransi_id']) : null;

        return $this->apiResponse(
            $this->simulationService->calculate($motor, $plan, $insurance, (int) $validated['dp']),
            'Simulasi berhasil dihitung.'
        );
    }

    public function faqs()
    {
        return response()->json(Faq::where('is_active', true)->orderBy('sort_order')->get());
    }

    public function contact(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        ContactMessage::create($validated);

        return $this->apiResponse([], 'Pesan berhasil dikirim.', 201);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatMotor(Motor $motor): array
    {
        $lowestPlan = JenisCicilan::orderBy('durasi_bulan')->first();
        $startingInstallment = null;

        if ($lowestPlan) {
            $simulation = $this->simulationService->calculate(
                $motor,
                $lowestPlan,
                null,
                (int) round($motor->harga_jual * 0.2)
            );

            $startingInstallment = $simulation['angsuran_per_bulan'];
        }

        return [
            ...$motor->toArray(),
            'starting_installment' => $startingInstallment,
        ];
    }
}

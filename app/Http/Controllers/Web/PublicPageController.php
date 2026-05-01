<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Asuransi;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\JenisCicilan;
use App\Models\JenisMotor;
use App\Models\Motor;
use App\Models\Testimonial;
use App\Services\CreditSimulationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PublicPageController extends Controller
{
    public function __construct(
        private readonly CreditSimulationService $simulationService,
    ) {
    }

    public function home(): View
    {
        $featuredMotors = $this->decorateMotors(
            Motor::with(['jenisMotor', 'images'])
                ->where('status_aktif', true)
                ->where('is_featured', true)
                ->take(4)
                ->get()
        );

        return view('public.home', [
            'featuredMotors' => $featuredMotors,
            'installmentOptions' => JenisCicilan::orderBy('durasi_bulan')->get(),
            'insuranceOptions' => Asuransi::orderBy('nama_asuransi')->get(),
            'faqs' => Faq::where('is_active', true)->orderBy('sort_order')->take(4)->get(),
            'testimonials' => Testimonial::where('is_featured', true)->take(3)->get(),
        ]);
    }

    public function about(): View
    {
        return view('public.about');
    }

    public function motors(Request $request): View
    {
        $query = Motor::with('jenisMotor')
            ->where('status_aktif', true)
            ->when($request->string('search')->isNotEmpty(), function ($builder) use ($request) {
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

        $motors = $query->paginate(9)->withQueryString();
        $motors->setCollection($this->decorateMotors($motors->getCollection()));

        return view('public.motors.index', [
            'motors' => $motors,
            'jenisMotors' => JenisMotor::orderBy('jenis')->get(),
            'brands' => Motor::query()->distinct()->orderBy('merk')->pluck('merk'),
            'filters' => $request->only(['search', 'merk', 'jenis_motor_id', 'min_price', 'max_price', 'warna', 'sort']),
        ]);
    }

    public function showMotor(Motor $motor): View
    {
        abort_unless($motor->status_aktif, 404);

        $motor->load(['jenisMotor', 'images']);
        $plans = JenisCicilan::orderBy('durasi_bulan')->get();
        $insurances = Asuransi::orderBy('nama_asuransi')->get();
        $defaultPlan = $plans->first();
        $simulation = $defaultPlan
            ? $this->simulationService->calculate($motor, $defaultPlan, $insurances->first(), (int) round($motor->harga_jual * 0.2))
            : null;

        return view('public.motors.show', [
            'motor' => $motor,
            'plans' => $plans,
            'insurances' => $insurances,
            'simulation' => $simulation,
        ]);
    }

    public function simulation(Request $request): View
    {
        $motors = Motor::where('status_aktif', true)->orderBy('nama_motor')->get();
        $plans = JenisCicilan::orderBy('durasi_bulan')->get();
        $insurances = Asuransi::orderBy('nama_asuransi')->get();

        $selectedMotor = $motors->firstWhere('id', $request->integer('motor_id')) ?? $motors->first();
        $selectedPlan = $plans->firstWhere('id', $request->integer('jenis_cicilan_id')) ?? $plans->first();
        $selectedInsurance = $insurances->firstWhere('id', $request->integer('asuransi_id')) ?? $insurances->first();
        $defaultDp = $selectedMotor ? (int) round($selectedMotor->harga_jual * 0.2) : 0;

        return view('public.simulation', [
            'motors' => $motors,
            'plans' => $plans,
            'insurances' => $insurances,
            'selectedMotor' => $selectedMotor,
            'selectedPlan' => $selectedPlan,
            'selectedInsurance' => $selectedInsurance,
            'defaultDp' => $request->integer('dp', $defaultDp),
        ]);
    }

    public function howToApply(): View
    {
        return view('public.how-to-apply');
    }

    public function faq(): View
    {
        return view('public.faq', [
            'faqs' => Faq::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function contact(): View
    {
        return view('public.contact');
    }

    public function sendContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        ContactMessage::create($validated);

        return back()->with('status', 'Pesan berhasil dikirim. Tim Credira akan segera menghubungi Anda.');
    }

    /**
     * @param  iterable<int, Motor>  $motors
     * @return \Illuminate\Support\Collection<int, Motor>
     */
    private function decorateMotors(iterable $motors)
    {
        $plan = JenisCicilan::orderBy('durasi_bulan')->first();

        return collect($motors)->map(function (Motor $motor) use ($plan) {
            $motor->starting_installment = null;

            if ($plan) {
                $simulation = $this->simulationService->calculate(
                    $motor,
                    $plan,
                    null,
                    (int) round($motor->harga_jual * 0.2)
                );

                $motor->starting_installment = $simulation['angsuran_per_bulan'];
            }

            return $motor;
        });
    }
}

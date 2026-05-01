<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMotorRequest;
use App\Http\Requests\Admin\UpdateMotorRequest;
use App\Models\JenisMotor;
use App\Models\Motor;
use App\Services\AdminMotorService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MotorController extends Controller
{
    public function __construct(
        private readonly AdminMotorService $motorService,
    ) {
    }

    public function index(Request $request): View
    {
        $motors = Motor::query()
            ->with(['jenisMotor', 'images'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = trim($request->string('q')->toString());

                $query->where(function ($builder) use ($keyword) {
                    $builder
                        ->where('nama_motor', 'like', '%'.$keyword.'%')
                        ->orWhere('merk', 'like', '%'.$keyword.'%');
                });
            })
            ->when($request->filled('jenis_motor_id'), fn ($query) => $query->where('jenis_motor_id', $request->integer('jenis_motor_id')))
            ->when($request->filled('status_aktif'), function ($query) use ($request) {
                $value = $request->string('status_aktif')->toString();
                if ($value === '1' || $value === '0') {
                    $query->where('status_aktif', $value === '1');
                }
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.motors.index', [
            'motors' => $motors,
            'jenisMotors' => JenisMotor::query()->orderBy('merk')->orderBy('jenis')->get(),
            'filters' => $request->only(['q', 'jenis_motor_id', 'status_aktif']),
            'summary' => [
                'total' => Motor::count(),
                'active' => Motor::where('status_aktif', true)->count(),
                'featured' => Motor::where('is_featured', true)->count(),
                'stock' => (int) Motor::sum('stok'),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.motors.create', [
            'jenisMotors' => JenisMotor::query()->orderBy('merk')->orderBy('jenis')->get(),
        ]);
    }

    public function store(StoreMotorRequest $request): RedirectResponse
    {
        $motor = $this->motorService->store($request->validated() + $request->allFiles());

        return redirect()
            ->route('admin.motors.show', $motor)
            ->with('status', 'Data motor berhasil ditambahkan.');
    }

    public function show(Motor $motor): View
    {
        return view('admin.motors.show', [
            'motor' => $motor->load(['jenisMotor', 'images']),
        ]);
    }

    public function edit(Motor $motor): View
    {
        return view('admin.motors.edit', [
            'motor' => $motor->load(['jenisMotor', 'images']),
            'jenisMotors' => JenisMotor::query()->orderBy('merk')->orderBy('jenis')->get(),
        ]);
    }

    public function update(UpdateMotorRequest $request, Motor $motor): RedirectResponse
    {
        $this->motorService->update($motor, $request->validated() + $request->allFiles());

        return redirect()
            ->route('admin.motors.edit', $motor)
            ->with('status', 'Data motor berhasil diperbarui.');
    }
}

<?php

namespace App\Http\Controllers\Web\Marketing;

use App\Http\Controllers\Controller;
use App\Models\JenisMotor;
use App\Models\Motor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MotorController extends Controller
{
    public function index(Request $request): View
    {
        $query = Motor::query()
            ->with(['jenisMotor', 'images'])
            ->active()
            ->when($request->filled('q'), function ($builder) use ($request) {
                $search = trim($request->string('q')->toString());

                $builder->where(function ($motorQuery) use ($search) {
                    $motorQuery
                        ->where('nama_motor', 'like', '%'.$search.'%')
                        ->orWhere('merk', 'like', '%'.$search.'%');
                });
            })
            ->when($request->filled('jenis_motor_id'), fn ($builder) => $builder->where('jenis_motor_id', $request->integer('jenis_motor_id')))
            ->when($request->filled('merk'), fn ($builder) => $builder->where('merk', $request->string('merk')->toString()))
            ->orderByDesc('is_featured')
            ->orderBy('nama_motor');

        return view('marketing.motors.index', [
            'motors' => $query->paginate(9)->withQueryString(),
            'jenisMotors' => JenisMotor::orderBy('jenis')->get(),
            'brands' => Motor::query()->active()->select('merk')->distinct()->orderBy('merk')->pluck('merk'),
            'filters' => $request->only(['q', 'jenis_motor_id', 'merk']),
        ]);
    }

    public function show(Motor $motor): View
    {
        abort_unless($motor->status_aktif, 404);

        return view('marketing.motors.show', [
            'motor' => $motor->load(['jenisMotor', 'images']),
            'relatedMotors' => Motor::query()
                ->with(['jenisMotor', 'images'])
                ->active()
                ->whereKeyNot($motor->id)
                ->where('jenis_motor_id', $motor->jenis_motor_id)
                ->orderByDesc('is_featured')
                ->take(3)
                ->get(),
        ]);
    }
}

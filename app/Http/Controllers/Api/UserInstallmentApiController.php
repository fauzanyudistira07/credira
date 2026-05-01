<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Angsuran;
use Illuminate\Http\Request;

class UserInstallmentApiController extends Controller
{
    public function index(Request $request)
    {
        $installments = Angsuran::with('application.motor')
            ->whereHas('application', fn ($builder) => $builder->where('pelanggan_id', $this->currentPelanggan()->id))
            ->when($request->filled('status'), fn ($builder) => $builder->where('status_pembayaran', $request->string('status')->toString()))
            ->orderBy('tanggal_jatuh_tempo')
            ->paginate(12);

        return response()->json($installments);
    }

    public function show(Angsuran $installment)
    {
        abort_unless($installment->application->pelanggan_id === $this->currentPelanggan()->id, 404);

        return $this->apiResponse([
            'installment' => $installment->load('application.motor', 'payments'),
        ], 'Detail angsuran berhasil dimuat.');
    }
}

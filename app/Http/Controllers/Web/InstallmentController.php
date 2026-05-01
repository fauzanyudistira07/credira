<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Angsuran;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class InstallmentController extends Controller
{
    public function index(Request $request): View
    {
        $installments = Angsuran::with('application.motor')
            ->whereHas('application', fn ($builder) => $builder->where('pelanggan_id', $this->currentPelanggan()->id))
            ->when($request->filled('status'), fn ($builder) => $builder->where('status_pembayaran', $request->string('status')->toString()))
            ->orderBy('tanggal_jatuh_tempo')
            ->paginate(12)
            ->withQueryString();

        return view('user.installments.index', [
            'installments' => $installments,
            'currentStatus' => $request->string('status')->toString(),
        ]);
    }

    public function show(Angsuran $installment): View
    {
        abort_unless($installment->application->pelanggan_id === $this->currentPelanggan()->id, 404);

        return view('user.installments.show', [
            'installment' => $installment->load('application.motor', 'payments.metodeBayar'),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class UserPaymentApiController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {
    }

    public function index()
    {
        return response()->json(
            $this->currentPelanggan()->payments()
                ->with('installment.application.motor')
                ->latest()
                ->paginate(10)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'angsuran_id' => ['required', 'exists:angsuran,id'],
        ]);

        $payment = $this->paymentService->create($this->currentPelanggan(), $validated);

        return $this->apiResponse([
            'payment' => $payment,
            'midtrans_redirect_url' => $payment->midtrans_redirect_url,
        ], 'Pembayaran berhasil dibuat.', 201);
    }

    public function show(Pembayaran $payment)
    {
        abort_unless($payment->pelanggan_id === $this->currentPelanggan()->id, 404);

        return $this->apiResponse([
            'payment' => $payment->load('installment.application.motor'),
        ], 'Detail pembayaran berhasil dimuat.');
    }
}

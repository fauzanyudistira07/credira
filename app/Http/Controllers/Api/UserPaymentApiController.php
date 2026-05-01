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
            'nominal_bayar' => ['required', 'integer', 'min:0'],
            'tanggal_bayar' => ['required', 'date'],
            'metode_bayar' => ['required', 'string', 'max:100'],
            'nama_bank_pengirim' => ['nullable', 'string', 'max:100'],
            'nama_pemilik_rekening' => ['nullable', 'string', 'max:255'],
            'bukti_bayar' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $payment = $this->paymentService->create($this->currentPelanggan(), $validated);

        return $this->apiResponse([
            'payment' => $payment,
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

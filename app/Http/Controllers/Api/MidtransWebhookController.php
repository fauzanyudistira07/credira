<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Services\MidtransService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransWebhookController extends Controller
{
    public function __construct(
        protected MidtransService $midtransService,
        protected PaymentService $paymentService
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->all();

        if (! $this->midtransService->verifyNotificationSignature($payload)) {
            return response()->json([
                'message' => 'Invalid signature',
            ], 403);
        }

        $orderId = (string) ($payload['order_id'] ?? '');
        if ($orderId === '') {
            return response()->json([
                'message' => 'order_id is required',
            ], 422);
        }

        $payment = Pembayaran::query()
            ->where('midtrans_order_id', $orderId)
            ->orWhere('kode_pembayaran', $orderId)
            ->latest()
            ->first();

        if (! $payment) {
            return response()->json([
                'message' => 'payment not found',
            ], 404);
        }

        $this->paymentService->syncFromMidtransWebhook($payment, $payload);

        return response()->json([
            'message' => 'ok',
        ]);
    }
}

<?php

namespace App\Services;

use App\Models\Pembayaran;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class MidtransService
{
    public function createSnapTransaction(Pembayaran $payment, int $amount, string $orderId): array
    {
        $baseUrl = $this->isProduction()
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';

        $serverKey = $this->serverKey();

        if ($serverKey === '') {
            throw new RuntimeException('MIDTRANS_SERVER_KEY belum diisi.');
        }

        $payment->loadMissing(['pelanggan.user', 'installment.application.motor']);

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => Str::limit($payment->pelanggan?->nama_lengkap ?? 'Customer', 50, ''),
                'email' => $payment->pelanggan?->email ?: $payment->pelanggan?->user?->email,
                'phone' => $payment->pelanggan?->no_telp,
            ],
            'item_details' => [[
                'id' => 'angsuran-'.$payment->angsuran_id,
                'price' => $amount,
                'quantity' => 1,
                'name' => Str::limit(
                    'Angsuran ke-'.$payment->installment?->angsuran_ke.' '.$payment->installment?->application?->motor?->nama_motor,
                    50,
                    ''
                ),
            ]],
        ];

        if ((bool) config('services.midtrans.use_finish_callback', true)) {
            $finishPath = route('user.payments.midtrans.finish', absolute: false);
            $request = request();
            $finishUrl = $request
                ? rtrim($request->getSchemeAndHttpHost(), '/').$finishPath
                : route('user.payments.midtrans.finish');

            $payload['callbacks'] = [
                'finish' => $finishUrl,
            ];
        }

        $request = Http::acceptJson()
            ->asJson()
            ->withBasicAuth($serverKey, '')
            ->withOptions([
                'verify' => (bool) config('services.midtrans.verify_ssl', true),
            ]);

        $overrideNotificationUrl = trim((string) config('services.midtrans.notification_url', ''));
        if ($overrideNotificationUrl !== '') {
            $request = $request->withHeaders([
                'X-Override-Notification' => $overrideNotificationUrl,
            ]);
        }

        $response = $request->post($baseUrl.'/snap/v1/transactions', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Gagal membuat transaksi Midtrans: '.$response->body());
        }

        $json = $response->json();

        return [
            'token' => (string) ($json['token'] ?? ''),
            'redirect_url' => (string) ($json['redirect_url'] ?? ''),
            'payload' => $json,
        ];
    }

    public function verifyNotificationSignature(array $payload): bool
    {
        $signature = (string) ($payload['signature_key'] ?? '');
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');

        if ($signature === '' || $orderId === '' || $statusCode === '' || $grossAmount === '') {
            return false;
        }

        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey());

        return hash_equals($expected, $signature);
    }

    public function getTransactionStatus(string $orderId): array
    {
        $orderId = trim($orderId);
        if ($orderId === '') {
            throw new RuntimeException('Order ID Midtrans kosong.');
        }

        $baseUrl = $this->isProduction()
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';

        $response = Http::acceptJson()
            ->withBasicAuth($this->serverKey(), '')
            ->withOptions([
                'verify' => (bool) config('services.midtrans.verify_ssl', true),
            ])
            ->get($baseUrl.'/v2/'.$orderId.'/status');

        $json = (array) $response->json();

        if (! $response->successful() && ! isset($json['status_code'])) {
            throw new RuntimeException('Gagal cek status Midtrans: '.$response->body());
        }

        return $json;
    }

    public function mapMidtransStatusToVerificationStatus(array $payload): string
    {
        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = (string) ($payload['fraud_status'] ?? '');

        if ($transactionStatus === 'settlement') {
            return 'valid';
        }

        if ($transactionStatus === 'capture') {
            return $fraudStatus === 'challenge' ? 'pending' : 'valid';
        }

        if ($transactionStatus === 'authorize') {
            return 'valid';
        }

        if ($transactionStatus === 'pending') {
            return 'pending';
        }

        if (in_array($transactionStatus, ['cancel', 'deny', 'expire', 'failure'], true)) {
            return 'ditolak';
        }

        if (in_array($transactionStatus, ['refund', 'partial_refund', 'chargeback', 'partial_chargeback'], true)) {
            return 'ditolak';
        }

        return 'pending';
    }

    public function generateOrderId(int $installmentId, int $paymentId): string
    {
        return 'MID-'.$installmentId.'-'.$paymentId.'-'.now()->format('YmdHis');
    }

    protected function serverKey(): string
    {
        $key = (string) config('services.midtrans.server_key');

        if ($key === '' || str_contains($key, 'REPLACE_WITH_YOUR_SANDBOX_SERVER_KEY')) {
            throw new RuntimeException('MIDTRANS_SERVER_KEY masih placeholder. Isi dengan Server Key asli dari Midtrans MAP.');
        }

        return $key;
    }

    protected function isProduction(): bool
    {
        return (bool) config('services.midtrans.is_production', false);
    }
}

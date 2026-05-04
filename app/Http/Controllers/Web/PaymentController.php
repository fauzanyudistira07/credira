<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Angsuran;
use App\Models\Pembayaran;
use App\Services\MidtransService;
use App\Services\PaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly MidtransService $midtransService,
    ) {
    }

    public function index(): View
    {
        $payments = $this->currentPelanggan()->payments()
            ->with('installment.application.motor')
            ->latest()
            ->paginate(10);

        return view('user.payments.index', [
            'payments' => $payments,
        ]);
    }

    public function create(Request $request): View
    {
        $installments = Angsuran::with('application.motor')
            ->whereHas('application', fn ($builder) => $builder->where('pelanggan_id', $this->currentPelanggan()->id))
            ->whereIn('status_pembayaran', ['belum_bayar', 'telat', 'gagal_verifikasi'])
            ->orderBy('tanggal_jatuh_tempo')
            ->get();

        return view('user.payments.create', [
            'installments' => $installments,
            'selectedInstallment' => $installments->firstWhere('id', $request->integer('installment')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'angsuran_id' => ['required', 'exists:angsuran,id'],
        ]);

        $payment = $this->paymentService->create($this->currentPelanggan(), $validated);

        if (! $payment->midtrans_redirect_url) {
            return redirect()
                ->route('user.payments.show', $payment)
                ->with('status', 'Sesi Midtrans belum tersedia. Silakan refresh status beberapa saat lagi.');
        }

        return redirect()->away($payment->midtrans_redirect_url);
    }

    public function show(Pembayaran $payment): View
    {
        abort_unless($payment->pelanggan_id === $this->currentPelanggan()->id, 404);

        if ($payment->status_verifikasi === 'pending' && $payment->midtrans_order_id) {
            try {
                $payment = $this->paymentService->syncFromMidtransGateway($payment, 6, 500);
            } catch (\Throwable) {
                // Keep page accessible even if Midtrans sync fails.
            }
        }

        return view('user.payments.show', [
            'payment' => $payment->load('installment.application.motor', 'metodeBayar'),
            'midtransUrl' => $payment->status_verifikasi === 'pending' ? $payment->midtrans_redirect_url : null,
        ]);
    }

    public function midtransFinish(Request $request): RedirectResponse
    {
        $orderId = trim((string) $request->query('order_id'));

        if ($orderId === '') {
            return redirect()
                ->route('user.payments.index')
                ->with('status', 'Kembali dari Midtrans. Status pembayaran akan diperbarui otomatis.');
        }

        $payment = Pembayaran::query()
            ->where('pelanggan_id', $this->currentPelanggan()->id)
            ->where(function ($query) use ($orderId) {
                $query->where('midtrans_order_id', $orderId)
                    ->orWhere('kode_pembayaran', $orderId);
            })
            ->latest()
            ->first();

        if (! $payment) {
            return redirect()
                ->route('user.payments.index')
                ->with('status', 'Kembali dari Midtrans. Status pembayaran akan diperbarui otomatis.');
        }

        if ($payment->status_verifikasi === 'pending' && $payment->midtrans_order_id) {
            try {
                $payment = $this->paymentService->syncFromMidtransGateway($payment, 20, 1000);
            } catch (\Throwable) {
                // Keep normal redirect flow even if status sync fails.
            }
        }

        if (
            $payment->status_verifikasi === 'pending'
            && $payment->midtrans_order_id
            && (bool) config('services.midtrans.local_simulator', false)
        ) {
            $resultData = $request->query('result_data');
            $resultPayload = [];
            if (is_string($resultData) && $resultData !== '') {
                $decoded = json_decode($resultData, true);
                if (is_array($decoded)) {
                    $resultPayload = $decoded;
                }
            }

            $statusCode = (string) ($request->query('status_code', $resultPayload['status_code'] ?? ''));
            $transactionStatus = (string) ($request->query('transaction_status', $resultPayload['transaction_status'] ?? ''));

            if ($transactionStatus === '' && $statusCode === '200') {
                $transactionStatus = 'settlement';
            }

            $fallbackPayload = [
                'order_id' => $orderId,
                'status_code' => $statusCode,
                'transaction_status' => $transactionStatus,
                'fraud_status' => (string) $request->query('fraud_status', $resultPayload['fraud_status'] ?? ''),
                'transaction_id' => (string) $request->query('transaction_id', $resultPayload['transaction_id'] ?? ''),
                'payment_type' => (string) $request->query('payment_type', $resultPayload['payment_type'] ?? ''),
            ];

            $mappedStatus = $this->midtransService->mapMidtransStatusToVerificationStatus($fallbackPayload);
            if ($mappedStatus === 'valid') {
                $payment = $this->paymentService->syncFromMidtransWebhook($payment, $fallbackPayload);
            }
        }

        $statusMessage = $payment->fresh()->status_verifikasi === 'valid'
            ? 'Pembayaran Midtrans sukses dan sudah dikonfirmasi otomatis.'
            : 'Kembali dari Midtrans. Status pembayaran akan diperbarui otomatis.';

        return redirect()
            ->route('user.payments.show', $payment)
            ->with('status', $statusMessage);
    }

    public function refreshMidtransStatus(Pembayaran $payment): RedirectResponse
    {
        abort_unless($payment->pelanggan_id === $this->currentPelanggan()->id, 404);

        if (! $payment->midtrans_order_id) {
            return redirect()
                ->route('user.payments.show', $payment)
                ->with('status', 'Refresh status hanya tersedia untuk transaksi Midtrans.');
        }

        try {
            $this->paymentService->syncFromMidtransGateway($payment, 8, 500);

            return redirect()
                ->route('user.payments.show', $payment)
                ->with('status', 'Status Midtrans berhasil diperbarui.');
        } catch (\Throwable $exception) {
            return redirect()
                ->route('user.payments.show', $payment)
                ->with('status', 'Gagal refresh status Midtrans: '.$exception->getMessage());
        }
    }

    public function receipt(Pembayaran $payment): View
    {
        abort_unless($payment->pelanggan_id === $this->currentPelanggan()->id, 404);

        return view('user.payments.receipt', [
            'payment' => $payment->load('installment.application.motor', 'metodeBayar', 'verifier'),
        ]);
    }
}

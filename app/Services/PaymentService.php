<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\InstallmentPaymentStatus;
use App\Enums\PaymentVerificationStatus;
use App\Models\Angsuran;
use App\Models\Notification;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\PengajuanLog;
use App\Support\LegacyDiagramSync;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        private readonly MidtransService $midtransService,
    ) {
    }

    public function create(Pelanggan $pelanggan, array $data): Pembayaran
    {
        $installment = Angsuran::with(['application.pelanggan'])->findOrFail($data['angsuran_id']);

        if ($installment->application->pelanggan_id !== $pelanggan->id) {
            throw ValidationException::withMessages([
                'angsuran_id' => 'Angsuran tidak dimiliki user.',
            ]);
        }

        if (! in_array($installment->status_pembayaran, [
            InstallmentPaymentStatus::BelumBayar->value,
            InstallmentPaymentStatus::Telat->value,
            InstallmentPaymentStatus::GagalVerifikasi->value,
            InstallmentPaymentStatus::MenungguVerifikasi->value,
        ], true)) {
            throw ValidationException::withMessages([
                'angsuran_id' => 'Status angsuran tidak bisa diproses untuk pembayaran.',
            ]);
        }

        return DB::transaction(function () use ($pelanggan, $installment) {
            Pembayaran::query()
                ->where('angsuran_id', $installment->id)
                ->where('status_verifikasi', PaymentVerificationStatus::Pending->value)
                ->update([
                    'status_verifikasi' => PaymentVerificationStatus::Ditolak->value,
                    'catatan_verifikasi' => 'Transaksi Midtrans baru dibuat. Transaksi pending sebelumnya ditutup otomatis.',
                    'verified_by' => null,
                    'verified_at' => now(),
                ]);

            $payload = [
                'angsuran_id' => $installment->id,
                'pelanggan_id' => $pelanggan->id,
                'kode_pembayaran' => $this->generateCode(),
                'metode_bayar' => 'Midtrans Snap',
                'nama_bank_pengirim' => null,
                'nama_pemilik_rekening' => null,
                'nominal_bayar' => (int) $installment->total_tagihan,
                'tanggal_bayar' => now()->toDateString(),
                'bukti_bayar' => 'midtrans://snap',
                'status_verifikasi' => PaymentVerificationStatus::Pending->value,
                'catatan_verifikasi' => 'Tagihan dibuat dan menunggu pembayaran melalui Midtrans.',
                'verified_by' => null,
                'verified_at' => null,
            ];

            LegacyDiagramSync::syncPaymentMethodFields($payload, null);

            $payment = Pembayaran::create($payload);

            $installment->update([
                'status_pembayaran' => InstallmentPaymentStatus::MenungguVerifikasi->value,
                'tanggal_bayar' => null,
                'metode_bayar' => 'Midtrans Snap',
                'tgl_bayar' => null,
                'id_kredit' => $installment->pengajuan_id,
                'total_bayar' => $installment->total_tagihan,
                'keterangan' => 'Menunggu pembayaran user via Midtrans Snap.',
                'verified_by' => null,
                'verified_at' => null,
            ]);

            LegacyDiagramSync::syncInstallment($installment->fresh());

            Notification::create([
                'user_id' => $pelanggan->user_id,
                'title' => 'Tagihan pembayaran dibuat',
                'message' => 'Pembayaran '.$payment->kode_pembayaran.' menunggu penyelesaian via Midtrans.',
                'type' => 'payment',
                'reference_type' => 'pembayaran',
                'reference_id' => $payment->id,
            ]);

            return $this->bootstrapMidtransPayment($payment);
        });
    }

    public function syncFromMidtransWebhook(Pembayaran $payment, array $payload): Pembayaran
    {
        $nextStatus = PaymentVerificationStatus::from($this->midtransService->mapMidtransStatusToVerificationStatus($payload));

        $payment->update([
            'midtrans_order_id' => (string) ($payload['order_id'] ?? $payment->midtrans_order_id),
            'midtrans_transaction_id' => (string) ($payload['transaction_id'] ?? $payment->midtrans_transaction_id),
            'midtrans_payment_type' => (string) ($payload['payment_type'] ?? $payment->midtrans_payment_type),
            'midtrans_status_code' => (string) ($payload['status_code'] ?? $payment->midtrans_status_code),
            'midtrans_payload' => $payload,
        ]);

        if ($nextStatus === PaymentVerificationStatus::Pending) {
            return $payment->fresh(['installment.application.motor', 'metodeBayar']);
        }

        $settledAt = $this->resolveSettlementDate($payload);

        DB::transaction(function () use ($payment, $nextStatus, $settledAt) {
            $note = $nextStatus === PaymentVerificationStatus::Valid
                ? 'Pembayaran berhasil dikonfirmasi otomatis oleh Midtrans.'
                : 'Pembayaran ditandai gagal/dibatalkan berdasarkan status Midtrans.';

            $payment->update([
                'status_verifikasi' => $nextStatus->value,
                'catatan_verifikasi' => $note,
                'tanggal_bayar' => $settledAt->toDateString(),
                'verified_by' => null,
                'verified_at' => now(),
            ]);

            $installment = $payment->installment()->with('application')->firstOrFail();

            $installmentPayload = [
                'metode_bayar' => 'Midtrans Snap',
                'keterangan' => $note,
                'verified_by' => null,
                'verified_at' => now(),
            ];

            if ($nextStatus === PaymentVerificationStatus::Valid) {
                $installmentPayload['status_pembayaran'] = InstallmentPaymentStatus::SudahBayar->value;
                $installmentPayload['tanggal_bayar'] = $settledAt;
                $installmentPayload['tgl_bayar'] = $settledAt->toDateString();
            } else {
                $installmentPayload['status_pembayaran'] = InstallmentPaymentStatus::GagalVerifikasi->value;
                $installmentPayload['tanggal_bayar'] = null;
                $installmentPayload['tgl_bayar'] = null;
            }

            $installment->update($installmentPayload);
            LegacyDiagramSync::syncInstallment($installment->fresh());

            if (
                $nextStatus === PaymentVerificationStatus::Valid
                && $installment->application
                && $installment->application->installments()
                    ->where('status_pembayaran', '!=', InstallmentPaymentStatus::SudahBayar->value)
                    ->count() === 0
                && $installment->application->status_pengajuan !== ApplicationStatus::Selesai->value
            ) {
                $oldStatus = $installment->application->status_pengajuan;
                $completionNote = 'Semua angsuran lunas otomatis melalui Midtrans. Status pengajuan selesai.';

                $installment->application->update([
                    'status_pengajuan' => ApplicationStatus::Selesai->value,
                    'catatan_status' => $completionNote,
                    'keterangan_status_pengajuan' => $completionNote,
                ]);

                LegacyDiagramSync::syncPengajuan($installment->application->fresh(['jenisCicilan']));

                PengajuanLog::create([
                    'pengajuan_id' => $installment->application->id,
                    'status_lama' => $oldStatus,
                    'status_baru' => ApplicationStatus::Selesai->value,
                    'catatan' => $completionNote,
                    'changed_by' => null,
                ]);
            }

            Notification::create([
                'user_id' => $payment->pelanggan->user_id,
                'title' => 'Status pembayaran diperbarui',
                'message' => 'Pembayaran '.$payment->kode_pembayaran.' sekarang berstatus '.$nextStatus->label().'.',
                'type' => 'payment',
                'reference_type' => 'pembayaran',
                'reference_id' => $payment->id,
            ]);
        });

        return $payment->fresh(['installment.application.motor', 'metodeBayar']);
    }

    public function syncFromMidtransGateway(Pembayaran $payment, int $maxAttempts = 1, int $delayMilliseconds = 0): Pembayaran
    {
        $orderId = (string) ($payment->midtrans_order_id ?: $payment->kode_pembayaran);

        if ($orderId === '') {
            return $payment->fresh(['installment.application.motor', 'metodeBayar']);
        }

        $attempts = max($maxAttempts, 1);
        $delayMicroseconds = max($delayMilliseconds, 0) * 1000;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                $payload = $this->midtransService->getTransactionStatus($orderId);
                $synced = $this->syncFromMidtransWebhook($payment, $payload);
            } catch (\Throwable) {
                if ($attempt < $attempts && $delayMicroseconds > 0) {
                    usleep($delayMicroseconds);
                }

                continue;
            }

            if ($synced->status_verifikasi !== PaymentVerificationStatus::Pending->value) {
                return $synced;
            }

            $payment = $synced->fresh();

            if ($attempt < $attempts && $delayMicroseconds > 0) {
                usleep($delayMicroseconds);
            }
        }

        return $payment->fresh(['installment.application.motor', 'metodeBayar']);
    }

    protected function bootstrapMidtransPayment(Pembayaran $payment): Pembayaran
    {
        $amount = (int) round((float) $payment->nominal_bayar);
        $orderId = $this->midtransService->generateOrderId($payment->angsuran_id, $payment->id);

        try {
            $result = $this->midtransService->createSnapTransaction($payment, $amount, $orderId);
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'payment' => ['Gagal membuat transaksi Midtrans: '.$exception->getMessage()],
            ]);
        }

        $payment->update([
            'midtrans_order_id' => $orderId,
            'midtrans_snap_token' => $result['token'],
            'midtrans_redirect_url' => $result['redirect_url'],
            'midtrans_payload' => $result['payload'],
            'midtrans_status_code' => '201',
        ]);

        return $payment->fresh(['installment.application.motor', 'metodeBayar']);
    }

    protected function resolveSettlementDate(array $payload): Carbon
    {
        $settlement = $payload['settlement_time'] ?? $payload['transaction_time'] ?? null;

        if (! $settlement) {
            return now();
        }

        try {
            return Carbon::parse((string) $settlement);
        } catch (\Throwable) {
            return now();
        }
    }

    private function generateCode(): string
    {
        $sequence = str_pad((string) (Pembayaran::count() + 1), 3, '0', STR_PAD_LEFT);

        return 'PAY-'.now()->format('Ymd').'-'.$sequence;
    }
}

<?php

namespace App\Services;

use App\Enums\InstallmentPaymentStatus;
use App\Enums\PaymentVerificationStatus;
use App\Models\Angsuran;
use App\Models\MetodeBayar;
use App\Models\Notification;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Support\LegacyDiagramSync;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function create(Pelanggan $pelanggan, array $data): Pembayaran
    {
        $installment = Angsuran::with('application')->findOrFail($data['angsuran_id']);

        if ($installment->application->pelanggan_id !== $pelanggan->id) {
            throw ValidationException::withMessages([
                'angsuran_id' => 'Angsuran tidak dimiliki user.',
            ]);
        }

        if ((int) $data['nominal_bayar'] !== (int) $installment->total_tagihan) {
            throw ValidationException::withMessages([
                'nominal_bayar' => 'Nominal bayar harus sama dengan total tagihan.',
            ]);
        }

        /** @var UploadedFile $proof */
        $proof = $data['bukti_bayar'];
        $method = MetodeBayar::findOrFail($data['id_metode_bayar']);

        return DB::transaction(function () use ($pelanggan, $installment, $data, $proof, $method) {
            $path = $proof->store('payments/'.$installment->id, 'public');

            $payload = [
                'angsuran_id' => $installment->id,
                'pelanggan_id' => $pelanggan->id,
                'kode_pembayaran' => $this->generateCode(),
                'nama_bank_pengirim' => $data['nama_bank_pengirim'] ?? null,
                'nama_pemilik_rekening' => $data['nama_pemilik_rekening'] ?? null,
                'nominal_bayar' => $data['nominal_bayar'],
                'tanggal_bayar' => $data['tanggal_bayar'],
                'bukti_bayar' => $path,
                'status_verifikasi' => PaymentVerificationStatus::Pending->value,
                'catatan_verifikasi' => $data['catatan'] ?? 'Bukti pembayaran telah diunggah user.',
            ];

            LegacyDiagramSync::syncPaymentMethodFields($payload, $method);

            $payment = Pembayaran::create($payload);

            $installment->update([
                'status_pembayaran' => InstallmentPaymentStatus::MenungguVerifikasi->value,
                'tanggal_bayar' => $data['tanggal_bayar'],
                'metode_bayar' => $method->metode_pembayaran,
                'tgl_bayar' => $data['tanggal_bayar'],
                'id_kredit' => $installment->pengajuan_id,
                'total_bayar' => $installment->total_tagihan,
                'keterangan' => 'Pembayaran dikirim melalui '.$method->metode_pembayaran.'.',
            ]);

            LegacyDiagramSync::syncInstallment($installment->fresh());

            Notification::create([
                'user_id' => $pelanggan->user_id,
                'title' => 'Bukti pembayaran berhasil diunggah',
                'message' => 'Pembayaran '.$payment->kode_pembayaran.' menunggu verifikasi admin.',
                'type' => 'payment',
                'reference_type' => 'pembayaran',
                'reference_id' => $payment->id,
            ]);

            return $payment->fresh(['installment.application.motor']);
        });
    }

    private function generateCode(): string
    {
        $sequence = str_pad((string) (Pembayaran::count() + 1), 3, '0', STR_PAD_LEFT);

        return 'PAY-'.now()->format('Ymd').'-'.$sequence;
    }
}

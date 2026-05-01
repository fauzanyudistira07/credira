<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\DeliveryStatus;
use App\Enums\DocumentVerificationStatus;
use App\Enums\InstallmentPaymentStatus;
use App\Enums\PaymentVerificationStatus;
use App\Models\Angsuran;
use App\Models\DokumenPengajuan;
use App\Models\Notification;
use App\Models\Pembayaran;
use App\Models\PengajuanKredit;
use App\Models\PengajuanLog;
use App\Models\Pengiriman;
use App\Models\User;
use App\Support\LegacyDiagramSync;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminWorkflowService
{
    public function updateApplicationStatus(PengajuanKredit $application, string $status, ?string $note, User $admin): void
    {
        $nextStatus = ApplicationStatus::tryFrom($status);

        if (! $nextStatus) {
            throw ValidationException::withMessages([
                'status_pengajuan' => 'Status pengajuan tidak valid.',
            ]);
        }

        $application->loadMissing(['pelanggan', 'jenisCicilan']);

        DB::transaction(function () use ($application, $nextStatus, $note, $admin) {
            $oldStatus = $application->status_pengajuan;
            $message = $this->resolveApplicationStatusNote($nextStatus, $note, $admin);

            $payload = [
                'status_pengajuan' => $nextStatus->value,
                'catatan_status' => $message,
                'keterangan_status_pengajuan' => $message,
            ];

            if (in_array($nextStatus, [ApplicationStatus::Disetujui, ApplicationStatus::KontrakAktif], true)) {
                $payload['approved_at'] = $application->approved_at ?? now();
                $payload['rejected_at'] = null;
            }

            if (in_array($nextStatus, [ApplicationStatus::Ditolak, ApplicationStatus::DibatalkanAdmin], true)) {
                $payload['approved_at'] = null;
                $payload['rejected_at'] = now();
            }

            if (in_array($nextStatus, [
                ApplicationStatus::MenungguKonfirmasi,
                ApplicationStatus::VerifikasiDokumen,
                ApplicationStatus::Diproses,
                ApplicationStatus::Survey,
            ], true)) {
                $payload['approved_at'] = null;
                $payload['rejected_at'] = null;
            }

            $application->update($payload);
            LegacyDiagramSync::syncPengajuan($application->fresh(['jenisCicilan']));

            if ($nextStatus === ApplicationStatus::KontrakAktif) {
                $this->ensureInstallments($application);
                $this->ensureDelivery($application);
            }

            $this->writeApplicationLog($application, $oldStatus, $nextStatus->value, $message, $admin->id);

            $this->notify(
                $application->pelanggan->user_id,
                'Status pengajuan diperbarui',
                'Pengajuan '.$application->kode_pengajuan.' sekarang berstatus '.$nextStatus->label().'.',
                'application',
                'pengajuan',
                $application->id,
            );
        });
    }

    public function verifyDocument(DokumenPengajuan $document, string $status, ?string $note, User $admin): void
    {
        $nextStatus = DocumentVerificationStatus::tryFrom($status);

        if (! $nextStatus) {
            throw ValidationException::withMessages([
                'status_verifikasi' => 'Status dokumen tidak valid.',
            ]);
        }

        $document->loadMissing(['application.pelanggan']);

        DB::transaction(function () use ($document, $nextStatus, $note, $admin) {
            $message = $note ?: 'Status dokumen diperbarui menjadi '.$nextStatus->label().' oleh '.$this->resolveActorLabel($admin).'.';

            $document->update([
                'status_verifikasi' => $nextStatus->value,
                'catatan_verifikasi' => $message,
                'verified_at' => $nextStatus === DocumentVerificationStatus::Pending ? null : now(),
            ]);

            $application = $document->application;
            $docType = str($document->jenis_dokumen)->replace('_', ' ')->title();

            $this->writeApplicationLog(
                $application,
                $application->status_pengajuan,
                $application->status_pengajuan,
                'Dokumen '.$docType.' diperbarui menjadi '.$nextStatus->label().'.',
                $admin->id,
            );

            $this->notify(
                $application->pelanggan->user_id,
                'Status dokumen diperbarui',
                'Dokumen '.$docType.' untuk pengajuan '.$application->kode_pengajuan.' sekarang '.$nextStatus->label().'.',
                'application',
                'pengajuan',
                $application->id,
            );
        });
    }

    public function verifyPayment(Pembayaran $payment, string $status, ?string $note, User $admin): void
    {
        $nextStatus = PaymentVerificationStatus::tryFrom($status);

        if (! $nextStatus) {
            throw ValidationException::withMessages([
                'status_verifikasi' => 'Status pembayaran tidak valid.',
            ]);
        }

        $payment->loadMissing(['pelanggan', 'installment.application.jenisCicilan']);

        DB::transaction(function () use ($payment, $nextStatus, $note, $admin) {
            $message = $note ?: $this->resolvePaymentVerificationNote($nextStatus, $admin);

            $payment->update([
                'status_verifikasi' => $nextStatus->value,
                'catatan_verifikasi' => $message,
                'verified_by' => $nextStatus === PaymentVerificationStatus::Pending ? null : $admin->id,
                'verified_at' => $nextStatus === PaymentVerificationStatus::Pending ? null : now(),
            ]);

            $installment = $payment->installment;
            $installmentPayload = [
                'metode_bayar' => $payment->metode_bayar,
                'keterangan' => $message,
            ];

            if ($nextStatus === PaymentVerificationStatus::Valid) {
                $installmentPayload['status_pembayaran'] = InstallmentPaymentStatus::SudahBayar->value;
                $installmentPayload['tanggal_bayar'] = $payment->tanggal_bayar;
                $installmentPayload['verified_by'] = $admin->id;
                $installmentPayload['verified_at'] = now();
            } elseif ($nextStatus === PaymentVerificationStatus::Ditolak) {
                $installmentPayload['status_pembayaran'] = InstallmentPaymentStatus::GagalVerifikasi->value;
                $installmentPayload['verified_by'] = $admin->id;
                $installmentPayload['verified_at'] = now();
            } else {
                $installmentPayload['status_pembayaran'] = InstallmentPaymentStatus::MenungguVerifikasi->value;
                $installmentPayload['verified_by'] = null;
                $installmentPayload['verified_at'] = null;
            }

            $installment->update($installmentPayload);
            LegacyDiagramSync::syncInstallment($installment->fresh());

            $application = $installment->application;

            if (
                $nextStatus === PaymentVerificationStatus::Valid
                && $application->installments()->where('status_pembayaran', '!=', InstallmentPaymentStatus::SudahBayar->value)->count() === 0
                && $application->status_pengajuan !== ApplicationStatus::Selesai->value
            ) {
                $completionNote = 'Semua angsuran telah tervalidasi. Status pengajuan selesai.';
                $oldStatus = $application->status_pengajuan;

                $application->update([
                    'status_pengajuan' => ApplicationStatus::Selesai->value,
                    'catatan_status' => $completionNote,
                    'keterangan_status_pengajuan' => $completionNote,
                ]);

                LegacyDiagramSync::syncPengajuan($application->fresh(['jenisCicilan']));

                $this->writeApplicationLog(
                    $application,
                    $oldStatus,
                    ApplicationStatus::Selesai->value,
                    $completionNote,
                    $admin->id,
                );
            }

            $this->notify(
                $payment->pelanggan->user_id,
                'Status pembayaran diperbarui',
                'Pembayaran '.$payment->kode_pembayaran.' sekarang berstatus '.$nextStatus->label().'.',
                'payment',
                'pembayaran',
                $payment->id,
            );
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateDelivery(Pengiriman $delivery, array $data, User $admin): void
    {
        $nextStatus = DeliveryStatus::tryFrom((string) $data['status_kirim']);

        if (! $nextStatus) {
            throw ValidationException::withMessages([
                'status_kirim' => 'Status pengiriman tidak valid.',
            ]);
        }

        $delivery->loadMissing(['application.pelanggan']);

        DB::transaction(function () use ($delivery, $data, $nextStatus, $admin) {
            $payload = [
                'status_kirim' => $nextStatus->value,
                'tgl_kirim' => $data['tgl_kirim'] ?? $delivery->tgl_kirim,
                'tgl_tiba' => $data['tgl_tiba'] ?? $delivery->tgl_tiba,
                'nama_kurir' => ! empty($data['nama_kurir']) ? trim((string) $data['nama_kurir']) : null,
                'telpon_kurir' => ! empty($data['telpon_kurir']) ? trim((string) $data['telpon_kurir']) : null,
                'nama_penerima' => ! empty($data['nama_penerima']) ? trim((string) $data['nama_penerima']) : null,
                'keterangan' => ! empty($data['keterangan']) ? trim((string) $data['keterangan']) : $this->resolveDeliveryNote($nextStatus, $admin),
            ];

            if (in_array($nextStatus, [DeliveryStatus::Dikirim, DeliveryStatus::SampaiTujuan], true) && ! $payload['tgl_kirim']) {
                $payload['tgl_kirim'] = Carbon::today()->toDateString();
            }

            if ($nextStatus === DeliveryStatus::SampaiTujuan && ! $payload['tgl_tiba']) {
                $payload['tgl_tiba'] = Carbon::today()->toDateString();
            }

            $delivery->update($payload);
            LegacyDiagramSync::syncDelivery($delivery->fresh());

            $this->notify(
                $delivery->application->pelanggan->user_id,
                'Status pengiriman diperbarui',
                'Pengiriman untuk pengajuan '.$delivery->application->kode_pengajuan.' sekarang berstatus '.$nextStatus->label().'.',
                'delivery',
                'pengiriman',
                $delivery->id,
            );

            $this->writeApplicationLog(
                $delivery->application,
                $delivery->application->status_pengajuan,
                $delivery->application->status_pengajuan,
                'Pengiriman diperbarui oleh '.$this->resolveActorLabel($admin).' menjadi '.$nextStatus->label().'.',
                $admin->id,
            );
        });
    }

    private function ensureInstallments(PengajuanKredit $application): void
    {
        if ($application->installments()->exists()) {
            return;
        }

        $duration = (int) ($application->jenisCicilan->durasi_bulan ?? 0);
        if ($duration <= 0) {
            return;
        }

        $installmentValue = (int) $application->cicilan_perbulan;
        $firstDueDate = now()->startOfMonth()->addMonth()->day(5);

        foreach (range(1, $duration) as $index) {
            $dueDate = $firstDueDate->copy()->addMonthsNoOverflow($index - 1);

            $installment = Angsuran::create([
                'pengajuan_id' => $application->id,
                'id_kredit' => $application->id,
                'angsuran_ke' => $index,
                'tanggal_jatuh_tempo' => $dueDate->toDateString(),
                'nominal_angsuran' => $installmentValue,
                'denda' => 0,
                'total_tagihan' => $installmentValue,
                'total_bayar' => $installmentValue,
                'status_pembayaran' => InstallmentPaymentStatus::BelumBayar->value,
                'keterangan' => 'Angsuran dibuat otomatis saat kontrak aktif.',
            ]);

            LegacyDiagramSync::syncInstallment($installment);
        }
    }

    private function ensureDelivery(PengajuanKredit $application): void
    {
        if ($application->delivery()->exists()) {
            return;
        }

        $primaryAddress = $application->pelanggan
            ->addresses()
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->first();

        $delivery = Pengiriman::create([
            'pengajuan_id' => $application->id,
            'invoice' => $this->generateInvoice(),
            'alamat_pengiriman_id' => $primaryAddress?->id,
            'alamat_tujuan' => $this->formatAddress($primaryAddress),
            'status_kirim' => DeliveryStatus::MenungguPengiriman->value,
            'nama_penerima' => $application->pelanggan->nama_lengkap,
            'keterangan' => 'Pengiriman akan dijadwalkan setelah kontrak aktif.',
        ]);

        LegacyDiagramSync::syncDelivery($delivery);
    }

    private function resolveApplicationStatusNote(ApplicationStatus $status, ?string $note, User $actor): string
    {
        if ($note) {
            return $note;
        }

        $actorLabel = $this->resolveActorLabel($actor);

        return match ($status) {
            ApplicationStatus::MenungguKonfirmasi => 'Pengajuan diterima dan masuk antrean konfirmasi '.$actorLabel.'.',
            ApplicationStatus::VerifikasiDokumen => 'Dokumen sedang diverifikasi oleh '.$actorLabel.'.',
            ApplicationStatus::Diproses => 'Pengajuan sedang diproses lebih lanjut oleh '.$actorLabel.'.',
            ApplicationStatus::Survey => 'Pengajuan masuk tahap survey lapangan.',
            ApplicationStatus::Disetujui => 'Pengajuan disetujui oleh '.$actorLabel.'.',
            ApplicationStatus::KontrakAktif => 'Kontrak aktif. Jadwal angsuran dan pengiriman telah disiapkan.',
            ApplicationStatus::Ditolak => 'Pengajuan ditolak oleh '.$actorLabel.'.',
            ApplicationStatus::DibatalkanAdmin => 'Pengajuan dibatalkan oleh '.$actorLabel.'.',
            ApplicationStatus::Selesai => 'Pengajuan telah selesai.',
            default => 'Status pengajuan diperbarui oleh '.$actorLabel.'.',
        };
    }

    private function resolvePaymentVerificationNote(PaymentVerificationStatus $status, User $actor): string
    {
        $actorLabel = $this->resolveActorLabel($actor);

        return match ($status) {
            PaymentVerificationStatus::Valid => 'Pembayaran dinyatakan valid oleh '.$actorLabel.'.',
            PaymentVerificationStatus::Ditolak => 'Pembayaran ditolak oleh '.$actorLabel.'.',
            PaymentVerificationStatus::Pending => 'Pembayaran menunggu verifikasi '.$actorLabel.'.',
        };
    }

    private function resolveDeliveryNote(DeliveryStatus $status, User $actor): string
    {
        $actorLabel = $this->resolveActorLabel($actor);

        return match ($status) {
            DeliveryStatus::MenungguPengiriman => 'Pengiriman menunggu penjadwalan oleh '.$actorLabel.'.',
            DeliveryStatus::Disiapkan => 'Unit sedang disiapkan untuk dikirim oleh '.$actorLabel.'.',
            DeliveryStatus::Dikirim => 'Unit sedang dalam proses pengiriman.',
            DeliveryStatus::SampaiTujuan => 'Unit telah sampai di alamat tujuan.',
            DeliveryStatus::GagalKirim => 'Pengiriman terkendala dan perlu penjadwalan ulang oleh '.$actorLabel.'.',
        };
    }

    private function resolveActorLabel(User $actor): string
    {
        return match ($actor->role) {
            User::ROLE_MARKETING => 'marketing',
            User::ROLE_CEO => 'owner',
            default => 'admin',
        };
    }

    private function writeApplicationLog(
        PengajuanKredit $application,
        ?string $oldStatus,
        string $newStatus,
        string $note,
        int $changedBy
    ): void {
        PengajuanLog::create([
            'pengajuan_id' => $application->id,
            'status_lama' => $oldStatus,
            'status_baru' => $newStatus,
            'catatan' => $note,
            'changed_by' => $changedBy,
        ]);
    }

    private function notify(
        int $userId,
        string $title,
        string $message,
        string $type,
        string $referenceType,
        int $referenceId
    ): void {
        Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
        ]);
    }

    private function generateInvoice(): string
    {
        do {
            $candidate = 'INV-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Pengiriman::where('invoice', $candidate)->exists());

        return $candidate;
    }

    private function formatAddress($address): string
    {
        if (! $address) {
            return 'Alamat pengiriman belum ditetapkan.';
        }

        return $address->alamat_lengkap.', '.$address->kota.', '.$address->provinsi.' '.$address->kode_pos;
    }
}

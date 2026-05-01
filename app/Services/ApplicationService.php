<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Asuransi;
use App\Models\DokumenPengajuan;
use App\Models\JenisCicilan;
use App\Models\Motor;
use App\Models\Notification;
use App\Models\Pelanggan;
use App\Models\PelangganAddress;
use App\Models\PengajuanDetailFinansial;
use App\Models\PengajuanKredit;
use App\Models\PengajuanLog;
use App\Support\LegacyDiagramSync;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ApplicationService
{
    public function __construct(
        private readonly CreditSimulationService $simulationService,
    ) {
    }

    public function create(Pelanggan $pelanggan, array $data, bool $submit = true): PengajuanKredit
    {
        return DB::transaction(function () use ($pelanggan, $data, $submit) {
            $motor = Motor::findOrFail($data['motor_id']);
            $plan = JenisCicilan::findOrFail($data['jenis_cicilan_id']);
            $insurance = ! empty($data['asuransi_id']) ? Asuransi::findOrFail($data['asuransi_id']) : null;
            $simulation = $this->simulationService->calculate($motor, $plan, $insurance, (int) $data['dp']);
            $status = $submit ? ApplicationStatus::MenungguKonfirmasi : ApplicationStatus::Draft;

            $application = PengajuanKredit::create([
                'kode_pengajuan' => $this->generateCode(),
                'tgl_pengajuan' => $submit ? now()->toDateString() : now()->toDateString(),
                'pelanggan_id' => $pelanggan->id,
                'id_pelanggan' => $pelanggan->id,
                'motor_id' => $motor->id,
                'id_motor' => $motor->id,
                'jenis_cicilan_id' => $plan->id,
                'id_jenis_cicilan' => $plan->id,
                'asuransi_id' => $insurance?->id,
                'id_asuransi' => $insurance?->id,
                'harga_cash' => $simulation['harga_motor'],
                'dp' => $simulation['dp'],
                'pokok_kredit' => $simulation['pokok_kredit'],
                'harga_kredit' => $simulation['pokok_kredit'],
                'margin_kredit' => $simulation['margin_amount'],
                'biaya_admin' => $simulation['biaya_admin'],
                'biaya_asuransi' => $simulation['biaya_asuransi'],
                'biaya_asuransi_perbulan' => $plan->durasi_bulan > 0 ? round($simulation['biaya_asuransi'] / $plan->durasi_bulan, 2) : 0,
                'cicilan_perbulan' => $simulation['angsuran_per_bulan'],
                'total_bayar' => $simulation['total_bayar'],
                'status_pengajuan' => $status->value,
                'catatan_status' => $submit
                    ? 'Pengajuan berhasil dikirim dan menunggu verifikasi.'
                    : 'Draft pengajuan berhasil disimpan.',
                'keterangan_status_pengajuan' => $submit
                    ? 'Pengajuan berhasil dikirim dan menunggu verifikasi.'
                    : 'Draft pengajuan berhasil disimpan.',
                'submitted_at' => $submit ? now() : null,
                'snapshot_data' => $this->snapshotData($pelanggan, $data),
            ]);

            PengajuanDetailFinansial::create([
                'pengajuan_id' => $application->id,
                'pekerjaan' => $data['pekerjaan'] ?? null,
                'nama_perusahaan' => $data['nama_perusahaan'] ?? null,
                'alamat_kantor' => $data['alamat_kantor'] ?? null,
                'lama_bekerja' => $data['lama_bekerja'] ?? null,
                'penghasilan_bulanan' => $data['penghasilan_bulanan'] ?? null,
                'pengeluaran_bulanan' => $data['pengeluaran_bulanan'] ?? null,
                'status_rumah' => $data['status_rumah'] ?? null,
                'kontak_darurat_nama' => $data['kontak_darurat_nama'] ?? null,
                'kontak_darurat_nohp' => $data['kontak_darurat_nohp'] ?? null,
                'kontak_darurat_hubungan' => $data['kontak_darurat_hubungan'] ?? null,
            ]);

            if (! empty($data['documents']) && is_array($data['documents'])) {
                $this->storeDocuments($application, $data['documents']);
            }

            LegacyDiagramSync::syncPengajuan($application->fresh(['jenisCicilan']));

            $this->writeLog(
                $application,
                null,
                $status->value,
                $submit ? 'Pengajuan dibuat oleh user.' : 'Draft pengajuan disimpan.'
            );

            $this->notify(
                $pelanggan,
                $submit ? 'Pengajuan berhasil dikirim' : 'Draft pengajuan disimpan',
                $submit
                    ? 'Pengajuan '.$application->kode_pengajuan.' masuk ke antrean verifikasi.'
                    : 'Draft '.$application->kode_pengajuan.' siap dilanjutkan kapan saja.',
                'application',
                $application->id,
            );

            return $application->fresh(['motor', 'jenisCicilan', 'asuransi', 'documents', 'financialDetail', 'logs']);
        });
    }

    public function update(PengajuanKredit $application, array $data): PengajuanKredit
    {
        $this->ensureEditable($application);

        return DB::transaction(function () use ($application, $data) {
            $motor = Motor::findOrFail($data['motor_id'] ?? $application->motor_id);
            $plan = JenisCicilan::findOrFail($data['jenis_cicilan_id'] ?? $application->jenis_cicilan_id);
            $insurance = array_key_exists('asuransi_id', $data)
                ? (! empty($data['asuransi_id']) ? Asuransi::findOrFail($data['asuransi_id']) : null)
                : $application->asuransi;
            $dp = (int) ($data['dp'] ?? $application->dp);
            $simulation = $this->simulationService->calculate($motor, $plan, $insurance, $dp);
            $snapshotSource = array_merge($application->snapshot_data ?? [], $data);

            $application->update([
                'motor_id' => $motor->id,
                'id_motor' => $motor->id,
                'jenis_cicilan_id' => $plan->id,
                'id_jenis_cicilan' => $plan->id,
                'asuransi_id' => $insurance?->id,
                'id_asuransi' => $insurance?->id,
                'harga_cash' => $simulation['harga_motor'],
                'dp' => $simulation['dp'],
                'pokok_kredit' => $simulation['pokok_kredit'],
                'harga_kredit' => $simulation['pokok_kredit'],
                'margin_kredit' => $simulation['margin_amount'],
                'biaya_admin' => $simulation['biaya_admin'],
                'biaya_asuransi' => $simulation['biaya_asuransi'],
                'biaya_asuransi_perbulan' => $plan->durasi_bulan > 0 ? round($simulation['biaya_asuransi'] / $plan->durasi_bulan, 2) : 0,
                'cicilan_perbulan' => $simulation['angsuran_per_bulan'],
                'total_bayar' => $simulation['total_bayar'],
                'snapshot_data' => $this->snapshotData($application->pelanggan, $snapshotSource),
            ]);

            $application->financialDetail()->updateOrCreate(
                ['pengajuan_id' => $application->id],
                Arr::only($data, [
                    'pekerjaan',
                    'nama_perusahaan',
                    'alamat_kantor',
                    'lama_bekerja',
                    'penghasilan_bulanan',
                    'pengeluaran_bulanan',
                    'status_rumah',
                    'kontak_darurat_nama',
                    'kontak_darurat_nohp',
                    'kontak_darurat_hubungan',
                ]),
            );

            if (! empty($data['documents']) && is_array($data['documents'])) {
                $this->storeDocuments($application, $data['documents']);
            }

            LegacyDiagramSync::syncPengajuan($application->fresh(['jenisCicilan']));

            $this->writeLog(
                $application,
                $application->status_pengajuan,
                $application->status_pengajuan,
                'Data pengajuan diperbarui oleh user.'
            );

            return $application->fresh(['motor', 'jenisCicilan', 'asuransi', 'documents', 'financialDetail', 'logs']);
        });
    }

    public function submit(PengajuanKredit $application): PengajuanKredit
    {
        $this->ensureEditable($application);

        $requiredDocuments = collect($application->documents)->pluck('jenis_dokumen');
        $missing = collect(['foto_ktp', 'slip_gaji', 'foto_selfie_ktp'])
            ->reject(fn (string $item) => $requiredDocuments->contains($item))
            ->values();

        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'documents' => 'Dokumen wajib belum lengkap: '.$missing->implode(', ').'.',
            ]);
        }

        $oldStatus = $application->status_pengajuan;

        $application->update([
            'status_pengajuan' => ApplicationStatus::MenungguKonfirmasi->value,
            'catatan_status' => 'Pengajuan berhasil dikirim dan menunggu verifikasi.',
            'keterangan_status_pengajuan' => 'Pengajuan berhasil dikirim dan menunggu verifikasi.',
            'submitted_at' => now(),
            'tgl_pengajuan' => now()->toDateString(),
        ]);

        LegacyDiagramSync::syncPengajuan($application->fresh(['jenisCicilan']));

        $this->writeLog(
            $application,
            $oldStatus,
            ApplicationStatus::MenungguKonfirmasi->value,
            'Pengajuan disubmit final oleh user.'
        );

        $this->notify(
            $application->pelanggan,
            'Pengajuan dikirim',
            'Pengajuan '.$application->kode_pengajuan.' berhasil dikirim untuk diproses.',
            'application',
            $application->id,
        );

        return $application->fresh(['motor', 'jenisCicilan', 'asuransi', 'documents', 'financialDetail', 'logs']);
    }

    public function cancel(PengajuanKredit $application): PengajuanKredit
    {
        if (! in_array($application->status_pengajuan, [
            ApplicationStatus::Draft->value,
            ApplicationStatus::MenungguKonfirmasi->value,
            ApplicationStatus::VerifikasiDokumen->value,
        ], true)) {
            throw ValidationException::withMessages([
                'status' => 'Pengajuan sudah terlalu jauh diproses dan tidak dapat dibatalkan user.',
            ]);
        }

        $oldStatus = $application->status_pengajuan;
        $application->update([
            'status_pengajuan' => ApplicationStatus::DibatalkanUser->value,
            'catatan_status' => 'Pengajuan dibatalkan oleh user.',
            'keterangan_status_pengajuan' => 'Pengajuan dibatalkan oleh user.',
        ]);

        LegacyDiagramSync::syncPengajuan($application->fresh(['jenisCicilan']));

        $this->writeLog(
            $application,
            $oldStatus,
            ApplicationStatus::DibatalkanUser->value,
            'Pengajuan dibatalkan oleh user.'
        );

        $this->notify(
            $application->pelanggan,
            'Pengajuan dibatalkan',
            'Pengajuan '.$application->kode_pengajuan.' berhasil dibatalkan.',
            'application',
            $application->id,
        );

        return $application->fresh(['logs']);
    }

    public function storeDocuments(PengajuanKredit $application, array $documents): void
    {
        foreach ($documents as $type => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store('applications/'.$application->id.'/documents', 'public');

            DokumenPengajuan::create([
                'pengajuan_id' => $application->id,
                'jenis_dokumen' => (string) $type,
                'nama_file' => $file->getClientOriginalName(),
                'path_file' => $path,
                'uploaded_at' => now(),
            ]);

            $legacyMap = [
                'foto_ktp' => 'url_ktp',
                'npwp' => 'url_npwp',
                'slip_gaji' => 'url_slip_gaji',
                'foto_selfie_ktp' => 'url_foto',
            ];

            if (isset($legacyMap[$type])) {
                $application->forceFill([
                    $legacyMap[$type] => $path,
                ])->save();
            }
        }

        LegacyDiagramSync::syncPengajuan($application->fresh(['jenisCicilan']));
    }

    public function deleteDocument(PengajuanKredit $application, DokumenPengajuan $document): void
    {
        if ($document->pengajuan_id !== $application->id) {
            throw ValidationException::withMessages([
                'document' => 'Dokumen tidak cocok dengan pengajuan.',
            ]);
        }

        $this->ensureEditable($application);

        Storage::disk('public')->delete($document->path_file);
        $document->delete();
    }

    private function ensureEditable(PengajuanKredit $application): void
    {
        if (! in_array($application->status_pengajuan, [
            ApplicationStatus::Draft->value,
            ApplicationStatus::MenungguKonfirmasi->value,
            ApplicationStatus::VerifikasiDokumen->value,
        ], true)) {
            throw ValidationException::withMessages([
                'status' => 'Pengajuan ini tidak lagi dapat diedit.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function snapshotData(Pelanggan $pelanggan, array $data): array
    {
        $address = null;

        if (! empty($data['alamat_pengiriman_id'])) {
            $address = PelangganAddress::where('pelanggan_id', $pelanggan->id)
                ->find($data['alamat_pengiriman_id']);
        }

        return [
            'personal' => [
                'nama_lengkap' => $data['nama_lengkap'] ?? $pelanggan->nama_lengkap,
                'no_ktp' => $data['no_ktp'] ?? $pelanggan->no_ktp,
                'tempat_lahir' => $data['tempat_lahir'] ?? $pelanggan->tempat_lahir,
                'tanggal_lahir' => $data['tanggal_lahir'] ?? $pelanggan->tanggal_lahir,
                'jenis_kelamin' => $data['jenis_kelamin'] ?? $pelanggan->jenis_kelamin,
                'status_pernikahan' => $data['status_pernikahan'] ?? $pelanggan->status_pernikahan,
                'nomor_hp' => $data['nomor_hp'] ?? $pelanggan->no_telp,
                'email' => $data['email'] ?? $pelanggan->email,
                'alamat_lengkap' => $data['alamat_lengkap'] ?? $address?->alamat_lengkap,
                'kota' => $data['kota'] ?? $address?->kota,
                'provinsi' => $data['provinsi'] ?? $address?->provinsi,
                'kode_pos' => $data['kode_pos'] ?? $address?->kode_pos,
            ],
            'financial' => Arr::only($data, [
                'pekerjaan',
                'nama_perusahaan',
                'alamat_kantor',
                'lama_bekerja',
                'penghasilan_bulanan',
                'pengeluaran_bulanan',
                'status_rumah',
                'kontak_darurat_nama',
                'kontak_darurat_nohp',
                'kontak_darurat_hubungan',
            ]),
        ];
    }

    private function writeLog(PengajuanKredit $application, ?string $oldStatus, string $newStatus, string $note): void
    {
        PengajuanLog::create([
            'pengajuan_id' => $application->id,
            'status_lama' => $oldStatus,
            'status_baru' => $newStatus,
            'catatan' => $note,
            'changed_by' => $application->pelanggan->user_id,
        ]);
    }

    private function notify(Pelanggan $pelanggan, string $title, string $message, string $type, int $referenceId): void
    {
        Notification::create([
            'user_id' => $pelanggan->user_id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'reference_type' => 'pengajuan',
            'reference_id' => $referenceId,
        ]);
    }

    private function generateCode(): string
    {
        $sequence = str_pad((string) (PengajuanKredit::count() + 1), 3, '0', STR_PAD_LEFT);

        return 'CRD-'.now()->format('Ymd').'-'.$sequence;
    }
}

<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Asuransi;
use App\Models\DokumenPengajuan;
use App\Models\JenisCicilan;
use App\Models\Motor;
use App\Models\Pelanggan;
use App\Models\PengajuanDetailFinansial;
use App\Models\PengajuanKredit;
use App\Models\PengajuanLog;
use App\Models\User;
use App\Support\LegacyDiagramSync;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class MarketingApplicationService
{
    public function __construct(
        private readonly CreditSimulationService $simulationService,
    ) {
    }

    public function create(User $marketing, Pelanggan $pelanggan, array $data): PengajuanKredit
    {
        return DB::transaction(function () use ($marketing, $pelanggan, $data) {
            $motor = Motor::query()->active()->findOrFail($data['motor_id']);
            $plan = JenisCicilan::findOrFail($data['jenis_cicilan_id']);
            $insurance = filled($data['asuransi_id'] ?? null)
                ? Asuransi::findOrFail($data['asuransi_id'])
                : null;

            $simulation = $this->simulationService->calculate($motor, $plan, $insurance, (int) $data['dp']);
            $status = ApplicationStatus::MenungguKonfirmasi;
            $note = 'Pengajuan dibuat oleh marketing dan menunggu review.';

            $application = PengajuanKredit::create([
                'kode_pengajuan' => $this->generateCode(),
                'tgl_pengajuan' => now()->toDateString(),
                'pelanggan_id' => $pelanggan->id,
                'marketing_user_id' => $marketing->id,
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
                'catatan_status' => $note,
                'keterangan_status_pengajuan' => $note,
                'submitted_at' => now(),
                'snapshot_data' => $this->snapshotData($pelanggan, $data),
            ]);

            PengajuanDetailFinansial::create([
                'pengajuan_id' => $application->id,
                'pekerjaan' => $data['pekerjaan'] ?? $pelanggan->pekerjaan_default,
                'nama_perusahaan' => $data['nama_perusahaan'] ?? null,
                'alamat_kantor' => $data['alamat_kantor'] ?? null,
                'lama_bekerja' => $data['lama_bekerja'] ?? null,
                'penghasilan_bulanan' => $data['penghasilan_bulanan'] ?? $pelanggan->penghasilan_default,
                'pengeluaran_bulanan' => $data['pengeluaran_bulanan'] ?? null,
                'status_rumah' => $data['status_rumah'] ?? null,
                'kontak_darurat_nama' => $data['kontak_darurat_nama'] ?? null,
                'kontak_darurat_nohp' => $data['kontak_darurat_nohp'] ?? null,
                'kontak_darurat_hubungan' => $data['kontak_darurat_hubungan'] ?? null,
            ]);

            $this->storeDocuments($application, $data['documents'] ?? []);

            PengajuanLog::create([
                'pengajuan_id' => $application->id,
                'status_lama' => null,
                'status_baru' => $status->value,
                'catatan' => $note,
                'changed_by' => $marketing->id,
            ]);

            LegacyDiagramSync::syncPengajuan($application->fresh('jenisCicilan'));

            return $application->fresh([
                'pelanggan.user',
                'marketingOwner',
                'motor.jenisMotor',
                'jenisCicilan',
                'asuransi',
                'financialDetail',
                'documents',
                'logs.changedBy',
            ]);
        });
    }

    private function storeDocuments(PengajuanKredit $application, array $documents): void
    {
        $legacyMap = [
            'foto_ktp' => 'url_ktp',
            'npwp' => 'url_npwp',
            'slip_gaji' => 'url_slip_gaji',
            'foto_selfie_ktp' => 'url_foto',
        ];

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

            if (isset($legacyMap[$type])) {
                $application->forceFill([
                    $legacyMap[$type] => $path,
                ])->save();
            }
        }
    }

    private function snapshotData(Pelanggan $pelanggan, array $data): array
    {
        return [
            'personal' => [
                'nama_lengkap' => $pelanggan->display_name,
                'no_ktp' => $pelanggan->no_ktp,
                'tempat_lahir' => $pelanggan->tempat_lahir,
                'tanggal_lahir' => optional($pelanggan->tanggal_lahir)?->toDateString(),
                'jenis_kelamin' => $pelanggan->jenis_kelamin,
                'status_pernikahan' => $pelanggan->status_pernikahan,
                'nomor_hp' => $pelanggan->no_telp,
                'email' => $pelanggan->email,
                'alamat_lengkap' => $pelanggan->alamat1,
                'kota' => $pelanggan->kota1,
                'provinsi' => $pelanggan->propinsi1,
                'kode_pos' => $pelanggan->kodepos1,
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

    private function generateCode(): string
    {
        do {
            $code = 'CRD-MKT-'.now()->format('ymd').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (PengajuanKredit::where('kode_pengajuan', $code)->exists());

        return $code;
    }
}

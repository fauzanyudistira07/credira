<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Enums\DeliveryStatus;
use App\Enums\InstallmentPaymentStatus;
use App\Enums\PaymentVerificationStatus;
use App\Models\Angsuran;
use App\Models\Asuransi;
use App\Models\Faq;
use App\Models\JenisCicilan;
use App\Models\JenisMotor;
use App\Models\Motor;
use App\Models\MotorImage;
use App\Models\MetodeBayar;
use App\Models\Notification;
use App\Models\Pelanggan;
use App\Models\PelangganAddress;
use App\Models\Pembayaran;
use App\Models\PengajuanDetailFinansial;
use App\Models\PengajuanKredit;
use App\Models\PengajuanLog;
use App\Models\Pengiriman;
use App\Models\Testimonial;
use App\Services\CreditSimulationService;
use App\Support\LegacyDiagramSync;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class StarterUserExperienceSeeder extends Seeder
{
    public function run(): void
    {
        $pelanggan = Pelanggan::with('user', 'addresses')->orderBy('id')->first();

        if (! $pelanggan || ! $pelanggan->user) {
            return;
        }

        [$motors, $plans, $insurances, $paymentMethods] = $this->seedMasterData();
        $primaryAddress = $this->ensurePrimaryAddress($pelanggan);

        if ($pelanggan->applications()->exists()) {
            return;
        }

        $simulationService = app(CreditSimulationService::class);
        $user = $pelanggan->user;

        $activeApplication = $this->seedActiveApplication(
            $simulationService,
            $pelanggan,
            $primaryAddress,
            $motors[0],
            $plans[1],
            $insurances[0],
            $paymentMethods[0],
            $user
        );

        $pendingApplication = $this->seedPendingApplication(
            $simulationService,
            $pelanggan,
            $primaryAddress,
            $motors[2],
            $plans[2],
            $user
        );

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Pengajuan sedang diverifikasi',
            'message' => 'Pengajuan '.$pendingApplication->kode_pengajuan.' sedang masuk tahap verifikasi dokumen.',
            'type' => 'application',
            'reference_type' => 'pengajuan',
            'reference_id' => $pendingApplication->id,
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Pembayaran diterima',
            'message' => 'Pembayaran angsuran pertama untuk kontrak '.$activeApplication->kode_pengajuan.' telah diverifikasi.',
            'type' => 'payment',
            'reference_type' => 'pengajuan',
            'reference_id' => $activeApplication->id,
            'is_read' => true,
        ]);

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Motor dalam pengiriman',
            'message' => 'Unit '.$activeApplication->motor->nama_motor.' sedang dikirim ke alamat utama Anda.',
            'type' => 'delivery',
            'reference_type' => 'pengajuan',
            'reference_id' => $activeApplication->id,
            'is_read' => false,
        ]);
    }

    /**
     * @return array{0: array<int, Motor>, 1: array<int, JenisCicilan>, 2: array<int, Asuransi>, 3: array<int, MetodeBayar>}
     */
    private function seedMasterData(): array
    {
        $jenisMotors = [
            JenisMotor::updateOrCreate(
                ['jenis' => 'Matic'],
                ['merk' => 'Honda', 'deskripsi_jenis' => 'Motor harian praktis untuk mobilitas perkotaan.', 'image_url' => '/images/motor-matic.svg']
            ),
            JenisMotor::updateOrCreate(
                ['jenis' => 'Sport'],
                ['merk' => 'Yamaha', 'deskripsi_jenis' => 'Motor performa tinggi dengan desain agresif.', 'image_url' => '/images/motor-sport.svg']
            ),
            JenisMotor::updateOrCreate(
                ['jenis' => 'Cub'],
                ['merk' => 'Suzuki', 'deskripsi_jenis' => 'Motor ekonomis dan tangguh untuk kebutuhan keluarga.', 'image_url' => '/images/motor-cub.svg']
            ),
        ];

        $motors = [
            $this->upsertMotor([
                'jenis_motor_id' => $jenisMotors[0]->id,
                'id_jenis' => $jenisMotors[0]->id,
                'nama_motor' => 'Honda Vario 160 ABS',
                'merk' => 'Honda',
                'harga_jual' => 29400000,
                'deskripsi' => 'Skuter premium dengan mesin 160cc, panel digital, dan bagasi luas untuk pemakaian harian.',
                'deskripsi_motor' => 'Skuter premium dengan mesin 160cc, panel digital, dan bagasi luas untuk pemakaian harian.',
                'warna' => 'Matte Black, Grande White, Active Red',
                'kapasitas_mesin' => '160cc',
                'transmisi' => 'CVT',
                'bahan_bakar' => 'Bensin',
                'berat' => 117,
                'tahun_produksi' => 2026,
                'stok' => 8,
                'status_aktif' => true,
                'foto1' => '/images/motor-matic.svg',
                'foto2' => '/images/motor-matic.svg',
                'foto3' => '/images/motor-matic.svg',
                'is_featured' => true,
            ]),
            $this->upsertMotor([
                'jenis_motor_id' => $jenisMotors[0]->id,
                'id_jenis' => $jenisMotors[0]->id,
                'nama_motor' => 'Yamaha NMAX Neo',
                'merk' => 'Yamaha',
                'harga_jual' => 33600000,
                'deskripsi' => 'Skuter touring dengan posisi duduk rileks dan kompartemen penyimpanan besar.',
                'deskripsi_motor' => 'Skuter touring dengan posisi duduk rileks dan kompartemen penyimpanan besar.',
                'warna' => 'Graphite, Silver, Blue',
                'kapasitas_mesin' => '155cc',
                'transmisi' => 'CVT',
                'bahan_bakar' => 'Bensin',
                'berat' => 132,
                'tahun_produksi' => 2026,
                'stok' => 6,
                'status_aktif' => true,
                'foto1' => '/images/motor-sport.svg',
                'foto2' => '/images/motor-sport.svg',
                'foto3' => '/images/motor-sport.svg',
                'is_featured' => true,
            ]),
            $this->upsertMotor([
                'jenis_motor_id' => $jenisMotors[1]->id,
                'id_jenis' => $jenisMotors[1]->id,
                'nama_motor' => 'Yamaha R15 Connected',
                'merk' => 'Yamaha',
                'harga_jual' => 39200000,
                'deskripsi' => 'Motor sport fairing dengan konektivitas smartphone dan desain balap modern.',
                'deskripsi_motor' => 'Motor sport fairing dengan konektivitas smartphone dan desain balap modern.',
                'warna' => 'Icon Blue, Midnight Black',
                'kapasitas_mesin' => '155cc',
                'transmisi' => 'Manual 6-Speed',
                'bahan_bakar' => 'Bensin',
                'berat' => 137,
                'tahun_produksi' => 2026,
                'stok' => 4,
                'status_aktif' => true,
                'foto1' => '/images/motor-sport.svg',
                'foto2' => '/images/motor-sport.svg',
                'foto3' => '/images/motor-sport.svg',
                'is_featured' => true,
            ]),
        ];

        $plans = [
            JenisCicilan::updateOrCreate(['nama_cicilan' => 'Reguler 12 Bulan'], ['durasi_bulan' => 12, 'lama_cicilan' => 12, 'margin_kredit' => 2.40, 'biaya_admin' => 450000]),
            JenisCicilan::updateOrCreate(['nama_cicilan' => 'Reguler 24 Bulan'], ['durasi_bulan' => 24, 'lama_cicilan' => 24, 'margin_kredit' => 2.90, 'biaya_admin' => 500000]),
            JenisCicilan::updateOrCreate(['nama_cicilan' => 'Reguler 36 Bulan'], ['durasi_bulan' => 36, 'lama_cicilan' => 36, 'margin_kredit' => 3.30, 'biaya_admin' => 650000]),
        ];

        $insurances = [
            Asuransi::updateOrCreate(['nama_asuransi' => 'Asuransi Komprehensif'], ['nama_perusahaan_asuransi' => 'Astra Guard', 'margin_asuransi' => 1.60, 'no_rekening' => '8800188881']),
            Asuransi::updateOrCreate(['nama_asuransi' => 'Asuransi Total Loss Only'], ['nama_perusahaan_asuransi' => 'Mandiri Proteksi', 'margin_asuransi' => 1.10, 'no_rekening' => '8800188882']),
        ];

        $paymentMethods = [
            MetodeBayar::updateOrCreate(['metode_pembayaran' => 'Transfer Bank BCA'], ['tempat_bayar' => 'ATM / Mobile Banking / Teller', 'no_rekening' => '1234567890', 'url_logo' => '/images/payment-proof.svg', 'status_aktif' => true]),
            MetodeBayar::updateOrCreate(['metode_pembayaran' => 'Transfer Bank Mandiri'], ['tempat_bayar' => 'ATM / Livin / Teller', 'no_rekening' => '9876543210', 'url_logo' => '/images/payment-proof.svg', 'status_aktif' => true]),
            MetodeBayar::updateOrCreate(['metode_pembayaran' => 'Virtual Account'], ['tempat_bayar' => 'Aplikasi pembayaran digital', 'no_rekening' => '880000112233', 'url_logo' => '/images/payment-proof.svg', 'status_aktif' => true]),
        ];

        foreach ([
            ['question' => 'Apakah simulasi cicilan bersifat final?', 'answer' => 'Tidak. Simulasi pada Credira adalah estimasi awal sebelum verifikasi dan approval final.', 'sort_order' => 1],
            ['question' => 'Dokumen apa saja yang wajib diunggah?', 'answer' => 'Minimal KTP, bukti penghasilan, dan selfie bersama KTP.', 'sort_order' => 2],
            ['question' => 'Berapa lama proses pengajuan?', 'answer' => 'Rata-rata verifikasi awal memakan waktu 1x24 jam kerja.', 'sort_order' => 3],
        ] as $faq) {
            Faq::updateOrCreate(['question' => $faq['question']], $faq + ['is_active' => true]);
        }

        foreach ([
            ['name' => 'Nadia Putri', 'occupation' => 'Karyawan Swasta', 'rating' => 5, 'message' => 'Saya bisa cek simulasi dan status pengajuan tanpa harus datang ke kantor.', 'is_featured' => true],
            ['name' => 'Rizky Pratama', 'occupation' => 'Wiraswasta', 'rating' => 5, 'message' => 'Dashboard angsurannya jelas dan status pembayarannya mudah dipantau.', 'is_featured' => true],
        ] as $testimonial) {
            Testimonial::updateOrCreate(['name' => $testimonial['name']], $testimonial);
        }

        return [$motors, $plans, $insurances, $paymentMethods];
    }

    private function upsertMotor(array $attributes): Motor
    {
        $motor = Motor::updateOrCreate(['nama_motor' => $attributes['nama_motor']], $attributes);

        MotorImage::updateOrCreate(
            ['motor_id' => $motor->id, 'sort_order' => 1],
            ['image_url' => $attributes['foto1'], 'caption' => $motor->nama_motor.' - tampilan utama']
        );

        MotorImage::updateOrCreate(
            ['motor_id' => $motor->id, 'sort_order' => 2],
            ['image_url' => $attributes['foto2'], 'caption' => $motor->nama_motor.' - sisi samping']
        );

        return $motor;
    }

    private function ensurePrimaryAddress(Pelanggan $pelanggan): PelangganAddress
    {
        $primaryAddress = $pelanggan->addresses()->where('is_primary', true)->first();

        if ($primaryAddress) {
            return $primaryAddress;
        }

        $address = $pelanggan->addresses()->create([
            'label_alamat' => 'Rumah',
            'penerima' => $pelanggan->nama_lengkap ?: $pelanggan->user->name,
            'no_telp' => $pelanggan->no_telp ?: '081234567890',
            'alamat_lengkap' => 'Jl. Melati Raya No. 12',
            'kota' => 'Jakarta Selatan',
            'provinsi' => 'DKI Jakarta',
            'kode_pos' => '12950',
            'is_primary' => true,
        ]);

        LegacyDiagramSync::syncPelanggan($pelanggan->fresh('addresses'));

        return $address;
    }

    private function seedActiveApplication(
        CreditSimulationService $simulationService,
        Pelanggan $pelanggan,
        PelangganAddress $primaryAddress,
        Motor $motor,
        JenisCicilan $plan,
        Asuransi $insurance,
        MetodeBayar $paymentMethod,
        $user,
    ): PengajuanKredit {
        $simulation = $simulationService->calculate($motor, $plan, $insurance, 6000000);
        $application = PengajuanKredit::create([
            'kode_pengajuan' => 'CRD-START-001',
            'tgl_pengajuan' => Carbon::now()->subDays(18)->toDateString(),
            'pelanggan_id' => $pelanggan->id,
            'id_pelanggan' => $pelanggan->id,
            'motor_id' => $motor->id,
            'id_motor' => $motor->id,
            'jenis_cicilan_id' => $plan->id,
            'id_jenis_cicilan' => $plan->id,
            'asuransi_id' => $insurance->id,
            'id_asuransi' => $insurance->id,
            'harga_cash' => $simulation['harga_motor'],
            'dp' => $simulation['dp'],
            'pokok_kredit' => $simulation['pokok_kredit'],
            'harga_kredit' => $simulation['pokok_kredit'],
            'margin_kredit' => $simulation['margin_amount'],
            'biaya_admin' => $simulation['biaya_admin'],
            'biaya_asuransi' => $simulation['biaya_asuransi'],
            'biaya_asuransi_perbulan' => round($simulation['biaya_asuransi'] / $plan->durasi_bulan, 2),
            'cicilan_perbulan' => $simulation['angsuran_per_bulan'],
            'total_bayar' => $simulation['total_bayar'],
            'status_pengajuan' => ApplicationStatus::KontrakAktif->value,
            'catatan_status' => 'Pengajuan telah disetujui dan kontrak aktif.',
            'keterangan_status_pengajuan' => 'Pengajuan telah disetujui dan kontrak aktif.',
            'submitted_at' => Carbon::now()->subDays(18),
            'approved_at' => Carbon::now()->subDays(12),
            'snapshot_data' => $this->snapshotData($pelanggan, $primaryAddress),
        ]);

        PengajuanDetailFinansial::create([
            'pengajuan_id' => $application->id,
            'pekerjaan' => $pelanggan->pekerjaan_default ?: 'Karyawan Swasta',
            'nama_perusahaan' => 'PT Solusi Performa Nusantara',
            'alamat_kantor' => 'Jl. Gatot Subroto No. 88, Jakarta Selatan',
            'lama_bekerja' => '4 tahun',
            'penghasilan_bulanan' => $pelanggan->penghasilan_default ?: 9000000,
            'pengeluaran_bulanan' => 3500000,
            'status_rumah' => 'Keluarga',
            'kontak_darurat_nama' => 'Rina Pratama',
            'kontak_darurat_nohp' => '081299887766',
            'kontak_darurat_hubungan' => 'Saudara',
        ]);

        $this->seedLogs($application, $user->id, [
            [null, ApplicationStatus::MenungguKonfirmasi->value, 'Pengajuan berhasil dikirim.', Carbon::now()->subDays(18)],
            [ApplicationStatus::MenungguKonfirmasi->value, ApplicationStatus::VerifikasiDokumen->value, 'Dokumen dinyatakan lengkap.', Carbon::now()->subDays(17)],
            [ApplicationStatus::VerifikasiDokumen->value, ApplicationStatus::Diproses->value, 'Pengajuan masuk tahap analisis kredit.', Carbon::now()->subDays(15)],
            [ApplicationStatus::Diproses->value, ApplicationStatus::Disetujui->value, 'Pengajuan disetujui.', Carbon::now()->subDays(12)],
            [ApplicationStatus::Disetujui->value, ApplicationStatus::KontrakAktif->value, 'Kontrak aktif dan angsuran dibentuk.', Carbon::now()->subDays(11)],
        ]);

        foreach (range(1, $plan->durasi_bulan) as $index) {
            $dueDate = Carbon::now()->startOfMonth()->addMonths($index - 2)->addDays(4);
            $status = match (true) {
                $index === 1 => InstallmentPaymentStatus::SudahBayar->value,
                $index === 2 => InstallmentPaymentStatus::MenungguVerifikasi->value,
                $dueDate->isPast() => InstallmentPaymentStatus::Telat->value,
                default => InstallmentPaymentStatus::BelumBayar->value,
            };
            $denda = $status === InstallmentPaymentStatus::Telat->value ? 75000 : 0;

            $installment = Angsuran::create([
                'pengajuan_id' => $application->id,
                'id_kredit' => $application->id,
                'angsuran_ke' => $index,
                'tanggal_jatuh_tempo' => $dueDate,
                'nominal_angsuran' => $application->cicilan_perbulan,
                'denda' => $denda,
                'total_tagihan' => $application->cicilan_perbulan + $denda,
                'total_bayar' => $application->cicilan_perbulan + $denda,
                'status_pembayaran' => $status,
                'tanggal_bayar' => in_array($status, [InstallmentPaymentStatus::SudahBayar->value, InstallmentPaymentStatus::MenungguVerifikasi->value], true) ? $dueDate->copy()->subDay() : null,
                'tgl_bayar' => in_array($status, [InstallmentPaymentStatus::SudahBayar->value, InstallmentPaymentStatus::MenungguVerifikasi->value], true) ? $dueDate->copy()->subDay()->toDateString() : null,
                'metode_bayar' => in_array($status, [InstallmentPaymentStatus::SudahBayar->value, InstallmentPaymentStatus::MenungguVerifikasi->value], true) ? $paymentMethod->metode_pembayaran : null,
                'keterangan' => $status === InstallmentPaymentStatus::SudahBayar->value ? 'Angsuran telah dibayar.' : null,
                'verified_by' => $status === InstallmentPaymentStatus::SudahBayar->value ? $user->id : null,
                'verified_at' => $status === InstallmentPaymentStatus::SudahBayar->value ? $dueDate->copy()->subDay() : null,
            ]);

            if (! in_array($status, [InstallmentPaymentStatus::SudahBayar->value, InstallmentPaymentStatus::MenungguVerifikasi->value], true)) {
                continue;
            }

            Pembayaran::create([
                'angsuran_id' => $installment->id,
                'pelanggan_id' => $pelanggan->id,
                'kode_pembayaran' => sprintf('PAY-START-%03d', $index),
                'id_metode_bayar' => $paymentMethod->id,
                'metode_bayar' => $paymentMethod->metode_pembayaran,
                'tempat_bayar' => $paymentMethod->tempat_bayar,
                'no_rekening_tujuan' => $paymentMethod->no_rekening,
                'url_logo_metode' => $paymentMethod->url_logo,
                'nama_bank_pengirim' => 'BCA',
                'nama_pemilik_rekening' => $pelanggan->nama_lengkap ?: $user->name,
                'nominal_bayar' => $installment->total_tagihan,
                'tanggal_bayar' => $dueDate->copy()->subDay(),
                'bukti_bayar' => '/images/payment-proof.svg',
                'status_verifikasi' => $status === InstallmentPaymentStatus::SudahBayar->value ? PaymentVerificationStatus::Valid->value : PaymentVerificationStatus::Pending->value,
                'catatan_verifikasi' => $status === InstallmentPaymentStatus::SudahBayar->value ? 'Pembayaran sudah diverifikasi.' : 'Menunggu pengecekan admin.',
                'verified_by' => $status === InstallmentPaymentStatus::SudahBayar->value ? $user->id : null,
                'verified_at' => $status === InstallmentPaymentStatus::SudahBayar->value ? $dueDate->copy()->subDay() : null,
            ]);
        }

        Pengiriman::create([
            'pengajuan_id' => $application->id,
            'id_kredit' => $application->id,
            'invoice' => 'INV-START-001',
            'no_invoice' => 'INV-START-001',
            'alamat_pengiriman_id' => $primaryAddress->id,
            'alamat_tujuan' => $primaryAddress->alamat_lengkap.', '.$primaryAddress->kota.', '.$primaryAddress->provinsi.' '.$primaryAddress->kode_pos,
            'tgl_kirim' => Carbon::now()->subDays(2),
            'tgl_tiba' => Carbon::now()->addDays(1),
            'status_kirim' => DeliveryStatus::Dikirim->value,
            'nama_kurir' => 'Budi Hartono',
            'telpon_kurir' => '081377788899',
            'bukti_foto' => '/images/delivery-proof.svg',
            'keterangan' => 'Motor sedang dalam perjalanan menuju alamat pengiriman utama.',
            'nama_penerima' => $pelanggan->nama_lengkap ?: $user->name,
        ]);

        return $application;
    }

    private function seedPendingApplication(
        CreditSimulationService $simulationService,
        Pelanggan $pelanggan,
        PelangganAddress $primaryAddress,
        Motor $motor,
        JenisCicilan $plan,
        $user,
    ): PengajuanKredit {
        $simulation = $simulationService->calculate($motor, $plan, null, 8000000);
        $application = PengajuanKredit::create([
            'kode_pengajuan' => 'CRD-START-002',
            'tgl_pengajuan' => Carbon::now()->subDays(2)->toDateString(),
            'pelanggan_id' => $pelanggan->id,
            'id_pelanggan' => $pelanggan->id,
            'motor_id' => $motor->id,
            'id_motor' => $motor->id,
            'jenis_cicilan_id' => $plan->id,
            'id_jenis_cicilan' => $plan->id,
            'asuransi_id' => null,
            'id_asuransi' => null,
            'harga_cash' => $simulation['harga_motor'],
            'dp' => $simulation['dp'],
            'pokok_kredit' => $simulation['pokok_kredit'],
            'harga_kredit' => $simulation['pokok_kredit'],
            'margin_kredit' => $simulation['margin_amount'],
            'biaya_admin' => $simulation['biaya_admin'],
            'biaya_asuransi' => 0,
            'biaya_asuransi_perbulan' => 0,
            'cicilan_perbulan' => $simulation['angsuran_per_bulan'],
            'total_bayar' => $simulation['total_bayar'],
            'status_pengajuan' => ApplicationStatus::VerifikasiDokumen->value,
            'catatan_status' => 'Dokumen sedang ditinjau tim verifikasi.',
            'keterangan_status_pengajuan' => 'Dokumen sedang ditinjau tim verifikasi.',
            'submitted_at' => Carbon::now()->subDays(2),
            'snapshot_data' => $this->snapshotData($pelanggan, $primaryAddress),
        ]);

        PengajuanDetailFinansial::create([
            'pengajuan_id' => $application->id,
            'pekerjaan' => $pelanggan->pekerjaan_default ?: 'Karyawan Swasta',
            'nama_perusahaan' => 'PT Solusi Performa Nusantara',
            'alamat_kantor' => 'Jl. Gatot Subroto No. 88, Jakarta Selatan',
            'lama_bekerja' => '4 tahun',
            'penghasilan_bulanan' => $pelanggan->penghasilan_default ?: 9000000,
            'pengeluaran_bulanan' => 4100000,
            'status_rumah' => 'Kontrak',
            'kontak_darurat_nama' => 'Rina Pratama',
            'kontak_darurat_nohp' => '081299887766',
            'kontak_darurat_hubungan' => 'Saudara',
        ]);

        $this->seedLogs($application, $user->id, [
            [null, ApplicationStatus::MenungguKonfirmasi->value, 'Pengajuan baru dibuat.', Carbon::now()->subDays(2)],
            [ApplicationStatus::MenungguKonfirmasi->value, ApplicationStatus::VerifikasiDokumen->value, 'Dokumen sedang ditinjau admin.', Carbon::now()->subDay()],
        ]);

        return $application;
    }

    private function seedLogs(PengajuanKredit $application, int $changedBy, array $logs): void
    {
        foreach ($logs as [$oldStatus, $newStatus, $note, $time]) {
            PengajuanLog::create([
                'pengajuan_id' => $application->id,
                'status_lama' => $oldStatus,
                'status_baru' => $newStatus,
                'catatan' => $note,
                'changed_by' => $changedBy,
                'created_at' => $time,
                'updated_at' => $time,
            ]);
        }
    }

    private function snapshotData(Pelanggan $pelanggan, PelangganAddress $address): array
    {
        return [
            'personal' => [
                'nama_lengkap' => $pelanggan->nama_lengkap ?: $pelanggan->user->name,
                'no_ktp' => $pelanggan->no_ktp ?: '3175000000000001',
                'email' => $pelanggan->email ?: $pelanggan->user->email,
                'nomor_hp' => $pelanggan->no_telp ?: '081234567890',
                'tempat_lahir' => $pelanggan->tempat_lahir ?: 'Jakarta',
                'tanggal_lahir' => optional($pelanggan->tanggal_lahir)->toDateString() ?: '1998-03-12',
                'jenis_kelamin' => $pelanggan->jenis_kelamin ?: 'Perempuan',
                'status_pernikahan' => $pelanggan->status_pernikahan ?: 'Belum Menikah',
                'alamat_lengkap' => $address->alamat_lengkap,
                'kota' => $address->kota,
                'provinsi' => $address->provinsi,
                'kode_pos' => $address->kode_pos,
            ],
        ];
    }
}

<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Enums\DeliveryStatus;
use App\Enums\InstallmentPaymentStatus;
use App\Enums\PaymentVerificationStatus;
use App\Models\Angsuran;
use App\Models\Asuransi;
use App\Models\ContactMessage;
use App\Models\DokumenPengajuan;
use App\Models\Faq;
use App\Models\JenisCicilan;
use App\Models\JenisMotor;
use App\Models\MetodeBayar;
use App\Models\Motor;
use App\Models\MotorImage;
use App\Models\Notification;
use App\Models\Pelanggan;
use App\Models\PelangganAddress;
use App\Models\Pembayaran;
use App\Models\PengajuanDetailFinansial;
use App\Models\PengajuanKredit;
use App\Models\PengajuanLog;
use App\Models\Pengiriman;
use App\Models\Testimonial;
use App\Models\User;
use App\Services\CreditSimulationService;
use App\Support\LegacyDiagramSync;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class CrediraSeeder extends Seeder
{
    public function run(): void
    {
        $simulationService = app(CreditSimulationService::class);

        $users = $this->seedUsers();
        $master = $this->seedMasterData();
        $pelanggans = $this->seedPelangganData($users['customers'], $users['marketing']);
        $applications = $this->seedApplications($simulationService, $pelanggans, $master, $users);

        $this->seedNotifications($applications, $users['admin']);
        $this->seedContent();
    }

    private function seedUsers(): array
    {
        $password = Hash::make('password');

        return [
            'admin' => User::updateOrCreate(
                ['email' => 'admin@credira.test'],
                ['name' => 'Credira Admin', 'role' => User::ROLE_ADMIN, 'password' => $password, 'email_verified_at' => now()]
            ),
            'ceo' => User::updateOrCreate(
                ['email' => 'ceo@credira.test'],
                ['name' => 'Credira CEO', 'role' => User::ROLE_CEO, 'password' => $password, 'email_verified_at' => now()]
            ),
            'marketing' => [
                User::updateOrCreate(
                    ['email' => 'marketing1@credira.test'],
                    ['name' => 'Marketing Satu', 'role' => User::ROLE_MARKETING, 'password' => $password, 'email_verified_at' => now()]
                ),
                User::updateOrCreate(
                    ['email' => 'marketing2@credira.test'],
                    ['name' => 'Marketing Dua', 'role' => User::ROLE_MARKETING, 'password' => $password, 'email_verified_at' => now()]
                ),
            ],
            'customers' => [
                User::updateOrCreate(
                    ['email' => 'alya@credira.test'],
                    ['name' => 'Alya Rahma', 'role' => 'user', 'password' => $password, 'email_verified_at' => now()]
                ),
                User::updateOrCreate(
                    ['email' => 'rizky@credira.test'],
                    ['name' => 'Rizky Pratama', 'role' => 'user', 'password' => $password, 'email_verified_at' => now()]
                ),
                User::updateOrCreate(
                    ['email' => 'nadia@credira.test'],
                    ['name' => 'Nadia Putri', 'role' => 'user', 'password' => $password, 'email_verified_at' => now()]
                ),
            ],
        ];
    }

    private function seedMasterData(): array
    {
        $jenis = [
            'matic' => JenisMotor::updateOrCreate(['jenis' => 'Matic'], ['merk' => 'Honda', 'deskripsi_jenis' => 'Motor harian praktis.', 'image_url' => '/images/motor-matic.svg']),
            'sport' => JenisMotor::updateOrCreate(['jenis' => 'Sport'], ['merk' => 'Yamaha', 'deskripsi_jenis' => 'Motor performa tinggi.', 'image_url' => '/images/motor-sport.svg']),
            'touring' => JenisMotor::updateOrCreate(['jenis' => 'Premium Touring'], ['merk' => 'Honda', 'deskripsi_jenis' => 'Motor premium untuk perjalanan.', 'image_url' => '/images/motor-sport.svg']),
        ];

        $motors = [
            'vario' => $this->upsertMotor('Honda Vario 160 ABS', $jenis['matic']->id, 'Honda', 29400000, 'Matte Black, Grande White, Active Red', '160cc', 'CVT', true, '/images/motor-matic.svg'),
            'pcx' => $this->upsertMotor('Honda PCX 160', $jenis['matic']->id, 'Honda', 33250000, 'Roadsync Matte Gray, White', '160cc', 'CVT', true, '/images/motor-matic.svg'),
            'nmax' => $this->upsertMotor('Yamaha NMAX Neo', $jenis['matic']->id, 'Yamaha', 33600000, 'Graphite, Silver, Blue', '155cc', 'CVT', true, '/images/motor-sport.svg'),
            'aerox' => $this->upsertMotor('Yamaha Aerox', $jenis['matic']->id, 'Yamaha', 31100000, 'Cyber City, Black Yellow', '155cc', 'CVT', false, '/images/motor-sport.svg'),
            'r15' => $this->upsertMotor('Yamaha R15 Connected', $jenis['sport']->id, 'Yamaha', 39200000, 'Icon Blue, Midnight Black', '155cc', 'Manual 6-Speed', true, '/images/motor-sport.svg'),
            'adv' => $this->upsertMotor('Honda ADV 160', $jenis['touring']->id, 'Honda', 36850000, 'Dynamic Black, Tough Matte Red', '160cc', 'CVT', true, '/images/motor-sport.svg'),
        ];

        $plans = [
            JenisCicilan::updateOrCreate(['nama_cicilan' => '12 bulan'], ['durasi_bulan' => 12, 'lama_cicilan' => 12, 'margin_kredit' => 2.40, 'biaya_admin' => 450000]),
            JenisCicilan::updateOrCreate(['nama_cicilan' => '24 bulan'], ['durasi_bulan' => 24, 'lama_cicilan' => 24, 'margin_kredit' => 2.90, 'biaya_admin' => 500000]),
            JenisCicilan::updateOrCreate(['nama_cicilan' => '36 bulan'], ['durasi_bulan' => 36, 'lama_cicilan' => 36, 'margin_kredit' => 3.30, 'biaya_admin' => 650000]),
        ];

        $insurances = [
            Asuransi::updateOrCreate(['nama_asuransi' => 'Asuransi Komprehensif'], ['nama_perusahaan_asuransi' => 'Astra Guard', 'margin_asuransi' => 1.60, 'no_rekening' => '8800188881']),
            Asuransi::updateOrCreate(['nama_asuransi' => 'Asuransi Total Loss Only'], ['nama_perusahaan_asuransi' => 'Mandiri Proteksi', 'margin_asuransi' => 1.10, 'no_rekening' => '8800188882']),
        ];

        $paymentMethods = [
            MetodeBayar::updateOrCreate(['metode_pembayaran' => 'Transfer Bank BCA'], ['tempat_bayar' => 'ATM / Mobile Banking / Teller', 'no_rekening' => '1234567890', 'url_logo' => '/images/payment-proof.svg', 'status_aktif' => true]),
            MetodeBayar::updateOrCreate(['metode_pembayaran' => 'Transfer Bank Mandiri'], ['tempat_bayar' => 'ATM / Livin / Teller', 'no_rekening' => '9876543210', 'url_logo' => '/images/payment-proof.svg', 'status_aktif' => true]),
        ];

        return compact('motors', 'plans', 'insurances', 'paymentMethods');
    }

    private function seedPelangganData(array $customerUsers, array $marketingUsers): array
    {
        $definitions = [
            [$customerUsers[0], 'Alya Rahma', '3175091203980001', '081234567890', '1998-03-12', 'Jakarta', 'Perempuan', 'Belum Menikah', 'UI Designer', 8500000, 'Jl. Cempaka Raya No. 18', 'Jakarta Pusat', 'DKI Jakarta', '10510'],
            [$customerUsers[1], 'Rizky Pratama', '3276010101950002', '081298765432', '1995-01-01', 'Bandung', 'Laki-laki', 'Menikah', 'Wiraswasta', 12000000, 'Jl. Sukajadi No. 55', 'Bandung', 'Jawa Barat', '40162'],
            [$customerUsers[2], 'Nadia Putri', '3578012302990003', '081377788899', '1999-02-23', 'Surabaya', 'Perempuan', 'Belum Menikah', 'Karyawan Swasta', 7800000, 'Jl. Manyar Kertoarjo No. 12', 'Surabaya', 'Jawa Timur', '60284'],
        ];

        $pelanggans = [];

        foreach ($definitions as $index => [$user, $nama, $ktp, $telp, $lahir, $tempat, $gender, $status, $job, $income, $alamat, $kota, $provinsi, $kodePos]) {
            $marketingOwner = $marketingUsers[$index] ?? null;
            $pelanggan = Pelanggan::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'marketing_user_id' => $marketingOwner?->id,
                    'nama_lengkap' => $nama,
                    'nama_pelanggan' => $nama,
                    'email' => $user->email,
                    'kata_sandi' => $user->password,
                    'no_ktp' => $ktp,
                    'no_telp' => $telp,
                    'tanggal_lahir' => $lahir,
                    'tempat_lahir' => $tempat,
                    'jenis_kelamin' => $gender,
                    'status_pernikahan' => $status,
                    'pekerjaan_default' => $job,
                    'penghasilan_default' => $income,
                ]
            );

            PelangganAddress::updateOrCreate(
                ['pelanggan_id' => $pelanggan->id, 'label_alamat' => 'Rumah'],
                [
                    'penerima' => $nama,
                    'no_telp' => $telp,
                    'alamat_lengkap' => $alamat,
                    'kota' => $kota,
                    'provinsi' => $provinsi,
                    'kode_pos' => $kodePos,
                    'is_primary' => true,
                ]
            );

            LegacyDiagramSync::syncPelanggan($pelanggan->fresh('addresses'));
            $pelanggans[] = $pelanggan->fresh('addresses', 'user');
        }

        return $pelanggans;
    }

    private function seedApplications(CreditSimulationService $simulationService, array $pelanggans, array $master, array $users): array
    {
        $definitions = [
            ['CRD-PENDING-001', $pelanggans[0], $master['motors']['vario'], $master['plans'][0], $master['insurances'][0], 6000000, ApplicationStatus::MenungguKonfirmasi->value, 'Pengajuan baru masuk dan menunggu review awal.', now()->subDays(1), null, null, $users['marketing'][0]],
            ['CRD-REVIEW-001', $pelanggans[1], $master['motors']['nmax'], $master['plans'][1], $master['insurances'][1], 7000000, ApplicationStatus::Diproses->value, 'Pengajuan sedang ditinjau oleh tim marketing.', now()->subDays(4), null, null, $users['marketing'][1]],
            ['CRD-APPROVED-001', $pelanggans[2], $master['motors']['adv'], $master['plans'][2], $master['insurances'][0], 9000000, ApplicationStatus::KontrakAktif->value, 'Pengajuan telah disetujui dan kontrak aktif.', now()->subDays(12), now()->subDays(8), null, $users['admin']],
            ['CRD-REJECTED-001', $pelanggans[0], $master['motors']['r15'], $master['plans'][1], null, 8000000, ApplicationStatus::Ditolak->value, 'Pengajuan ditolak setelah proses verifikasi.', now()->subDays(10), null, now()->subDays(7), $users['admin']],
        ];

        $applications = [];

        foreach ($definitions as [$kode, $pelanggan, $motor, $plan, $insurance, $dp, $status, $note, $submittedAt, $approvedAt, $rejectedAt, $changer]) {
            $simulation = $simulationService->calculate($motor, $plan, $insurance, $dp);
            $address = $pelanggan->addresses->firstWhere('is_primary', true) ?? $pelanggan->addresses->first();

            $application = PengajuanKredit::updateOrCreate(
                ['kode_pengajuan' => $kode],
                [
                    'tgl_pengajuan' => Carbon::parse($submittedAt)->toDateString(),
                    'pelanggan_id' => $pelanggan->id,
                    'marketing_user_id' => $changer->role === User::ROLE_MARKETING ? $changer->id : $pelanggan->marketing_user_id,
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
                    'status_pengajuan' => $status,
                    'catatan_status' => $note,
                    'keterangan_status_pengajuan' => $note,
                    'submitted_at' => $submittedAt,
                    'approved_at' => $approvedAt,
                    'rejected_at' => $rejectedAt,
                    'snapshot_data' => [
                        'pelanggan' => ['nama_lengkap' => $pelanggan->display_name, 'email' => $pelanggan->email, 'no_telp' => $pelanggan->no_telp],
                        'alamat' => $address ? ['alamat_lengkap' => $address->alamat_lengkap, 'kota' => $address->kota, 'provinsi' => $address->provinsi, 'kode_pos' => $address->kode_pos] : null,
                    ],
                    'url_ktp' => '/dummy/dokumen/ktp-'.$pelanggan->id.'.jpg',
                    'url_npwp' => '/dummy/dokumen/npwp-'.$pelanggan->id.'.jpg',
                    'url_slip_gaji' => '/dummy/dokumen/slip-gaji-'.$pelanggan->id.'.pdf',
                    'url_foto' => '/dummy/dokumen/selfie-'.$pelanggan->id.'.jpg',
                ]
            );

            PengajuanDetailFinansial::updateOrCreate(
                ['pengajuan_id' => $application->id],
                [
                    'pekerjaan' => $pelanggan->pekerjaan_default,
                    'nama_perusahaan' => 'PT Credira Dummy Partner',
                    'alamat_kantor' => 'Jl. Pembiayaan No. 12',
                    'lama_bekerja' => '3 tahun',
                    'penghasilan_bulanan' => $pelanggan->penghasilan_default,
                    'pengeluaran_bulanan' => (int) round(($pelanggan->penghasilan_default ?? 0) * 0.45),
                    'status_rumah' => 'Milik keluarga',
                    'kontak_darurat_nama' => 'Kontak '.$pelanggan->display_name,
                    'kontak_darurat_nohp' => '08120000000'.$pelanggan->id,
                    'kontak_darurat_hubungan' => 'Saudara',
                ]
            );

            $this->seedApplicationDocuments($application, $status);
            $this->seedApplicationLog($application, $note, $status, $changer->id, $submittedAt);
            LegacyDiagramSync::syncPengajuan($application->fresh('jenisCicilan'));

            if ($status === ApplicationStatus::KontrakAktif->value) {
                $this->seedActiveContractData($application, $master['paymentMethods'][0], $users['admin'], $address);
            }

            $applications[] = $application->fresh('pelanggan');
        }

        return $applications;
    }

    private function seedApplicationDocuments(PengajuanKredit $application, string $status): void
    {
        foreach ([['foto_ktp', 'ktp.jpg'], ['slip_gaji', 'slip-gaji.pdf'], ['foto_selfie_ktp', 'selfie-ktp.jpg']] as [$jenis, $file]) {
            DokumenPengajuan::updateOrCreate(
                ['pengajuan_id' => $application->id, 'jenis_dokumen' => $jenis],
                [
                    'nama_file' => $file,
                    'path_file' => '/dummy/dokumen/'.$application->kode_pengajuan.'-'.$file,
                    'status_verifikasi' => $status === ApplicationStatus::Ditolak->value ? 'gagal_verifikasi' : 'valid',
                    'catatan_verifikasi' => 'Dummy document seeded.',
                    'uploaded_at' => $application->submitted_at,
                    'verified_at' => $application->submitted_at,
                ]
            );
        }
    }

    private function seedApplicationLog(PengajuanKredit $application, string $note, string $status, int $changedBy, Carbon $time): void
    {
        PengajuanLog::updateOrCreate(
            ['pengajuan_id' => $application->id, 'status_baru' => $status],
            ['status_lama' => null, 'catatan' => $note, 'changed_by' => $changedBy, 'created_at' => $time, 'updated_at' => $time]
        );
    }

    private function seedActiveContractData(PengajuanKredit $application, MetodeBayar $paymentMethod, User $admin, ?PelangganAddress $address): void
    {
        foreach (range(1, 3) as $index) {
            $dueDate = now()->startOfMonth()->addMonths($index - 1)->addDays(6);
            $status = match ($index) {
                1 => InstallmentPaymentStatus::SudahBayar->value,
                2 => InstallmentPaymentStatus::MenungguVerifikasi->value,
                default => InstallmentPaymentStatus::BelumBayar->value,
            };

            $angsuran = Angsuran::updateOrCreate(
                ['pengajuan_id' => $application->id, 'angsuran_ke' => $index],
                [
                    'id_kredit' => $application->id,
                    'tanggal_jatuh_tempo' => $dueDate,
                    'nominal_angsuran' => $application->cicilan_perbulan,
                    'denda' => 0,
                    'total_tagihan' => $application->cicilan_perbulan,
                    'total_bayar' => $application->cicilan_perbulan,
                    'status_pembayaran' => $status,
                    'tanggal_bayar' => $status !== InstallmentPaymentStatus::BelumBayar->value ? $dueDate->copy()->subDay() : null,
                    'tgl_bayar' => $status !== InstallmentPaymentStatus::BelumBayar->value ? $dueDate->copy()->subDay()->toDateString() : null,
                    'metode_bayar' => $status !== InstallmentPaymentStatus::BelumBayar->value ? $paymentMethod->metode_pembayaran : null,
                    'keterangan' => 'Dummy angsuran seeded.',
                    'verified_by' => $status === InstallmentPaymentStatus::SudahBayar->value ? $admin->id : null,
                    'verified_at' => $status === InstallmentPaymentStatus::SudahBayar->value ? $dueDate->copy()->subDay() : null,
                ]
            );

            LegacyDiagramSync::syncInstallment($angsuran);

            if ($status === InstallmentPaymentStatus::BelumBayar->value) {
                continue;
            }

            Pembayaran::updateOrCreate(
                ['kode_pembayaran' => sprintf('PAY-%s-%02d', $application->id, $index)],
                [
                    'angsuran_id' => $angsuran->id,
                    'pelanggan_id' => $application->pelanggan_id,
                    'id_metode_bayar' => $paymentMethod->id,
                    'metode_bayar' => $paymentMethod->metode_pembayaran,
                    'tempat_bayar' => $paymentMethod->tempat_bayar,
                    'no_rekening_tujuan' => $paymentMethod->no_rekening,
                    'url_logo_metode' => $paymentMethod->url_logo,
                    'nama_bank_pengirim' => 'BCA',
                    'nama_pemilik_rekening' => $application->pelanggan->display_name,
                    'nominal_bayar' => $angsuran->total_tagihan,
                    'tanggal_bayar' => $dueDate->copy()->subDay()->toDateString(),
                    'bukti_bayar' => '/dummy/pembayaran/'.$application->kode_pengajuan.'-'.$index.'.jpg',
                    'status_verifikasi' => $status === InstallmentPaymentStatus::SudahBayar->value ? PaymentVerificationStatus::Valid->value : PaymentVerificationStatus::Pending->value,
                    'catatan_verifikasi' => 'Dummy pembayaran seeded.',
                    'verified_by' => $status === InstallmentPaymentStatus::SudahBayar->value ? $admin->id : null,
                    'verified_at' => $status === InstallmentPaymentStatus::SudahBayar->value ? $dueDate->copy()->subDay() : null,
                ]
            );
        }

        Pengiriman::updateOrCreate(
            ['pengajuan_id' => $application->id],
            [
                'id_kredit' => $application->id,
                'invoice' => 'INV-'.$application->kode_pengajuan,
                'no_invoice' => 'INV-'.$application->kode_pengajuan,
                'alamat_pengiriman_id' => $address?->id,
                'alamat_tujuan' => $address ? $address->alamat_lengkap.', '.$address->kota.', '.$address->provinsi.' '.$address->kode_pos : 'Alamat dummy pengiriman',
                'tgl_kirim' => now()->subDay()->toDateString(),
                'tgl_tiba' => now()->addDays(2)->toDateString(),
                'status_kirim' => DeliveryStatus::Dikirim->value,
                'nama_kurir' => 'Kurir Dummy',
                'telpon_kurir' => '081355566677',
                'bukti_foto' => '/dummy/pengiriman/'.$application->kode_pengajuan.'.jpg',
                'keterangan' => 'Dummy pengiriman seeded.',
                'nama_penerima' => $application->pelanggan->display_name,
            ]
        );
    }

    private function seedNotifications(array $applications, User $admin): void
    {
        foreach ($applications as $application) {
            Notification::updateOrCreate(
                ['user_id' => $application->pelanggan->user_id, 'title' => 'Update pengajuan '.$application->kode_pengajuan],
                ['message' => 'Status pengajuan Anda saat ini: '.$application->status_badge.'.', 'type' => 'application', 'reference_type' => 'pengajuan_kredit', 'reference_id' => $application->id, 'is_read' => false]
            );
        }

        Notification::updateOrCreate(
            ['user_id' => $admin->id, 'title' => 'Laporan seed selesai'],
            ['message' => 'Data dummy Credira berhasil disiapkan.', 'type' => 'info', 'reference_type' => null, 'reference_id' => null, 'is_read' => false]
        );
    }

    private function seedContent(): void
    {
        foreach ([
            ['question' => 'Apakah simulasi cicilan bersifat final?', 'answer' => 'Tidak. Simulasi pada Credira adalah estimasi awal sebelum verifikasi dan approval final.', 'sort_order' => 1, 'is_active' => true],
            ['question' => 'Dokumen apa saja yang wajib diunggah?', 'answer' => 'Minimal KTP, bukti penghasilan, dan selfie bersama KTP.', 'sort_order' => 2, 'is_active' => true],
            ['question' => 'Berapa lama proses pengajuan?', 'answer' => 'Rata-rata verifikasi awal memakan waktu 1x24 jam kerja.', 'sort_order' => 3, 'is_active' => true],
        ] as $faq) {
            Faq::updateOrCreate(['question' => $faq['question']], $faq);
        }

        foreach ([
            ['name' => 'Nadia Putri', 'occupation' => 'Karyawan Swasta', 'rating' => 5, 'message' => 'Saya bisa cek simulasi dan status pengajuan tanpa harus datang ke kantor.', 'is_featured' => true],
            ['name' => 'Rizky Pratama', 'occupation' => 'Wiraswasta', 'rating' => 5, 'message' => 'Dashboard angsurannya jelas dan status pembayarannya mudah dipantau.', 'is_featured' => true],
        ] as $testimonial) {
            Testimonial::updateOrCreate(['name' => $testimonial['name']], $testimonial);
        }

        ContactMessage::updateOrCreate(
            ['email' => 'lead@contoh.test', 'subject' => 'Kerja sama dealer'],
            ['name' => 'Dimas Saputra', 'phone' => '081300000111', 'message' => 'Ingin diskusi integrasi dealer dengan Credira.', 'status' => 'baru']
        );
    }

    private function upsertMotor(string $name, int $jenisId, string $merk, int $harga, string $warna, string $mesin, string $transmisi, bool $featured, string $image): Motor
    {
        $motor = Motor::updateOrCreate(
            ['nama_motor' => $name],
            [
                'jenis_motor_id' => $jenisId,
                'id_jenis' => $jenisId,
                'merk' => $merk,
                'harga_jual' => $harga,
                'deskripsi' => $name.' adalah unit dummy untuk kebutuhan pengujian sistem Credira.',
                'deskripsi_motor' => $name.' adalah unit dummy untuk kebutuhan pengujian sistem Credira.',
                'warna' => $warna,
                'kapasitas_mesin' => $mesin,
                'transmisi' => $transmisi,
                'bahan_bakar' => 'Bensin',
                'berat' => 130,
                'tahun_produksi' => 2026,
                'stok' => 5,
                'status_aktif' => true,
                'foto1' => $image,
                'foto2' => $image,
                'foto3' => $image,
                'is_featured' => $featured,
            ]
        );

        foreach ([1, 2, 3] as $sortOrder) {
            MotorImage::updateOrCreate(
                ['motor_id' => $motor->id, 'sort_order' => $sortOrder],
                ['image_url' => $image, 'caption' => $motor->nama_motor.' - gambar '.$sortOrder]
            );
        }

        return $motor;
    }
}

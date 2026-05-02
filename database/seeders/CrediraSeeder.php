<?php

namespace Database\Seeders;

use App\Models\Asuransi;
use App\Models\Faq;
use App\Models\JenisCicilan;
use App\Models\JenisMotor;
use App\Models\MetodeBayar;
use App\Models\Motor;
use App\Models\MotorImage;
use App\Models\Pelanggan;
use App\Models\PelangganAddress;
use App\Models\Testimonial;
use App\Models\User;
use App\Support\LegacyDiagramSync;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CrediraSeeder extends Seeder
{
    public function run(): void
    {
        $users = $this->seedUsers();
        $this->seedMasterData();
        $this->seedCustomerProfile($users['user'], $users['marketing']);
        $this->seedPublicContent();
    }

    /**
     * @return array{admin: User, marketing: User, ceo: User, user: User}
     */
    private function seedUsers(): array
    {
        $password = Hash::make('password');

        return [
            'admin' => User::updateOrCreate(
                ['email' => 'admin@credira.com'],
                ['name' => 'Admin Credira', 'role' => User::ROLE_ADMIN, 'password' => $password, 'email_verified_at' => now()]
            ),
            'marketing' => User::updateOrCreate(
                ['email' => 'marketing@credira.com'],
                ['name' => 'Marketing Credira', 'role' => User::ROLE_MARKETING, 'password' => $password, 'email_verified_at' => now()]
            ),
            'ceo' => User::updateOrCreate(
                ['email' => 'ceo@credira.com'],
                ['name' => 'CEO Credira', 'role' => User::ROLE_CEO, 'password' => $password, 'email_verified_at' => now()]
            ),
            'user' => User::updateOrCreate(
                ['email' => 'user@credira.com'],
                ['name' => 'User Credira', 'role' => User::ROLE_USER, 'password' => $password, 'email_verified_at' => now()]
            ),
        ];
    }

    private function seedMasterData(): void
    {
        $matic = JenisMotor::updateOrCreate(
            ['jenis' => 'Matic'],
            ['merk' => 'Honda', 'deskripsi_jenis' => 'Motor matic untuk harian.', 'image_url' => '/images/motor-matic.svg']
        );
        $sport = JenisMotor::updateOrCreate(
            ['jenis' => 'Sport'],
            ['merk' => 'Yamaha', 'deskripsi_jenis' => 'Motor sport berperforma tinggi.', 'image_url' => '/images/motor-sport.svg']
        );

        $this->upsertMotor(
            name: 'Honda Vario 160 ABS',
            jenisId: $matic->id,
            merk: 'Honda',
            harga: 29400000,
            warna: 'Matte Black, Grande White',
            mesin: '160cc',
            transmisi: 'CVT',
            featured: true,
            image: '/images/motor-matic.svg',
        );
        $this->upsertMotor(
            name: 'Yamaha NMAX Neo',
            jenisId: $matic->id,
            merk: 'Yamaha',
            harga: 33600000,
            warna: 'Graphite, Silver, Blue',
            mesin: '155cc',
            transmisi: 'CVT',
            featured: true,
            image: '/images/motor-sport.svg',
        );
        $this->upsertMotor(
            name: 'Yamaha R15 Connected',
            jenisId: $sport->id,
            merk: 'Yamaha',
            harga: 39200000,
            warna: 'Icon Blue, Midnight Black',
            mesin: '155cc',
            transmisi: 'Manual 6-Speed',
            featured: false,
            image: '/images/motor-sport.svg',
        );

        JenisCicilan::updateOrCreate(
            ['nama_cicilan' => '12 bulan'],
            ['durasi_bulan' => 12, 'lama_cicilan' => 12, 'margin_kredit' => 2.40, 'biaya_admin' => 450000]
        );
        JenisCicilan::updateOrCreate(
            ['nama_cicilan' => '24 bulan'],
            ['durasi_bulan' => 24, 'lama_cicilan' => 24, 'margin_kredit' => 2.90, 'biaya_admin' => 500000]
        );
        JenisCicilan::updateOrCreate(
            ['nama_cicilan' => '36 bulan'],
            ['durasi_bulan' => 36, 'lama_cicilan' => 36, 'margin_kredit' => 3.30, 'biaya_admin' => 650000]
        );

        Asuransi::updateOrCreate(
            ['nama_asuransi' => 'Asuransi Komprehensif'],
            ['nama_perusahaan_asuransi' => 'Astra Guard', 'margin_asuransi' => 1.60, 'no_rekening' => '8800188881']
        );

        MetodeBayar::updateOrCreate(
            ['metode_pembayaran' => 'Midtrans Snap'],
            ['tempat_bayar' => 'Online Payment Gateway', 'no_rekening' => 'MIDTRANS', 'url_logo' => '/images/payment-proof.svg', 'status_aktif' => true]
        );
    }

    private function seedCustomerProfile(User $userAccount, User $marketing): void
    {
        $pelanggan = Pelanggan::updateOrCreate(
            ['user_id' => $userAccount->id],
            [
                'marketing_user_id' => $marketing->id,
                'nama_lengkap' => 'User Credira',
                'nama_pelanggan' => 'User Credira',
                'email' => $userAccount->email,
                'kata_sandi' => $userAccount->password,
                'no_ktp' => '3175091203980001',
                'no_telp' => '081234567890',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '1998-03-12',
                'jenis_kelamin' => 'Perempuan',
                'status_pernikahan' => 'Belum Menikah',
                'pekerjaan_default' => 'Karyawan Swasta',
                'penghasilan_default' => 8500000,
            ]
        );

        PelangganAddress::updateOrCreate(
            ['pelanggan_id' => $pelanggan->id, 'label_alamat' => 'Rumah'],
            [
                'penerima' => 'User Credira',
                'no_telp' => '081234567890',
                'alamat_lengkap' => 'Jl. Cempaka Raya No. 18',
                'kota' => 'Jakarta Pusat',
                'provinsi' => 'DKI Jakarta',
                'kode_pos' => '10510',
                'is_primary' => true,
            ]
        );

        LegacyDiagramSync::syncPelanggan($pelanggan->fresh('addresses'));
    }

    private function seedPublicContent(): void
    {
        Faq::updateOrCreate(
            ['question' => 'Apakah simulasi cicilan bersifat final?'],
            ['answer' => 'Tidak. Simulasi pada Credira adalah estimasi awal sebelum verifikasi final.', 'sort_order' => 1, 'is_active' => true]
        );
        Faq::updateOrCreate(
            ['question' => 'Dokumen apa saja yang wajib diunggah?'],
            ['answer' => 'Minimal KTP, bukti penghasilan, dan selfie bersama KTP.', 'sort_order' => 2, 'is_active' => true]
        );

        Testimonial::updateOrCreate(
            ['name' => 'User Credira'],
            ['occupation' => 'Karyawan Swasta', 'rating' => 5, 'message' => 'Aplikasi mudah digunakan untuk cek motor dan simulasi kredit.', 'is_featured' => true]
        );
    }

    private function upsertMotor(
        string $name,
        int $jenisId,
        string $merk,
        int $harga,
        string $warna,
        string $mesin,
        string $transmisi,
        bool $featured,
        string $image
    ): Motor {
        $motor = Motor::updateOrCreate(
            ['nama_motor' => $name],
            [
                'jenis_motor_id' => $jenisId,
                'id_jenis' => $jenisId,
                'merk' => $merk,
                'harga_jual' => $harga,
                'deskripsi' => $name.' untuk pengujian fitur catalog Credira.',
                'deskripsi_motor' => $name.' untuk pengujian fitur catalog Credira.',
                'warna' => $warna,
                'kapasitas_mesin' => $mesin,
                'transmisi' => $transmisi,
                'bahan_bakar' => 'Bensin',
                'berat' => 130,
                'tahun_produksi' => 2026,
                'stok' => 10,
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metode_bayar', function (Blueprint $table) {
            $table->id();
            $table->string('metode_pembayaran');
            $table->string('tempat_bayar')->nullable();
            $table->string('no_rekening')->nullable();
            $table->string('url_logo')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });

        Schema::table('jenis_cicilan', function (Blueprint $table) {
            $table->unsignedInteger('lama_cicilan')->nullable()->after('nama_cicilan');
        });

        Schema::table('motors', function (Blueprint $table) {
            $table->unsignedBigInteger('id_jenis')->nullable()->after('jenis_motor_id');
            $table->text('deskripsi_motor')->nullable()->after('deskripsi');
        });

        Schema::table('pelanggan', function (Blueprint $table) {
            $table->string('nama_pelanggan')->nullable()->after('user_id');
            $table->string('kata_sandi')->nullable()->after('email');
            $table->text('alamat1')->nullable()->after('foto_selfie');
            $table->string('kota1')->nullable()->after('alamat1');
            $table->string('kodepos1')->nullable()->after('kota1');
            $table->string('propinsi1')->nullable()->after('kodepos1');
            $table->text('alamat2')->nullable()->after('propinsi1');
            $table->string('kota2')->nullable()->after('alamat2');
            $table->string('kodepos2')->nullable()->after('kota2');
            $table->string('propinsi2')->nullable()->after('kodepos2');
            $table->text('alamat3')->nullable()->after('propinsi2');
            $table->string('kota3')->nullable()->after('alamat3');
            $table->string('kodepos3')->nullable()->after('kota3');
            $table->string('propinsi3')->nullable()->after('kodepos3');
            $table->string('foto')->nullable()->after('propinsi3');
        });

        Schema::table('pengajuan_kredit', function (Blueprint $table) {
            $table->unsignedBigInteger('id_pelanggan')->nullable()->after('pelanggan_id');
            $table->unsignedBigInteger('id_motor')->nullable()->after('motor_id');
            $table->unsignedBigInteger('id_jenis_cicilan')->nullable()->after('jenis_cicilan_id');
            $table->unsignedBigInteger('id_asuransi')->nullable()->after('asuransi_id');
            $table->date('tgl_pengajuan')->nullable()->after('kode_pengajuan');
            $table->double('harga_kredit')->default(0)->after('pokok_kredit');
            $table->double('biaya_asuransi_perbulan')->default(0)->after('biaya_asuransi');
            $table->string('url_ktp')->nullable()->after('biaya_asuransi_perbulan');
            $table->string('url_npwp')->nullable()->after('url_ktp');
            $table->string('url_slip_gaji')->nullable()->after('url_npwp');
            $table->string('url_foto')->nullable()->after('url_slip_gaji');
            $table->string('keterangan_status_pengajuan')->nullable()->after('catatan_status');
        });

        Schema::table('angsuran', function (Blueprint $table) {
            $table->unsignedBigInteger('id_kredit')->nullable()->after('pengajuan_id');
            $table->date('tgl_bayar')->nullable()->after('tanggal_bayar');
            $table->unsignedBigInteger('total_bayar')->default(0)->after('total_tagihan');
            $table->text('keterangan')->nullable()->after('metode_bayar');
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            $table->unsignedBigInteger('id_metode_bayar')->nullable()->after('kode_pembayaran');
            $table->string('tempat_bayar')->nullable()->after('metode_bayar');
            $table->string('no_rekening_tujuan')->nullable()->after('tempat_bayar');
            $table->string('url_logo_metode')->nullable()->after('no_rekening_tujuan');
        });

        Schema::table('pengiriman', function (Blueprint $table) {
            $table->unsignedBigInteger('id_kredit')->nullable()->after('pengajuan_id');
            $table->string('no_invoice')->nullable()->after('invoice');
        });

        DB::table('jenis_cicilan')->update([
            'lama_cicilan' => DB::raw('durasi_bulan'),
        ]);

        DB::table('motors')->update([
            'id_jenis' => DB::raw('jenis_motor_id'),
            'deskripsi_motor' => DB::raw('COALESCE(deskripsi_motor, deskripsi)'),
        ]);

        DB::table('pelanggan')->update([
            'nama_pelanggan' => DB::raw('nama_lengkap'),
            'kata_sandi' => DB::raw("(SELECT password FROM users WHERE users.id = pelanggan.user_id)"),
            'foto' => DB::raw('foto_profil'),
        ]);

        $pelangganRows = DB::table('pelanggan')->select('id')->get();
        foreach ($pelangganRows as $pelanggan) {
            $addresses = DB::table('pelanggan_addresses')
                ->where('pelanggan_id', $pelanggan->id)
                ->orderByDesc('is_primary')
                ->orderBy('id')
                ->limit(3)
                ->get()
                ->values();

            DB::table('pelanggan')
                ->where('id', $pelanggan->id)
                ->update([
                    'alamat1' => $addresses[0]->alamat_lengkap ?? null,
                    'kota1' => $addresses[0]->kota ?? null,
                    'kodepos1' => $addresses[0]->kode_pos ?? null,
                    'propinsi1' => $addresses[0]->provinsi ?? null,
                    'alamat2' => $addresses[1]->alamat_lengkap ?? null,
                    'kota2' => $addresses[1]->kota ?? null,
                    'kodepos2' => $addresses[1]->kode_pos ?? null,
                    'propinsi2' => $addresses[1]->provinsi ?? null,
                    'alamat3' => $addresses[2]->alamat_lengkap ?? null,
                    'kota3' => $addresses[2]->kota ?? null,
                    'kodepos3' => $addresses[2]->kode_pos ?? null,
                    'propinsi3' => $addresses[2]->provinsi ?? null,
                ]);
        }

        DB::table('pengajuan_kredit')->update([
            'id_pelanggan' => DB::raw('pelanggan_id'),
            'id_motor' => DB::raw('motor_id'),
            'id_jenis_cicilan' => DB::raw('jenis_cicilan_id'),
            'id_asuransi' => DB::raw('asuransi_id'),
            'tgl_pengajuan' => DB::raw("COALESCE(date(submitted_at), date(created_at))"),
            'harga_kredit' => DB::raw('pokok_kredit'),
            'biaya_asuransi_perbulan' => DB::raw('CASE WHEN biaya_asuransi > 0 THEN biaya_asuransi ELSE 0 END'),
            'keterangan_status_pengajuan' => DB::raw('catatan_status'),
        ]);

        $applications = DB::table('pengajuan_kredit')->select('id')->get();
        foreach ($applications as $application) {
            $documents = DB::table('dokumen_pengajuan')
                ->where('pengajuan_id', $application->id)
                ->pluck('path_file', 'jenis_dokumen');

            DB::table('pengajuan_kredit')
                ->where('id', $application->id)
                ->update([
                    'url_ktp' => $documents['foto_ktp'] ?? null,
                    'url_npwp' => $documents['npwp'] ?? null,
                    'url_slip_gaji' => $documents['slip_gaji'] ?? null,
                    'url_foto' => $documents['foto_selfie_ktp'] ?? null,
                ]);
        }

        DB::table('angsuran')->update([
            'id_kredit' => DB::raw('pengajuan_id'),
            'tgl_bayar' => DB::raw('date(tanggal_bayar)'),
            'total_bayar' => DB::raw('total_tagihan'),
        ]);

        DB::table('pengiriman')->update([
            'id_kredit' => DB::raw('pengajuan_id'),
            'no_invoice' => DB::raw('invoice'),
        ]);

        if (DB::table('metode_bayar')->count() === 0) {
            DB::table('metode_bayar')->insert([
                [
                    'metode_pembayaran' => 'Transfer Bank BCA',
                    'tempat_bayar' => 'ATM / Mobile Banking / Teller',
                    'no_rekening' => '1234567890',
                    'url_logo' => '/images/payment-proof.svg',
                    'status_aktif' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'metode_pembayaran' => 'Transfer Bank Mandiri',
                    'tempat_bayar' => 'ATM / Livin / Teller',
                    'no_rekening' => '9876543210',
                    'url_logo' => '/images/payment-proof.svg',
                    'status_aktif' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'metode_pembayaran' => 'Virtual Account',
                    'tempat_bayar' => 'Aplikasi pembayaran digital',
                    'no_rekening' => '880000112233',
                    'url_logo' => '/images/payment-proof.svg',
                    'status_aktif' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('pengiriman', function (Blueprint $table) {
            $table->dropColumn(['id_kredit', 'no_invoice']);
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropColumn(['id_metode_bayar', 'tempat_bayar', 'no_rekening_tujuan', 'url_logo_metode']);
        });

        Schema::table('angsuran', function (Blueprint $table) {
            $table->dropColumn(['id_kredit', 'tgl_bayar', 'total_bayar', 'keterangan']);
        });

        Schema::table('pengajuan_kredit', function (Blueprint $table) {
            $table->dropColumn([
                'id_pelanggan',
                'id_motor',
                'id_jenis_cicilan',
                'id_asuransi',
                'tgl_pengajuan',
                'harga_kredit',
                'biaya_asuransi_perbulan',
                'url_ktp',
                'url_npwp',
                'url_slip_gaji',
                'url_foto',
                'keterangan_status_pengajuan',
            ]);
        });

        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropColumn([
                'nama_pelanggan',
                'kata_sandi',
                'alamat1',
                'kota1',
                'kodepos1',
                'propinsi1',
                'alamat2',
                'kota2',
                'kodepos2',
                'propinsi2',
                'alamat3',
                'kota3',
                'kodepos3',
                'propinsi3',
                'foto',
            ]);
        });

        Schema::table('motors', function (Blueprint $table) {
            $table->dropColumn(['id_jenis', 'deskripsi_motor']);
        });

        Schema::table('jenis_cicilan', function (Blueprint $table) {
            $table->dropColumn('lama_cicilan');
        });

        Schema::dropIfExists('metode_bayar');
    }
};

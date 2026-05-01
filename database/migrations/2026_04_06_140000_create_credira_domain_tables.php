<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jenis_motor', function (Blueprint $table) {
            $table->id();
            $table->string('merk');
            $table->string('jenis');
            $table->text('deskripsi_jenis')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();
        });

        Schema::create('motors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_motor_id')->constrained('jenis_motor')->cascadeOnDelete();
            $table->string('nama_motor');
            $table->string('merk');
            $table->unsignedBigInteger('harga_jual');
            $table->text('deskripsi')->nullable();
            $table->string('warna')->nullable();
            $table->string('kapasitas_mesin')->nullable();
            $table->string('transmisi')->nullable();
            $table->string('bahan_bakar')->nullable();
            $table->unsignedInteger('berat')->nullable();
            $table->year('tahun_produksi')->nullable();
            $table->unsignedInteger('stok')->default(0);
            $table->boolean('status_aktif')->default(true);
            $table->string('foto1')->nullable();
            $table->string('foto2')->nullable();
            $table->string('foto3')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        Schema::create('motor_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motor_id')->constrained('motors')->cascadeOnDelete();
            $table->string('image_url');
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('jenis_cicilan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_cicilan');
            $table->unsignedInteger('durasi_bulan');
            $table->decimal('margin_kredit', 5, 2);
            $table->unsignedBigInteger('biaya_admin')->default(0);
            $table->timestamps();
        });

        Schema::create('asuransi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_perusahaan_asuransi');
            $table->string('nama_asuransi');
            $table->decimal('margin_asuransi', 5, 2)->default(0);
            $table->string('no_rekening')->nullable();
            $table->string('url_logo')->nullable();
            $table->timestamps();
        });

        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('nama_lengkap');
            $table->string('email');
            $table->string('no_ktp')->nullable();
            $table->string('no_telp');
            $table->date('tanggal_lahir')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->string('status_pernikahan')->nullable();
            $table->string('pekerjaan_default')->nullable();
            $table->unsignedBigInteger('penghasilan_default')->nullable();
            $table->string('foto_profil')->nullable();
            $table->string('foto_ktp')->nullable();
            $table->string('foto_selfie')->nullable();
            $table->timestamps();
        });

        Schema::create('pelanggan_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_id')->constrained('pelanggan')->cascadeOnDelete();
            $table->string('label_alamat');
            $table->string('penerima');
            $table->string('no_telp');
            $table->text('alamat_lengkap');
            $table->string('kota');
            $table->string('provinsi');
            $table->string('kode_pos');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('pengajuan_kredit', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pengajuan')->unique();
            $table->foreignId('pelanggan_id')->constrained('pelanggan')->cascadeOnDelete();
            $table->foreignId('motor_id')->constrained('motors')->cascadeOnDelete();
            $table->foreignId('jenis_cicilan_id')->constrained('jenis_cicilan')->cascadeOnDelete();
            $table->foreignId('asuransi_id')->nullable()->constrained('asuransi')->nullOnDelete();
            $table->unsignedBigInteger('harga_cash');
            $table->unsignedBigInteger('dp');
            $table->unsignedBigInteger('pokok_kredit');
            $table->decimal('margin_kredit', 12, 2);
            $table->unsignedBigInteger('biaya_admin')->default(0);
            $table->unsignedBigInteger('biaya_asuransi')->default(0);
            $table->unsignedBigInteger('cicilan_perbulan');
            $table->unsignedBigInteger('total_bayar');
            $table->string('status_pengajuan')->default('draft');
            $table->text('catatan_status')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->json('snapshot_data')->nullable();
            $table->timestamps();
        });

        Schema::create('pengajuan_detail_finansial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->unique()->constrained('pengajuan_kredit')->cascadeOnDelete();
            $table->string('pekerjaan')->nullable();
            $table->string('nama_perusahaan')->nullable();
            $table->text('alamat_kantor')->nullable();
            $table->string('lama_bekerja')->nullable();
            $table->unsignedBigInteger('penghasilan_bulanan')->nullable();
            $table->unsignedBigInteger('pengeluaran_bulanan')->nullable();
            $table->string('status_rumah')->nullable();
            $table->string('kontak_darurat_nama')->nullable();
            $table->string('kontak_darurat_nohp')->nullable();
            $table->string('kontak_darurat_hubungan')->nullable();
            $table->timestamps();
        });

        Schema::create('dokumen_pengajuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan_kredit')->cascadeOnDelete();
            $table->string('jenis_dokumen');
            $table->string('nama_file');
            $table->string('path_file');
            $table->string('status_verifikasi')->default('pending');
            $table->text('catatan_verifikasi')->nullable();
            $table->dateTime('uploaded_at')->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('angsuran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan_kredit')->cascadeOnDelete();
            $table->unsignedInteger('angsuran_ke');
            $table->date('tanggal_jatuh_tempo');
            $table->unsignedBigInteger('nominal_angsuran');
            $table->unsignedBigInteger('denda')->default(0);
            $table->unsignedBigInteger('total_tagihan');
            $table->string('status_pembayaran')->default('belum_bayar');
            $table->dateTime('tanggal_bayar')->nullable();
            $table->string('metode_bayar')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['pengajuan_id', 'angsuran_ke']);
        });

        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('angsuran_id')->constrained('angsuran')->cascadeOnDelete();
            $table->foreignId('pelanggan_id')->constrained('pelanggan')->cascadeOnDelete();
            $table->string('kode_pembayaran')->unique();
            $table->string('metode_bayar');
            $table->string('nama_bank_pengirim')->nullable();
            $table->string('nama_pemilik_rekening')->nullable();
            $table->unsignedBigInteger('nominal_bayar');
            $table->date('tanggal_bayar');
            $table->string('bukti_bayar');
            $table->string('status_verifikasi')->default('pending');
            $table->text('catatan_verifikasi')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pengiriman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan_kredit')->cascadeOnDelete();
            $table->string('invoice')->unique();
            $table->foreignId('alamat_pengiriman_id')->nullable()->constrained('pelanggan_addresses')->nullOnDelete();
            $table->text('alamat_tujuan');
            $table->date('tgl_kirim')->nullable();
            $table->date('tgl_tiba')->nullable();
            $table->string('status_kirim')->default('menunggu_pengiriman');
            $table->string('nama_kurir')->nullable();
            $table->string('telpon_kurir')->nullable();
            $table->string('bukti_foto')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('nama_penerima')->nullable();
            $table->timestamps();
        });

        Schema::create('pengajuan_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan_kredit')->cascadeOnDelete();
            $table->string('status_lama')->nullable();
            $table->string('status_baru');
            $table->text('catatan')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('info');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->string('status')->default('baru');
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('occupation')->nullable();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->text('message');
            $table->boolean('is_featured')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('pengajuan_logs');
        Schema::dropIfExists('pengiriman');
        Schema::dropIfExists('pembayaran');
        Schema::dropIfExists('angsuran');
        Schema::dropIfExists('dokumen_pengajuan');
        Schema::dropIfExists('pengajuan_detail_finansial');
        Schema::dropIfExists('pengajuan_kredit');
        Schema::dropIfExists('pelanggan_addresses');
        Schema::dropIfExists('pelanggan');
        Schema::dropIfExists('asuransi');
        Schema::dropIfExists('jenis_cicilan');
        Schema::dropIfExists('motor_images');
        Schema::dropIfExists('motors');
        Schema::dropIfExists('jenis_motor');
    }
};

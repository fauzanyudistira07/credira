<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanDetailFinansial extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_detail_finansial';

    protected $fillable = [
        'pengajuan_id',
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
    ];

    protected function casts(): array
    {
        return [
            'penghasilan_bulanan' => 'integer',
            'pengeluaran_bulanan' => 'integer',
        ];
    }

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanKredit::class, 'pengajuan_id');
    }

    public function application(): BelongsTo
    {
        return $this->pengajuan();
    }
}

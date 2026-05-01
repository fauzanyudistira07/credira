<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisCicilan extends Model
{
    use HasFactory;

    protected $table = 'jenis_cicilan';

    protected $fillable = [
        'nama_cicilan',
        'durasi_bulan',
        'lama_cicilan',
        'margin_kredit',
        'biaya_admin',
    ];

    protected function casts(): array
    {
        return [
            'durasi_bulan' => 'integer',
            'lama_cicilan' => 'integer',
            'margin_kredit' => 'float',
            'biaya_admin' => 'integer',
        ];
    }

    public function pengajuanKredit(): HasMany
    {
        return $this->hasMany(PengajuanKredit::class, 'jenis_cicilan_id');
    }

    public function applications(): HasMany
    {
        return $this->pengajuanKredit();
    }
}

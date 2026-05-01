<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PelangganAddress extends Model
{
    use HasFactory;

    protected $table = 'pelanggan_addresses';

    protected $fillable = [
        'pelanggan_id',
        'label_alamat',
        'penerima',
        'no_telp',
        'alamat_lengkap',
        'kota',
        'provinsi',
        'kode_pos',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }
}

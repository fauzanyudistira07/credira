<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetodeBayar extends Model
{
    use HasFactory;

    protected $table = 'metode_bayar';

    protected $fillable = [
        'metode_pembayaran',
        'tempat_bayar',
        'no_rekening',
        'url_logo',
        'status_aktif',
    ];

    protected function casts(): array
    {
        return [
            'status_aktif' => 'boolean',
        ];
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'id_metode_bayar');
    }
}

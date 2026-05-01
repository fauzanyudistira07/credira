<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Angsuran extends Model
{
    use HasFactory;

    protected $table = 'angsuran';

    protected $fillable = [
        'pengajuan_id',
        'id_kredit',
        'angsuran_ke',
        'tanggal_jatuh_tempo',
        'nominal_angsuran',
        'denda',
        'total_tagihan',
        'total_bayar',
        'status_pembayaran',
        'tanggal_bayar',
        'tgl_bayar',
        'metode_bayar',
        'keterangan',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'angsuran_ke' => 'integer',
            'tanggal_jatuh_tempo' => 'date',
            'nominal_angsuran' => 'integer',
            'denda' => 'integer',
            'total_tagihan' => 'integer',
            'total_bayar' => 'integer',
            'tanggal_bayar' => 'datetime',
            'tgl_bayar' => 'date',
            'verified_at' => 'datetime',
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

    public function payments(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'angsuran_id')->latest();
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';

    protected $appends = [
        'proof_url',
    ];

    protected $fillable = [
        'angsuran_id',
        'pelanggan_id',
        'kode_pembayaran',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'midtrans_snap_token',
        'midtrans_redirect_url',
        'midtrans_payment_type',
        'midtrans_status_code',
        'midtrans_payload',
        'id_metode_bayar',
        'metode_bayar',
        'tempat_bayar',
        'no_rekening_tujuan',
        'url_logo_metode',
        'nama_bank_pengirim',
        'nama_pemilik_rekening',
        'nominal_bayar',
        'tanggal_bayar',
        'bukti_bayar',
        'status_verifikasi',
        'catatan_verifikasi',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'nominal_bayar' => 'integer',
            'tanggal_bayar' => 'date',
            'verified_at' => 'datetime',
            'midtrans_payload' => 'array',
        ];
    }

    public function angsuran(): BelongsTo
    {
        return $this->belongsTo(Angsuran::class, 'angsuran_id');
    }

    public function installment(): BelongsTo
    {
        return $this->angsuran();
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function metodeBayar(): BelongsTo
    {
        return $this->belongsTo(MetodeBayar::class, 'id_metode_bayar');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getProofUrlAttribute(): ?string
    {
        return $this->normalizeStoredFileUrl($this->bukti_bayar);
    }

    private function normalizeStoredFileUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if ($path === '-' || str_starts_with($path, 'midtrans://')) {
            return null;
        }

        $normalizedPath = ltrim($path, '/');

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        if (str_starts_with($normalizedPath, 'storage/')) {
            return '/'.$normalizedPath;
        }

        if (str_starts_with($normalizedPath, 'public/')) {
            $normalizedPath = substr($normalizedPath, 7);
        }

        return Storage::disk('public')->url($normalizedPath);
    }
}

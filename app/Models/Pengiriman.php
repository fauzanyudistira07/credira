<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Pengiriman extends Model
{
    use HasFactory;

    protected $table = 'pengiriman';

    protected $appends = [
        'proof_photo_url',
    ];

    protected $fillable = [
        'pengajuan_id',
        'id_kredit',
        'invoice',
        'no_invoice',
        'alamat_pengiriman_id',
        'alamat_tujuan',
        'tgl_kirim',
        'tgl_tiba',
        'status_kirim',
        'nama_kurir',
        'telpon_kurir',
        'bukti_foto',
        'keterangan',
        'nama_penerima',
    ];

    protected function casts(): array
    {
        return [
            'tgl_kirim' => 'date',
            'tgl_tiba' => 'date',
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

    public function address(): BelongsTo
    {
        return $this->belongsTo(PelangganAddress::class, 'alamat_pengiriman_id');
    }

    public function getProofPhotoUrlAttribute(): ?string
    {
        return $this->normalizeStoredFileUrl($this->bukti_foto);
    }

    private function normalizeStoredFileUrl(?string $path): ?string
    {
        if (! $path) {
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

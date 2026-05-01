<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Pelanggan extends Model
{
    use HasFactory;

    protected $table = 'pelanggan';

    protected $fillable = [
        'user_id',
        'marketing_user_id',
        'nama_lengkap',
        'nama_pelanggan',
        'email',
        'kata_sandi',
        'no_ktp',
        'no_telp',
        'tanggal_lahir',
        'tempat_lahir',
        'jenis_kelamin',
        'status_pernikahan',
        'pekerjaan_default',
        'penghasilan_default',
        'foto_profil',
        'foto_ktp',
        'foto_selfie',
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
    ];

    protected $appends = [
        'display_name',
        'foto_profil_url',
        'foto_ktp_url',
        'foto_selfie_url',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'penghasilan_default' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function marketingOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marketing_user_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(PelangganAddress::class, 'pelanggan_id');
    }

    public function pengajuanKredit(): HasMany
    {
        return $this->hasMany(PengajuanKredit::class, 'pelanggan_id');
    }

    public function applications(): HasMany
    {
        return $this->pengajuanKredit();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'pelanggan_id');
    }

    public function getDisplayNameAttribute(): string
    {
        return (string) ($this->nama_lengkap ?: $this->nama_pelanggan ?: $this->user?->name ?: 'Pelanggan');
    }

    public function getFotoProfilUrlAttribute(): ?string
    {
        return $this->normalizeStoredFileUrl($this->foto_profil);
    }

    public function getFotoKtpUrlAttribute(): ?string
    {
        return $this->normalizeStoredFileUrl($this->foto_ktp);
    }

    public function getFotoSelfieUrlAttribute(): ?string
    {
        return $this->normalizeStoredFileUrl($this->foto_selfie);
    }

    public function scopeOwnedByMarketing(Builder $query, int $marketingUserId): Builder
    {
        return $query->where('marketing_user_id', $marketingUserId);
    }

    private function normalizeStoredFileUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        $normalizedPath = ltrim($path, '/');

        if (str_starts_with($normalizedPath, 'storage/')) {
            return '/'.$normalizedPath;
        }

        if (str_starts_with($normalizedPath, 'public/')) {
            $normalizedPath = substr($normalizedPath, 7);
        }

        return Storage::disk('public')->url($normalizedPath);
    }
}

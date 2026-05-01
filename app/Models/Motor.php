<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Motor extends Model
{
    use HasFactory;

    protected $fillable = [
        'jenis_motor_id',
        'id_jenis',
        'nama_motor',
        'merk',
        'harga_jual',
        'deskripsi',
        'deskripsi_motor',
        'warna',
        'kapasitas_mesin',
        'transmisi',
        'bahan_bakar',
        'berat',
        'tahun_produksi',
        'stok',
        'status_aktif',
        'foto1',
        'foto2',
        'foto3',
        'is_featured',
    ];

    protected $appends = [
        'formatted_harga_jual',
        'primary_image_url',
        'gallery_urls',
    ];

    protected function casts(): array
    {
        return [
            'harga_jual' => 'integer',
            'berat' => 'integer',
            'tahun_produksi' => 'integer',
            'stok' => 'integer',
            'status_aktif' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function jenisMotor(): BelongsTo
    {
        return $this->belongsTo(JenisMotor::class, 'jenis_motor_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(MotorImage::class, 'motor_id')->orderBy('sort_order');
    }

    public function pengajuanKredit(): HasMany
    {
        return $this->hasMany(PengajuanKredit::class, 'motor_id');
    }

    public function applications(): HasMany
    {
        return $this->pengajuanKredit();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status_aktif', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function getFormattedHargaJualAttribute(): string
    {
        return 'Rp '.number_format((int) $this->harga_jual, 0, ',', '.');
    }

    public function getPrimaryImageUrlAttribute(): ?string
    {
        $path = $this->images->first()?->image_url ?: $this->foto1 ?: $this->foto2 ?: $this->foto3;

        return $this->normalizeImagePath($path);
    }

    public function getGalleryUrlsAttribute(): array
    {
        $paths = $this->images->pluck('image_url')
            ->merge([$this->foto1, $this->foto2, $this->foto3])
            ->filter()
            ->unique()
            ->map(fn (string $path) => $this->normalizeImagePath($path))
            ->filter()
            ->values()
            ->all();

        return $paths;
    }

    private function normalizeImagePath(?string $path): ?string
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

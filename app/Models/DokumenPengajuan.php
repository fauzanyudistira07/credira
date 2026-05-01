<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DokumenPengajuan extends Model
{
    use HasFactory;

    protected $table = 'dokumen_pengajuan';

    protected $fillable = [
        'pengajuan_id',
        'jenis_dokumen',
        'nama_file',
        'path_file',
        'status_verifikasi',
        'catatan_verifikasi',
        'uploaded_at',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    protected $appends = [
        'file_url',
    ];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanKredit::class, 'pengajuan_id');
    }

    public function application(): BelongsTo
    {
        return $this->pengajuan();
    }

    public function getFileUrlAttribute(): string
    {
        $path = ltrim((string) $this->path_file, '/');

        if (str_starts_with((string) $this->path_file, 'http://') || str_starts_with((string) $this->path_file, 'https://') || str_starts_with((string) $this->path_file, '/')) {
            return (string) $this->path_file;
        }

        if (str_starts_with($path, 'storage/')) {
            return '/'.$path;
        }

        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        }

        return Storage::disk('public')->url($path);
    }
}

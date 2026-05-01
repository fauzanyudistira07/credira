<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PengajuanKredit extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_kredit';

    protected $fillable = [
        'kode_pengajuan',
        'pelanggan_id',
        'marketing_user_id',
        'id_pelanggan',
        'motor_id',
        'id_motor',
        'jenis_cicilan_id',
        'id_jenis_cicilan',
        'asuransi_id',
        'id_asuransi',
        'tgl_pengajuan',
        'harga_cash',
        'dp',
        'pokok_kredit',
        'harga_kredit',
        'margin_kredit',
        'biaya_admin',
        'biaya_asuransi',
        'biaya_asuransi_perbulan',
        'cicilan_perbulan',
        'total_bayar',
        'status_pengajuan',
        'catatan_status',
        'keterangan_status_pengajuan',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'snapshot_data',
        'url_ktp',
        'url_npwp',
        'url_slip_gaji',
        'url_foto',
    ];

    protected function casts(): array
    {
        return [
            'tgl_pengajuan' => 'date',
            'harga_cash' => 'integer',
            'dp' => 'integer',
            'pokok_kredit' => 'integer',
            'harga_kredit' => 'float',
            'margin_kredit' => 'float',
            'biaya_admin' => 'integer',
            'biaya_asuransi' => 'integer',
            'biaya_asuransi_perbulan' => 'float',
            'cicilan_perbulan' => 'integer',
            'total_bayar' => 'integer',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'snapshot_data' => 'array',
        ];
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function marketingOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marketing_user_id');
    }

    public function motor(): BelongsTo
    {
        return $this->belongsTo(Motor::class, 'motor_id');
    }

    public function jenisCicilan(): BelongsTo
    {
        return $this->belongsTo(JenisCicilan::class, 'jenis_cicilan_id');
    }

    public function asuransi(): BelongsTo
    {
        return $this->belongsTo(Asuransi::class, 'asuransi_id');
    }

    public function financialDetail(): HasOne
    {
        return $this->hasOne(PengajuanDetailFinansial::class, 'pengajuan_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DokumenPengajuan::class, 'pengajuan_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PengajuanLog::class, 'pengajuan_id')->latest();
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Angsuran::class, 'pengajuan_id');
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Pengiriman::class, 'pengajuan_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status_pengajuan', [
            ApplicationStatus::Draft->value,
            ApplicationStatus::MenungguKonfirmasi->value,
            ApplicationStatus::VerifikasiDokumen->value,
        ]);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->whereIn('status_pengajuan', [
            ApplicationStatus::Disetujui->value,
            ApplicationStatus::KontrakAktif->value,
            ApplicationStatus::Selesai->value,
        ]);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->whereIn('status_pengajuan', [
            ApplicationStatus::Ditolak->value,
            ApplicationStatus::DibatalkanAdmin->value,
            ApplicationStatus::DibatalkanUser->value,
        ]);
    }

    public function scopeReview(Builder $query): Builder
    {
        return $query->whereIn('status_pengajuan', [
            ApplicationStatus::VerifikasiDokumen->value,
            ApplicationStatus::Diproses->value,
            ApplicationStatus::Survey->value,
        ]);
    }

    public function scopeOwnedByMarketing(Builder $query, int $marketingUserId): Builder
    {
        return $query->where('marketing_user_id', $marketingUserId);
    }

    public function getStatusBadgeAttribute(): string
    {
        return str($this->status_pengajuan)->replace('_', ' ')->title()->toString();
    }
}

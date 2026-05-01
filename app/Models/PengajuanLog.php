<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanLog extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_logs';

    protected $fillable = [
        'pengajuan_id',
        'status_lama',
        'status_baru',
        'catatan',
        'changed_by',
    ];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanKredit::class, 'pengajuan_id');
    }

    public function application(): BelongsTo
    {
        return $this->pengajuan();
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}

<?php

namespace App\Enums;

enum DocumentVerificationStatus: string
{
    case Pending = 'pending';
    case Valid = 'valid';
    case Revisi = 'revisi';
    case Ditolak = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Valid => 'Valid',
            self::Revisi => 'Perlu Revisi',
            self::Ditolak => 'Ditolak',
        };
    }
}

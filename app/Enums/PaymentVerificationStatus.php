<?php

namespace App\Enums;

enum PaymentVerificationStatus: string
{
    case Pending = 'pending';
    case Valid = 'valid';
    case Ditolak = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Valid => 'Valid',
            self::Ditolak => 'Ditolak',
        };
    }
}

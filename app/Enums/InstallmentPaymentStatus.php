<?php

namespace App\Enums;

enum InstallmentPaymentStatus: string
{
    case BelumBayar = 'belum_bayar';
    case MenungguVerifikasi = 'menunggu_verifikasi';
    case SudahBayar = 'sudah_bayar';
    case Telat = 'telat';
    case GagalVerifikasi = 'gagal_verifikasi';

    public function label(): string
    {
        return match ($this) {
            self::BelumBayar => 'Belum Bayar',
            self::MenungguVerifikasi => 'Menunggu Verifikasi',
            self::SudahBayar => 'Sudah Bayar',
            self::Telat => 'Telat',
            self::GagalVerifikasi => 'Gagal Verifikasi',
        };
    }
}

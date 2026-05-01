<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case MenungguPengiriman = 'menunggu_pengiriman';
    case Disiapkan = 'disiapkan';
    case Dikirim = 'dikirim';
    case SampaiTujuan = 'sampai_tujuan';
    case GagalKirim = 'gagal_kirim';

    public function label(): string
    {
        return match ($this) {
            self::MenungguPengiriman => 'Menunggu Pengiriman',
            self::Disiapkan => 'Disiapkan',
            self::Dikirim => 'Dikirim',
            self::SampaiTujuan => 'Sampai Tujuan',
            self::GagalKirim => 'Gagal Kirim',
        };
    }
}

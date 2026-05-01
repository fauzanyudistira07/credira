<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Draft = 'draft';
    case MenungguKonfirmasi = 'menunggu_konfirmasi';
    case VerifikasiDokumen = 'verifikasi_dokumen';
    case Diproses = 'diproses';
    case Survey = 'survey';
    case Disetujui = 'disetujui';
    case Ditolak = 'ditolak';
    case DibatalkanUser = 'dibatalkan_user';
    case DibatalkanAdmin = 'dibatalkan_admin';
    case KontrakAktif = 'kontrak_aktif';
    case Selesai = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::MenungguKonfirmasi => 'Menunggu Konfirmasi',
            self::VerifikasiDokumen => 'Verifikasi Dokumen',
            self::Diproses => 'Diproses',
            self::Survey => 'Survey',
            self::Disetujui => 'Disetujui',
            self::Ditolak => 'Ditolak',
            self::DibatalkanUser => 'Dibatalkan User',
            self::DibatalkanAdmin => 'Dibatalkan Admin',
            self::KontrakAktif => 'Kontrak Aktif',
            self::Selesai => 'Selesai',
        };
    }
}

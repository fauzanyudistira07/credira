<?php

namespace App\Support;

use App\Models\Angsuran;
use App\Models\MetodeBayar;
use App\Models\Pelanggan;
use App\Models\PengajuanKredit;
use App\Models\Pengiriman;

class LegacyDiagramSync
{
    public static function syncPelanggan(Pelanggan $pelanggan): void
    {
        $addresses = $pelanggan->addresses()
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->take(3)
            ->get()
            ->values();

        $payload = [
            'nama_pelanggan' => $pelanggan->nama_lengkap,
            'foto' => $pelanggan->foto_profil,
        ];

        foreach ([1, 2, 3] as $slot) {
            $address = $addresses->get($slot - 1);
            $payload["alamat{$slot}"] = $address?->alamat_lengkap;
            $payload["kota{$slot}"] = $address?->kota;
            $payload["kodepos{$slot}"] = $address?->kode_pos;
            $payload["propinsi{$slot}"] = $address?->provinsi;
        }

        $pelanggan->forceFill($payload)->save();
    }

    public static function syncPengajuan(PengajuanKredit $application): void
    {
        $duration = (int) ($application->jenisCicilan->durasi_bulan ?? 0);
        $biayaAsuransiPerbulan = $duration > 0
            ? round((float) $application->biaya_asuransi / $duration, 2)
            : 0;

        $application->forceFill([
            'id_pelanggan' => $application->pelanggan_id,
            'id_motor' => $application->motor_id,
            'id_jenis_cicilan' => $application->jenis_cicilan_id,
            'id_asuransi' => $application->asuransi_id,
            'tgl_pengajuan' => optional($application->submitted_at ?? $application->created_at)?->format('Y-m-d'),
            'harga_kredit' => $application->pokok_kredit,
            'biaya_asuransi_perbulan' => $biayaAsuransiPerbulan,
            'keterangan_status_pengajuan' => $application->catatan_status,
        ])->save();
    }

    public static function syncInstallment(Angsuran $installment): void
    {
        $installment->forceFill([
            'id_kredit' => $installment->pengajuan_id,
            'tgl_bayar' => optional($installment->tanggal_bayar)?->format('Y-m-d'),
            'total_bayar' => $installment->total_tagihan,
        ])->save();
    }

    public static function syncDelivery(Pengiriman $delivery): void
    {
        $delivery->forceFill([
            'id_kredit' => $delivery->pengajuan_id,
            'no_invoice' => $delivery->invoice,
        ])->save();
    }

    public static function syncPaymentMethodFields(array &$payload, ?MetodeBayar $method): void
    {
        $payload['id_metode_bayar'] = $method?->id;
        $payload['metode_bayar'] = $method?->metode_pembayaran ?? ($payload['metode_bayar'] ?? null);
        $payload['tempat_bayar'] = $method?->tempat_bayar;
        $payload['no_rekening_tujuan'] = $method?->no_rekening;
        $payload['url_logo_metode'] = $method?->url_logo;
    }
}

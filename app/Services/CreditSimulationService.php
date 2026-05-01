<?php

namespace App\Services;

use App\Models\Asuransi;
use App\Models\JenisCicilan;
use App\Models\Motor;
use Illuminate\Validation\ValidationException;

class CreditSimulationService
{
    /**
     * @return array<string, mixed>
     */
    public function calculate(Motor $motor, JenisCicilan $jenisCicilan, ?Asuransi $asuransi, int $dp): array
    {
        if ($dp >= $motor->harga_jual) {
            throw ValidationException::withMessages([
                'dp' => 'DP harus lebih kecil dari harga motor.',
            ]);
        }

        $pokokKredit = max($motor->harga_jual - $dp, 0);
        $marginAmount = (int) round($pokokKredit * ($jenisCicilan->margin_kredit / 100) * ($jenisCicilan->durasi_bulan / 12));
        $biayaAdmin = (int) $jenisCicilan->biaya_admin;
        $biayaAsuransi = $asuransi
            ? (int) round($pokokKredit * ($asuransi->margin_asuransi / 100))
            : 0;
        $totalBayar = $pokokKredit + $marginAmount + $biayaAdmin + $biayaAsuransi;
        $angsuranPerBulan = (int) ceil($totalBayar / $jenisCicilan->durasi_bulan);

        return [
            'motor_id' => $motor->id,
            'harga_motor' => (int) $motor->harga_jual,
            'dp' => $dp,
            'pokok_kredit' => $pokokKredit,
            'margin_persen' => (float) $jenisCicilan->margin_kredit,
            'margin_amount' => $marginAmount,
            'biaya_admin' => $biayaAdmin,
            'biaya_asuransi' => $biayaAsuransi,
            'angsuran_per_bulan' => $angsuranPerBulan,
            'total_bayar' => $totalBayar,
            'durasi_bulan' => $jenisCicilan->durasi_bulan,
            'jenis_cicilan' => [
                'id' => $jenisCicilan->id,
                'nama_cicilan' => $jenisCicilan->nama_cicilan,
            ],
            'asuransi' => $asuransi ? [
                'id' => $asuransi->id,
                'nama_asuransi' => $asuransi->nama_asuransi,
            ] : null,
        ];
    }
}

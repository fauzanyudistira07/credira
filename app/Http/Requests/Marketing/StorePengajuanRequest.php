<?php

namespace App\Http\Requests\Marketing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isMarketing() === true;
    }

    public function rules(): array
    {
        return [
            'pelanggan_id' => ['required', 'integer', Rule::exists('pelanggan', 'id')],
            'motor_id' => ['required', 'integer', Rule::exists('motors', 'id')->where('status_aktif', true)],
            'jenis_cicilan_id' => ['required', 'integer', 'exists:jenis_cicilan,id'],
            'asuransi_id' => ['nullable', 'integer', 'exists:asuransi,id'],
            'dp' => ['required', 'numeric', 'min:0'],
            'harga_cash' => ['nullable', 'numeric', 'min:0'],
            'pokok_kredit' => ['nullable', 'numeric', 'min:0'],
            'harga_kredit' => ['nullable', 'numeric', 'min:0'],
            'margin_kredit' => ['nullable', 'numeric', 'min:0'],
            'biaya_admin' => ['nullable', 'numeric', 'min:0'],
            'biaya_asuransi' => ['nullable', 'numeric', 'min:0'],
            'total_bayar' => ['nullable', 'numeric', 'min:0'],
            'pekerjaan' => ['nullable', 'string', 'max:255'],
            'nama_perusahaan' => ['nullable', 'string', 'max:255'],
            'alamat_kantor' => ['nullable', 'string'],
            'lama_bekerja' => ['nullable', 'string', 'max:255'],
            'penghasilan_bulanan' => ['nullable', 'numeric', 'min:0'],
            'pengeluaran_bulanan' => ['nullable', 'numeric', 'min:0'],
            'status_rumah' => ['nullable', 'string', 'max:255'],
            'kontak_darurat_nama' => ['nullable', 'string', 'max:255'],
            'kontak_darurat_nohp' => ['nullable', 'string', 'max:255'],
            'kontak_darurat_hubungan' => ['nullable', 'string', 'max:255'],
            'documents.foto_ktp' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'documents.foto_selfie_ktp' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'documents.slip_gaji' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'documents.npwp' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'documents.kk' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'pelanggan_id.exists' => 'Pelanggan yang dipilih tidak tersedia.',
            'motor_id.exists' => 'Motor yang dipilih tidak aktif atau tidak tersedia.',
        ];
    }
}

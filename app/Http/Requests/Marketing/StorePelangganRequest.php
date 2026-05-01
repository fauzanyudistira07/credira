<?php

namespace App\Http\Requests\Marketing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePelangganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isMarketing() === true;
    }

    public function rules(): array
    {
        return [
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email', 'unique:pelanggan,email'],
            'kata_sandi' => ['required', 'string', 'min:8', 'max:255'],
            'no_ktp' => ['nullable', 'string', 'max:255', 'unique:pelanggan,no_ktp'],
            'no_telp' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'jenis_kelamin' => ['nullable', 'string', Rule::in(['Laki-laki', 'Perempuan'])],
            'status_pernikahan' => ['nullable', 'string', 'max:255'],
            'pekerjaan_default' => ['nullable', 'string', 'max:255'],
            'penghasilan_default' => ['nullable', 'numeric', 'min:0'],
            'alamat1' => ['nullable', 'string'],
            'kota1' => ['nullable', 'string', 'max:255'],
            'propinsi1' => ['nullable', 'string', 'max:255'],
            'kodepos1' => ['nullable', 'string', 'max:255'],
            'foto_profil' => ['nullable', 'image', 'max:5120'],
            'foto_ktp' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'foto_selfie' => ['nullable', 'image', 'max:5120'],
        ];
    }
}

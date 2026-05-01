<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreMotorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return $this->baseRules();
    }

    private function baseRules(): array
    {
        return [
            'jenis_motor_id' => ['required', 'exists:jenis_motor,id'],
            'nama_motor' => ['required', 'string', 'max:255'],
            'merk' => ['required', 'string', 'max:255'],
            'harga_jual' => ['required', 'numeric', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
            'deskripsi_motor' => ['nullable', 'string'],
            'warna' => ['nullable', 'string', 'max:255'],
            'kapasitas_mesin' => ['nullable', 'string', 'max:255'],
            'transmisi' => ['nullable', 'string', 'max:255'],
            'bahan_bakar' => ['nullable', 'string', 'max:255'],
            'berat' => ['nullable', 'integer', 'min:0'],
            'tahun_produksi' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'stok' => ['required', 'integer', 'min:0'],
            'status_aktif' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'foto1' => ['nullable', 'image', 'max:5120'],
            'foto2' => ['nullable', 'image', 'max:5120'],
            'foto3' => ['nullable', 'image', 'max:5120'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['nullable', 'image', 'max:5120'],
        ];
    }
}

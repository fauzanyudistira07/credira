<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewPengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(['pending', 'review', 'approved', 'rejected'])],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                if ($this->input('status') === 'rejected' && ! filled($this->input('catatan'))) {
                    $validator->errors()->add('catatan', 'Catatan wajib diisi saat pengajuan ditolak.');
                }
            },
        ];
    }
}

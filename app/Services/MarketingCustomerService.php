<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\User;
use App\Support\LegacyDiagramSync;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MarketingCustomerService
{
    public function create(User $marketing, array $data): Pelanggan
    {
        return DB::transaction(function () use ($marketing, $data) {
            $user = User::create([
                'name' => $data['nama_lengkap'],
                'email' => $data['email'],
                'role' => 'user',
                'password' => $data['kata_sandi'],
            ]);

            $pelanggan = Pelanggan::create($this->pelangganPayload($marketing, $user, $data));

            $this->storeFiles($pelanggan, $data);
            $this->syncPrimaryAddress($pelanggan, $data);

            return $pelanggan->fresh(['user', 'addresses', 'marketingOwner']);
        });
    }

    public function update(User $marketing, Pelanggan $pelanggan, array $data): Pelanggan
    {
        return DB::transaction(function () use ($marketing, $pelanggan, $data) {
            $userPayload = [
                'name' => $data['nama_lengkap'],
                'email' => $data['email'],
            ];

            if (filled($data['kata_sandi'] ?? null)) {
                $userPayload['password'] = $data['kata_sandi'];
            }

            $pelanggan->user()->update($userPayload);

            $pelanggan->update($this->pelangganPayload($marketing, $pelanggan->user, $data, false));

            $this->storeFiles($pelanggan, $data);
            $this->syncPrimaryAddress($pelanggan, $data);

            return $pelanggan->fresh(['user', 'addresses', 'marketingOwner']);
        });
    }

    private function pelangganPayload(User $marketing, User $user, array $data, bool $includeIdentity = true): array
    {
        $payload = Arr::only($data, [
            'nama_lengkap',
            'email',
            'no_ktp',
            'no_telp',
            'tanggal_lahir',
            'tempat_lahir',
            'jenis_kelamin',
            'status_pernikahan',
            'pekerjaan_default',
            'penghasilan_default',
            'alamat1',
            'kota1',
            'kodepos1',
            'propinsi1',
        ]);

        $payload['nama_pelanggan'] = $data['nama_lengkap'];
        $payload['user_id'] = $user->id;
        $payload['marketing_user_id'] = $marketing->id;

        if ($includeIdentity) {
            $payload['kata_sandi'] = $user->password;
        }

        return $payload;
    }

    private function storeFiles(Pelanggan $pelanggan, array $data): void
    {
        $fileMap = [
            'foto_profil' => 'foto_profil',
            'foto_ktp' => 'foto_ktp',
            'foto_selfie' => 'foto_selfie',
        ];

        $updates = [];

        foreach ($fileMap as $field => $folder) {
            $file = $data[$field] ?? null;

            if (! $file instanceof UploadedFile) {
                continue;
            }

            if ($pelanggan->{$field}) {
                Storage::disk('public')->delete($pelanggan->{$field});
            }

            $updates[$field] = $file->store('pelanggan/'.$pelanggan->id.'/'.$folder, 'public');
        }

        if ($updates !== []) {
            $pelanggan->forceFill($updates + ['foto' => $updates['foto_profil'] ?? $pelanggan->foto])->save();
        }
    }

    private function syncPrimaryAddress(Pelanggan $pelanggan, array $data): void
    {
        if (! filled($data['alamat1'] ?? null) && ! filled($data['kota1'] ?? null) && ! filled($data['propinsi1'] ?? null)) {
            return;
        }

        $pelanggan->addresses()->updateOrCreate(
            ['pelanggan_id' => $pelanggan->id, 'is_primary' => true],
            [
                'label_alamat' => 'Rumah',
                'penerima' => $pelanggan->display_name,
                'no_telp' => $data['no_telp'],
                'alamat_lengkap' => $data['alamat1'] ?? '',
                'kota' => $data['kota1'] ?? '',
                'provinsi' => $data['propinsi1'] ?? '',
                'kode_pos' => $data['kodepos1'] ?? '',
            ],
        );

        LegacyDiagramSync::syncPelanggan($pelanggan->fresh('addresses'));
    }
}

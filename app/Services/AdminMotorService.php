<?php

namespace App\Services;

use App\Models\Motor;
use App\Models\MotorImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminMotorService
{
    public function store(array $data): Motor
    {
        return DB::transaction(function () use ($data) {
            $motor = Motor::create($this->payload($data));

            $this->syncPrimaryPhotos($motor, $data);
            $this->storeGalleryImages($motor, $data['gallery_images'] ?? []);

            return $motor->fresh(['jenisMotor', 'images']);
        });
    }

    public function update(Motor $motor, array $data): Motor
    {
        return DB::transaction(function () use ($motor, $data) {
            $motor->update($this->payload($data));

            $this->syncPrimaryPhotos($motor, $data);
            $this->storeGalleryImages($motor, $data['gallery_images'] ?? []);

            return $motor->fresh(['jenisMotor', 'images']);
        });
    }

    private function payload(array $data): array
    {
        $payload = Arr::only($data, [
            'jenis_motor_id',
            'nama_motor',
            'merk',
            'harga_jual',
            'deskripsi',
            'deskripsi_motor',
            'warna',
            'kapasitas_mesin',
            'transmisi',
            'bahan_bakar',
            'berat',
            'tahun_produksi',
            'stok',
        ]);

        $payload['id_jenis'] = $data['jenis_motor_id'];
        $payload['deskripsi'] = $data['deskripsi'] ?? $data['deskripsi_motor'] ?? null;
        $payload['deskripsi_motor'] = $data['deskripsi_motor'] ?? $data['deskripsi'] ?? null;
        $payload['status_aktif'] = (bool) ($data['status_aktif'] ?? false);
        $payload['is_featured'] = (bool) ($data['is_featured'] ?? false);

        return $payload;
    }

    private function syncPrimaryPhotos(Motor $motor, array $data): void
    {
        foreach (['foto1', 'foto2', 'foto3'] as $field) {
            $file = $data[$field] ?? null;

            if (! $file instanceof UploadedFile) {
                continue;
            }

            if ($motor->{$field}) {
                Storage::disk('public')->delete($motor->{$field});
            }

            $motor->{$field} = $file->store('motors/'.$motor->id.'/primary', 'public');
        }

        if ($motor->isDirty(['foto1', 'foto2', 'foto3'])) {
            $motor->save();
        }
    }

    /**
     * @param  array<int, mixed>  $images
     */
    private function storeGalleryImages(Motor $motor, array $images): void
    {
        $sortOrder = (int) ($motor->images()->max('sort_order') ?? -1) + 1;

        foreach ($images as $image) {
            if (! $image instanceof UploadedFile) {
                continue;
            }

            MotorImage::create([
                'motor_id' => $motor->id,
                'image_url' => $image->store('motors/'.$motor->id.'/gallery', 'public'),
                'caption' => $motor->nama_motor,
                'sort_order' => $sortOrder++,
            ]);
        }
    }
}

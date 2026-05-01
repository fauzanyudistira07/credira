<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MotorImage extends Model
{
    use HasFactory;

    protected $table = 'motor_images';

    protected $fillable = [
        'motor_id',
        'image_url',
        'caption',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function getImageUrlAttribute(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $normalizedPath = ltrim($value, '/');

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '/')) {
            return $value;
        }

        if (str_starts_with($normalizedPath, 'storage/')) {
            return '/'.$normalizedPath;
        }

        if (str_starts_with($normalizedPath, 'public/')) {
            $normalizedPath = substr($normalizedPath, 7);
        }

        return Storage::disk('public')->url($normalizedPath);
    }

    public function motor(): BelongsTo
    {
        return $this->belongsTo(Motor::class, 'motor_id');
    }
}

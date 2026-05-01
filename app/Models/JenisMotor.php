<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisMotor extends Model
{
    use HasFactory;

    protected $table = 'jenis_motor';

    protected $fillable = [
        'merk',
        'jenis',
        'deskripsi_jenis',
        'image_url',
    ];

    public function motors(): HasMany
    {
        return $this->hasMany(Motor::class, 'jenis_motor_id');
    }
}

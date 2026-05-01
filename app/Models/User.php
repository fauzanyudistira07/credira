<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_MARKETING = 'marketing';
    public const ROLE_CEO = 'ceo';
    public const ROLE_USER = 'user';

    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_MARKETING,
        self::ROLE_CEO,
        self::ROLE_USER,
    ];

    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function pelanggan(): HasOne
    {
        return $this->hasOne(Pelanggan::class, 'user_id');
    }

    public function assignedPelanggan(): HasMany
    {
        return $this->hasMany(Pelanggan::class, 'marketing_user_id');
    }

    public function assignedPengajuan(): HasMany
    {
        return $this->hasMany(PengajuanKredit::class, 'marketing_user_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id')->latest();
    }

    public function verifiedPayments(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'verified_by')->latest();
    }

    public function verifiedInstallments(): HasMany
    {
        return $this->hasMany(Angsuran::class, 'verified_by')->latest();
    }

    public function changedApplicationLogs(): HasMany
    {
        return $this->hasMany(PengajuanLog::class, 'changed_by')->latest();
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isMarketing(): bool
    {
        return $this->role === self::ROLE_MARKETING;
    }

    public function isCEO(): bool
    {
        return $this->role === self::ROLE_CEO;
    }

    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function hasValidRole(): bool
    {
        return in_array($this->role, self::ROLES, true);
    }
}

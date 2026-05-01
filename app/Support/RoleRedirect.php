<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RoleRedirect
{
    public static function routeNameFor(?User $user): ?string
    {
        return match ($user?->role) {
            User::ROLE_ADMIN => 'admin.dashboard',
            User::ROLE_MARKETING => 'marketing.dashboard',
            User::ROLE_CEO => 'ceo.dashboard',
            User::ROLE_USER => 'user.dashboard',
            default => null,
        };
    }

    public static function redirectFor(User $user): RedirectResponse
    {
        $route = static::routeNameFor($user);

        if (! $route) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Role akun tidak valid. Silakan hubungi administrator.',
            ]);
        }

        return redirect()->intended(route($route));
    }
}

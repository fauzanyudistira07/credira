<?php

namespace App\Http\Middleware;

use App\Support\RoleRedirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->hasValidRole()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Role akun tidak dikenali. Silakan hubungi administrator.',
            ]);
        }

        if ($roles !== [] && ! $user->hasRole(...$roles)) {
            return RoleRedirect::redirectFor($user)->with('status', 'Anda tidak memiliki akses ke halaman tersebut.');
        }

        return $next($request);
    }
}

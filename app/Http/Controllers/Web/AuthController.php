<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\User;
use App\Support\RoleRedirect;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('public.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        if (! Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
        ], (bool) ($validated['remember'] ?? false))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password tidak valid.',
            ]);
        }

        $request->session()->regenerate();

        return RoleRedirect::redirectFor($request->user());
    }

    public function showRegister(): View
    {
        return view('public.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'agree' => ['accepted'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => User::ROLE_USER,
            'password' => Hash::make($validated['password']),
        ]);

        Pelanggan::create([
            'user_id' => $user->id,
            'nama_lengkap' => $validated['name'],
            'nama_pelanggan' => $validated['name'],
            'email' => $validated['email'],
            'kata_sandi' => $user->password,
            'no_telp' => 'pending-'.$user->id,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return RoleRedirect::redirectFor($user);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function showForgotPassword(): View
    {
        return view('public.auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink($validated);

        return back()->with('status', __($status));
    }

    public function showResetPassword(Request $request, string $token): View
    {
        return view('public.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $validated,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        return redirect()->route('login')->with('status', __($status));
    }
}

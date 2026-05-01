<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthApiController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'no_hp' => ['required', 'string', 'max:20', 'unique:pelanggan,no_telp'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['nama_lengkap'],
            'email' => $validated['email'],
            'role' => 'user',
            'password' => Hash::make($validated['password']),
        ]);

        Pelanggan::create([
            'user_id' => $user->id,
            'nama_lengkap' => $validated['nama_lengkap'],
            'email' => $validated['email'],
            'no_telp' => $validated['no_hp'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return $this->apiResponse([
            'user' => $user->load('pelanggan'),
        ], 'Registrasi berhasil.', 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $email = filter_var($validated['login'], FILTER_VALIDATE_EMAIL)
            ? $validated['login']
            : Pelanggan::where('no_telp', $validated['login'])->value('email');

        if (! $email || ! Auth::attempt(['email' => $email, 'password' => $validated['password']], (bool) ($validated['remember'] ?? false))) {
            throw ValidationException::withMessages([
                'login' => 'Email/nomor HP atau password tidak valid.',
            ]);
        }

        $request->session()->regenerate();

        return $this->apiResponse([
            'user' => auth()->user()->load('pelanggan'),
        ], 'Login berhasil.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->apiResponse([], 'Logout berhasil.');
    }

    public function forgotPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink($validated);

        return $this->apiResponse([
            'status' => __($status),
        ], 'Permintaan reset password diproses.');
    }

    public function resetPassword(Request $request)
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

        return $this->apiResponse([], 'Password berhasil direset.');
    }
}

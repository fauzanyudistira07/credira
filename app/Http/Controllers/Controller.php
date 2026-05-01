<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Http\JsonResponse;

abstract class Controller
{
    protected function currentPelanggan(): Pelanggan
    {
        $user = auth()->user();
        $pelanggan = $user?->pelanggan;

        if (! $pelanggan && $user?->role === User::ROLE_USER) {
            $pelanggan = Pelanggan::create([
                'user_id' => $user->id,
                'nama_lengkap' => $user->name,
                'nama_pelanggan' => $user->name,
                'email' => $user->email,
                'kata_sandi' => $user->password,
                'no_telp' => 'pending-'.$user->id,
            ]);
        }

        abort_if(! $pelanggan, 403, 'Akun ini tidak memiliki akses nasabah.');

        return $pelanggan;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function apiResponse(array $data = [], string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}

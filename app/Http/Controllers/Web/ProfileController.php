<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PelangganAddress;
use App\Support\LegacyDiagramSync;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function index(): View
    {
        $pelanggan = $this->currentPelanggan()->load('user', 'addresses');

        return view('user.profile.index', [
            'pelanggan' => $pelanggan,
            'addresses' => $pelanggan->addresses->sortByDesc('is_primary'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $pelanggan = $this->currentPelanggan();

        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'no_telp' => ['required', 'string', 'max:20', Rule::unique('pelanggan', 'no_telp')->ignore($pelanggan->id)],
            'no_ktp' => ['nullable', 'string', 'max:30'],
            'tanggal_lahir' => ['nullable', 'date'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'jenis_kelamin' => ['nullable', 'string', 'max:50'],
            'status_pernikahan' => ['nullable', 'string', 'max:50'],
            'pekerjaan_default' => ['nullable', 'string', 'max:255'],
            'penghasilan_default' => ['nullable', 'integer', 'min:0'],
            'foto_profil' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('foto_profil')) {
            if ($pelanggan->foto_profil) {
                Storage::disk('public')->delete($pelanggan->foto_profil);
            }

            $validated['foto_profil'] = $request->file('foto_profil')->store('profiles', 'public');
            $validated['foto'] = $validated['foto_profil'];
        }

        $user->update([
            'name' => $validated['nama_lengkap'],
            'email' => $validated['email'],
        ]);

        $pelanggan->update($validated);
        LegacyDiagramSync::syncPelanggan($pelanggan->load('addresses'));

        return back()->with('status', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], auth()->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Password saat ini tidak sesuai.',
            ]);
        }

        auth()->user()->update([
            'password' => $validated['password'],
        ]);

        $pelanggan = $this->currentPelanggan();
        $pelanggan->update([
            'kata_sandi' => auth()->user()->password,
        ]);
        LegacyDiagramSync::syncPelanggan($pelanggan->load('addresses'));

        return back()->with('status', 'Password berhasil diubah.');
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->addressRules());
        $pelanggan = $this->currentPelanggan();

        if (! empty($validated['is_primary'])) {
            $pelanggan->addresses()->update(['is_primary' => false]);
        }

        $pelanggan->addresses()->create($validated);
        LegacyDiagramSync::syncPelanggan($pelanggan->load('addresses'));

        return back()->with('status', 'Alamat berhasil ditambahkan.');
    }

    public function updateAddress(Request $request, PelangganAddress $address): RedirectResponse
    {
        abort_unless($address->pelanggan_id === $this->currentPelanggan()->id, 404);

        $validated = $request->validate($this->addressRules());

        if (! empty($validated['is_primary'])) {
            $this->currentPelanggan()->addresses()->update(['is_primary' => false]);
        }

        $address->update($validated);
        LegacyDiagramSync::syncPelanggan($this->currentPelanggan()->load('addresses'));

        return back()->with('status', 'Alamat berhasil diperbarui.');
    }

    public function destroyAddress(PelangganAddress $address): RedirectResponse
    {
        abort_unless($address->pelanggan_id === $this->currentPelanggan()->id, 404);
        $address->delete();
        LegacyDiagramSync::syncPelanggan($this->currentPelanggan()->load('addresses'));

        return back()->with('status', 'Alamat berhasil dihapus.');
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function addressRules(): array
    {
        return [
            'label_alamat' => ['required', 'string', 'max:100'],
            'penerima' => ['required', 'string', 'max:255'],
            'no_telp' => ['required', 'string', 'max:20'],
            'alamat_lengkap' => ['required', 'string', 'max:1000'],
            'kota' => ['required', 'string', 'max:255'],
            'provinsi' => ['required', 'string', 'max:255'],
            'kode_pos' => ['required', 'string', 'max:10'],
            'is_primary' => ['nullable', 'boolean'],
        ];
    }
}

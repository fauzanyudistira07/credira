<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengiriman;

class UserDeliveryApiController extends Controller
{
    public function index()
    {
        return response()->json(
            Pengiriman::with('application.motor')
                ->whereHas('application', fn ($builder) => $builder->where('pelanggan_id', $this->currentPelanggan()->id))
                ->latest()
                ->paginate(10)
        );
    }

    public function show(Pengiriman $delivery)
    {
        abort_unless($delivery->application->pelanggan_id === $this->currentPelanggan()->id, 404);

        return $this->apiResponse([
            'delivery' => $delivery->load('application.motor', 'address'),
        ], 'Detail pengiriman berhasil dimuat.');
    }
}

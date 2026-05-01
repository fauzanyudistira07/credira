<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pengiriman;
use Illuminate\Contracts\View\View;

class DeliveryController extends Controller
{
    public function index(): View
    {
        $deliveries = Pengiriman::with('application.motor')
            ->whereHas('application', fn ($builder) => $builder->where('pelanggan_id', $this->currentPelanggan()->id))
            ->latest()
            ->paginate(10);

        return view('user.deliveries.index', [
            'deliveries' => $deliveries,
        ]);
    }

    public function show(Pengiriman $delivery): View
    {
        abort_unless($delivery->application->pelanggan_id === $this->currentPelanggan()->id, 404);

        return view('user.deliveries.show', [
            'delivery' => $delivery->load('application.motor', 'address'),
        ]);
    }
}

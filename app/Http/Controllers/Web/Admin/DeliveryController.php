<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\DeliveryStatus;
use App\Http\Controllers\Controller;
use App\Models\Pengiriman;
use App\Services\AdminWorkflowService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function __construct(
        private readonly AdminWorkflowService $adminWorkflowService,
    ) {
    }

    public function index(Request $request): View
    {
        $deliveries = Pengiriman::query()
            ->with(['application.pelanggan.user', 'application.motor', 'address'])
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status_kirim', $request->string('status')->toString())
            )
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = $request->string('q')->toString();

                $query->where(function ($builder) use ($keyword) {
                    $builder->where('invoice', 'like', '%'.$keyword.'%')
                        ->orWhereHas('application', fn ($applicationQuery) => $applicationQuery->where('kode_pengajuan', 'like', '%'.$keyword.'%'))
                        ->orWhereHas('application.pelanggan', fn ($pelangganQuery) => $pelangganQuery->where('nama_lengkap', 'like', '%'.$keyword.'%'))
                        ->orWhereHas('application.motor', fn ($motorQuery) => $motorQuery->where('nama_motor', 'like', '%'.$keyword.'%'));
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.deliveries.index', [
            'deliveries' => $deliveries,
            'statuses' => DeliveryStatus::cases(),
            'currentStatus' => $request->string('status')->toString(),
            'keyword' => $request->string('q')->toString(),
            'summary' => [
                'total' => Pengiriman::count(),
                'waiting' => Pengiriman::where('status_kirim', DeliveryStatus::MenungguPengiriman->value)->count(),
                'on_delivery' => Pengiriman::whereIn('status_kirim', [
                    DeliveryStatus::Disiapkan->value,
                    DeliveryStatus::Dikirim->value,
                ])->count(),
                'completed' => Pengiriman::where('status_kirim', DeliveryStatus::SampaiTujuan->value)->count(),
            ],
        ]);
    }

    public function show(Pengiriman $delivery): View
    {
        return view('admin.deliveries.show', [
            'delivery' => $delivery->load(['application.pelanggan.user', 'application.motor', 'address']),
            'statusOptions' => DeliveryStatus::cases(),
        ]);
    }

    public function update(Request $request, Pengiriman $delivery): RedirectResponse
    {
        $validated = $request->validate([
            'status_kirim' => ['required', 'string'],
            'tgl_kirim' => ['nullable', 'date'],
            'tgl_tiba' => ['nullable', 'date'],
            'nama_kurir' => ['nullable', 'string', 'max:255'],
            'telpon_kurir' => ['nullable', 'string', 'max:30'],
            'nama_penerima' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ]);
        $admin = auth()->user();
        abort_if(! $admin, 403);

        $this->adminWorkflowService->updateDelivery(
            $delivery,
            $validated,
            $admin,
        );

        return back()->with('status', 'Data pengiriman berhasil diperbarui.');
    }
}

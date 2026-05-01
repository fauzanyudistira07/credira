<?php

namespace App\Http\Controllers\Web\Marketing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\StorePelangganRequest;
use App\Http\Requests\Marketing\UpdatePelangganRequest;
use App\Models\Pelanggan;
use App\Services\MarketingCustomerService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    public function __construct(
        private readonly MarketingCustomerService $customerService,
    ) {
    }

    public function index(Request $request): View
    {
        $marketing = $request->user();

        $pelanggan = Pelanggan::query()
            ->with(['user', 'pengajuanKredit'])
            ->ownedByMarketing($marketing->id)
            ->when($request->filled('q'), function ($builder) use ($request) {
                $search = trim($request->string('q')->toString());

                $builder->where(function ($pelangganQuery) use ($search) {
                    $pelangganQuery
                        ->where('nama_lengkap', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('no_telp', 'like', '%'.$search.'%');
                });
            })
            ->withCount('pengajuanKredit')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('marketing.pelanggan.index', [
            'pelanggan' => $pelanggan,
            'filters' => $request->only(['q']),
        ]);
    }

    public function create(): View
    {
        return view('marketing.pelanggan.create');
    }

    public function store(StorePelangganRequest $request): RedirectResponse
    {
        $pelanggan = $this->customerService->create($request->user(), $request->validated() + $request->allFiles());

        return redirect()
            ->route('marketing.pelanggan.show', $pelanggan)
            ->with('status', 'Pelanggan baru berhasil ditambahkan.');
    }

    public function show(Pelanggan $pelanggan): View
    {
        $pelanggan = $this->ownedPelanggan($pelanggan)->load([
            'user',
            'addresses',
            'pengajuanKredit.motor',
            'pengajuanKredit.jenisCicilan',
        ]);

        return view('marketing.pelanggan.show', [
            'pelanggan' => $pelanggan,
        ]);
    }

    public function edit(Pelanggan $pelanggan): View
    {
        return view('marketing.pelanggan.edit', [
            'pelanggan' => $this->ownedPelanggan($pelanggan)->load('user', 'addresses'),
        ]);
    }

    public function update(UpdatePelangganRequest $request, Pelanggan $pelanggan): RedirectResponse
    {
        $pelanggan = $this->ownedPelanggan($pelanggan);

        $this->customerService->update($request->user(), $pelanggan, $request->validated() + $request->allFiles());

        return redirect()
            ->route('marketing.pelanggan.show', $pelanggan)
            ->with('status', 'Data pelanggan berhasil diperbarui.');
    }

    private function ownedPelanggan(Pelanggan $pelanggan): Pelanggan
    {
        abort_unless($pelanggan->marketing_user_id === auth()->id(), 404);

        return $pelanggan;
    }
}

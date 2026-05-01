<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\PaymentVerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Services\AdminWorkflowService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly AdminWorkflowService $adminWorkflowService,
    ) {
    }

    public function index(Request $request): View
    {
        $payments = Pembayaran::query()
            ->with(['pelanggan.user', 'installment.application.motor', 'metodeBayar'])
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status_verifikasi', $request->string('status')->toString())
            )
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = $request->string('q')->toString();

                $query->where(function ($builder) use ($keyword) {
                    $builder->where('kode_pembayaran', 'like', '%'.$keyword.'%')
                        ->orWhereHas('pelanggan', fn ($pelangganQuery) => $pelangganQuery->where('nama_lengkap', 'like', '%'.$keyword.'%'))
                        ->orWhereHas('installment.application.motor', fn ($motorQuery) => $motorQuery->where('nama_motor', 'like', '%'.$keyword.'%'));
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.payments.index', [
            'payments' => $payments,
            'statuses' => PaymentVerificationStatus::cases(),
            'currentStatus' => $request->string('status')->toString(),
            'keyword' => $request->string('q')->toString(),
            'summary' => [
                'total' => Pembayaran::count(),
                'pending' => Pembayaran::where('status_verifikasi', PaymentVerificationStatus::Pending->value)->count(),
                'valid' => Pembayaran::where('status_verifikasi', PaymentVerificationStatus::Valid->value)->count(),
                'rejected' => Pembayaran::where('status_verifikasi', PaymentVerificationStatus::Ditolak->value)->count(),
            ],
        ]);
    }

    public function show(Pembayaran $payment): View
    {
        return view('admin.payments.show', [
            'payment' => $payment->load(['pelanggan.user', 'installment.application.motor', 'metodeBayar']),
            'statusOptions' => PaymentVerificationStatus::cases(),
        ]);
    }

    public function updateStatus(Request $request, Pembayaran $payment): RedirectResponse
    {
        $validated = $request->validate([
            'status_verifikasi' => ['required', 'string'],
            'catatan_verifikasi' => ['nullable', 'string', 'max:1000'],
        ]);
        $admin = auth()->user();
        abort_if(! $admin, 403);

        $this->adminWorkflowService->verifyPayment(
            $payment,
            $validated['status_verifikasi'],
            $validated['catatan_verifikasi'] ?? null,
            $admin,
        );

        return back()->with('status', 'Status pembayaran berhasil diperbarui.');
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Angsuran;
use App\Models\MetodeBayar;
use App\Models\Pembayaran;
use App\Services\PaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {
    }

    public function index(): View
    {
        $payments = $this->currentPelanggan()->payments()
            ->with('installment.application.motor')
            ->latest()
            ->paginate(10);

        return view('user.payments.index', [
            'payments' => $payments,
        ]);
    }

    public function create(Request $request): View
    {
        $installments = Angsuran::with('application.motor')
            ->whereHas('application', fn ($builder) => $builder->where('pelanggan_id', $this->currentPelanggan()->id))
            ->whereIn('status_pembayaran', ['belum_bayar', 'telat', 'gagal_verifikasi'])
            ->orderBy('tanggal_jatuh_tempo')
            ->get();

        return view('user.payments.create', [
            'installments' => $installments,
            'selectedInstallment' => $installments->firstWhere('id', $request->integer('installment')),
            'paymentMethods' => MetodeBayar::where('status_aktif', true)->orderBy('metode_pembayaran')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'angsuran_id' => ['required', 'exists:angsuran,id'],
            'nominal_bayar' => ['required', 'integer', 'min:0'],
            'tanggal_bayar' => ['required', 'date'],
            'id_metode_bayar' => ['required', 'exists:metode_bayar,id'],
            'nama_bank_pengirim' => ['nullable', 'string', 'max:100'],
            'nama_pemilik_rekening' => ['nullable', 'string', 'max:255'],
            'bukti_bayar' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $payment = $this->paymentService->create($this->currentPelanggan(), $validated);

        return redirect()->route('user.payments.show', $payment)->with('status', 'Bukti pembayaran berhasil diunggah.');
    }

    public function show(Pembayaran $payment): View
    {
        abort_unless($payment->pelanggan_id === $this->currentPelanggan()->id, 404);

        return view('user.payments.show', [
            'payment' => $payment->load('installment.application.motor', 'metodeBayar'),
        ]);
    }

    public function receipt(Pembayaran $payment): View
    {
        abort_unless($payment->pelanggan_id === $this->currentPelanggan()->id, 404);

        return view('user.payments.receipt', [
            'payment' => $payment->load('installment.application.motor', 'metodeBayar', 'verifier'),
        ]);
    }
}

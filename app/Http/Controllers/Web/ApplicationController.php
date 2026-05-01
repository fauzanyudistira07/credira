<?php

namespace App\Http\Controllers\Web;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Asuransi;
use App\Models\DokumenPengajuan;
use App\Models\JenisCicilan;
use App\Models\Motor;
use App\Models\PengajuanKredit;
use App\Services\ApplicationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function __construct(
        private readonly ApplicationService $applicationService,
    ) {
    }

    public function index(Request $request): View
    {
        $applications = $this->currentPelanggan()->applications()
            ->with(['motor', 'jenisCicilan'])
            ->when($request->filled('status'), fn ($builder) => $builder->where('status_pengajuan', $request->string('status')->toString()))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('user.applications.index', [
            'applications' => $applications,
            'statuses' => collect(ApplicationStatus::cases())->map(fn (ApplicationStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ]),
            'currentStatus' => $request->string('status')->toString(),
        ]);
    }

    public function create(): View
    {
        return view('user.applications.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $submit = $request->input('action', 'submit') !== 'draft';
        $validated = $request->validate($this->applicationRules($submit));
        $validated['documents'] = $request->file('documents', []);

        $application = $this->applicationService->create($this->currentPelanggan(), $validated, $submit);

        return redirect()
            ->route('user.applications.show', $application)
            ->with('status', $submit ? 'Pengajuan berhasil dikirim.' : 'Draft pengajuan berhasil disimpan.');
    }

    public function show(PengajuanKredit $application): View
    {
        $application = $this->ownedApplication($application)->load([
            'motor.jenisMotor',
            'jenisCicilan',
            'asuransi',
            'documents',
            'financialDetail',
            'logs.changedBy',
            'installments.payments',
            'delivery.address',
        ]);

        return view('user.applications.show', [
            'application' => $application,
            'canEdit' => in_array($application->status_pengajuan, [
                ApplicationStatus::Draft->value,
                ApplicationStatus::MenungguKonfirmasi->value,
                ApplicationStatus::VerifikasiDokumen->value,
            ], true),
        ]);
    }

    public function edit(PengajuanKredit $application): View
    {
        $application = $this->ownedApplication($application)->load(['financialDetail', 'documents']);

        return view('user.applications.create', array_merge($this->formData(), [
            'application' => $application,
        ]));
    }

    public function update(Request $request, PengajuanKredit $application): RedirectResponse
    {
        $application = $this->ownedApplication($application);
        $validated = $request->validate($this->applicationRules(false));
        $validated['documents'] = $request->file('documents', []);

        $this->applicationService->update($application, $validated);

        return redirect()->route('user.applications.show', $application)->with('status', 'Pengajuan berhasil diperbarui.');
    }

    public function documents(PengajuanKredit $application): View
    {
        return view('user.applications.documents', [
            'application' => $this->ownedApplication($application)->load('documents'),
        ]);
    }

    public function uploadDocuments(Request $request, PengajuanKredit $application): RedirectResponse
    {
        $application = $this->ownedApplication($application);
        $validated = $request->validate($this->documentRules(false));
        $this->applicationService->storeDocuments($application, $request->file('documents', []));

        return back()->with('status', 'Dokumen berhasil diunggah.');
    }

    public function submit(PengajuanKredit $application): RedirectResponse
    {
        $application = $this->ownedApplication($application);
        $this->applicationService->submit($application);

        return back()->with('status', 'Pengajuan berhasil disubmit.');
    }

    public function cancel(PengajuanKredit $application): RedirectResponse
    {
        $application = $this->ownedApplication($application);
        $this->applicationService->cancel($application);

        return back()->with('status', 'Pengajuan berhasil dibatalkan.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        $pelanggan = $this->currentPelanggan()->load('addresses');

        return [
            'motors' => Motor::where('status_aktif', true)->orderBy('nama_motor')->get(),
            'plans' => JenisCicilan::orderBy('durasi_bulan')->get(),
            'insurances' => Asuransi::orderBy('nama_asuransi')->get(),
            'pelanggan' => $pelanggan,
            'addresses' => $pelanggan->addresses,
            'application' => null,
        ];
    }

    /**
     * @return array<string, array<int, string|\Illuminate\Validation\Rules\Exists>>
     */
    private function applicationRules(bool $requireDocuments): array
    {
        return array_merge([
            'motor_id' => ['required', 'exists:motors,id'],
            'jenis_cicilan_id' => ['required', 'exists:jenis_cicilan,id'],
            'asuransi_id' => ['nullable', 'exists:asuransi,id'],
            'dp' => ['required', 'integer', 'min:0'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'no_ktp' => ['required', 'string', 'max:30'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', 'string', 'max:50'],
            'status_pernikahan' => ['nullable', 'string', 'max:50'],
            'nomor_hp' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'alamat_pengiriman_id' => ['nullable', 'exists:pelanggan_addresses,id'],
            'alamat_lengkap' => ['nullable', 'string', 'max:1000'],
            'kota' => ['nullable', 'string', 'max:255'],
            'provinsi' => ['nullable', 'string', 'max:255'],
            'kode_pos' => ['nullable', 'string', 'max:10'],
            'pekerjaan' => ['required', 'string', 'max:255'],
            'nama_perusahaan' => ['nullable', 'string', 'max:255'],
            'alamat_kantor' => ['nullable', 'string', 'max:1000'],
            'lama_bekerja' => ['nullable', 'string', 'max:100'],
            'penghasilan_bulanan' => ['required', 'integer', 'min:0'],
            'pengeluaran_bulanan' => ['required', 'integer', 'min:0'],
            'status_rumah' => ['nullable', 'string', 'max:100'],
            'kontak_darurat_nama' => ['required', 'string', 'max:255'],
            'kontak_darurat_nohp' => ['required', 'string', 'max:20'],
            'kontak_darurat_hubungan' => ['required', 'string', 'max:100'],
        ], $this->documentRules($requireDocuments));
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function documentRules(bool $required): array
    {
        $presence = $required ? 'required' : 'nullable';

        return [
            'documents.foto_ktp' => [$presence, 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'documents.slip_gaji' => [$presence, 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'documents.foto_selfie_ktp' => [$presence, 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'documents.kk' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'documents.npwp' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'documents.bukti_domisili' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    private function ownedApplication(PengajuanKredit $application): PengajuanKredit
    {
        abort_unless($application->pelanggan_id === $this->currentPelanggan()->id, 404);

        return $application;
    }
}

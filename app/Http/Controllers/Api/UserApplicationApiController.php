<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\DokumenPengajuan;
use App\Models\PengajuanKredit;
use App\Services\ApplicationService;
use Illuminate\Http\Request;

class UserApplicationApiController extends Controller
{
    public function __construct(
        private readonly ApplicationService $applicationService,
    ) {
    }

    public function index(Request $request)
    {
        $applications = $this->currentPelanggan()->applications()
            ->with(['motor', 'jenisCicilan', 'documents'])
            ->when($request->filled('status'), fn ($builder) => $builder->where('status_pengajuan', $request->string('status')->toString()))
            ->latest()
            ->paginate(10);

        return response()->json($applications);
    }

    public function store(Request $request)
    {
        $submit = ! $request->boolean('draft');
        $validated = $request->validate($this->rules($submit));
        $validated['documents'] = $request->file('documents', []);
        $application = $this->applicationService->create($this->currentPelanggan(), $validated, $submit);

        return $this->apiResponse([
            'application' => $application,
        ], 'Pengajuan berhasil disimpan.', 201);
    }

    public function show(PengajuanKredit $application)
    {
        return $this->apiResponse([
            'application' => $this->ownedApplication($application)->load([
                'motor.jenisMotor',
                'jenisCicilan',
                'asuransi',
                'documents',
                'financialDetail',
                'logs.changedBy',
                'installments',
                'delivery',
            ]),
        ], 'Detail pengajuan berhasil dimuat.');
    }

    public function update(Request $request, PengajuanKredit $application)
    {
        $application = $this->ownedApplication($application);
        $validated = $request->validate($this->rules(false));
        $validated['documents'] = $request->file('documents', []);
        $application = $this->applicationService->update($application, $validated);

        return $this->apiResponse([
            'application' => $application,
        ], 'Pengajuan berhasil diperbarui.');
    }

    public function submit(PengajuanKredit $application)
    {
        $application = $this->applicationService->submit($this->ownedApplication($application));

        return $this->apiResponse([
            'application' => $application,
        ], 'Pengajuan berhasil disubmit.');
    }

    public function cancel(PengajuanKredit $application)
    {
        $application = $this->applicationService->cancel($this->ownedApplication($application));

        return $this->apiResponse([
            'application' => $application,
        ], 'Pengajuan berhasil dibatalkan.');
    }

    public function logs(PengajuanKredit $application)
    {
        return $this->apiResponse([
            'logs' => $this->ownedApplication($application)->logs()->with('changedBy')->get(),
        ], 'Log pengajuan berhasil dimuat.');
    }

    public function storeDocuments(Request $request, PengajuanKredit $application)
    {
        $application = $this->ownedApplication($application);
        $request->validate($this->documentRules(false));
        $this->applicationService->storeDocuments($application, $request->file('documents', []));

        return $this->apiResponse([
            'documents' => $application->fresh('documents')->documents,
        ], 'Dokumen berhasil diunggah.', 201);
    }

    public function documents(PengajuanKredit $application)
    {
        return $this->apiResponse([
            'documents' => $this->ownedApplication($application)->documents()->latest()->get(),
        ], 'Dokumen pengajuan berhasil dimuat.');
    }

    public function destroyDocument(DokumenPengajuan $document)
    {
        $application = PengajuanKredit::findOrFail($document->pengajuan_id);
        $this->applicationService->deleteDocument($this->ownedApplication($application), $document);

        return $this->apiResponse([], 'Dokumen berhasil dihapus.');
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function rules(bool $requireDocuments): array
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

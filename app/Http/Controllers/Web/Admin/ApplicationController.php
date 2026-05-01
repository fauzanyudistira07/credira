<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\DocumentVerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\DokumenPengajuan;
use App\Models\PengajuanKredit;
use App\Services\AdminWorkflowService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function __construct(
        private readonly AdminWorkflowService $adminWorkflowService,
    ) {
    }

    public function index(Request $request): View
    {
        $applications = PengajuanKredit::query()
            ->with(['pelanggan.user', 'motor', 'jenisCicilan'])
            ->withCount('documents')
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status_pengajuan', $request->string('status')->toString())
            )
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = $request->string('q')->toString();

                $query->where(function ($builder) use ($keyword) {
                    $builder->where('kode_pengajuan', 'like', '%'.$keyword.'%')
                        ->orWhereHas('pelanggan', fn ($pelangganQuery) => $pelangganQuery->where('nama_lengkap', 'like', '%'.$keyword.'%'))
                        ->orWhereHas('motor', fn ($motorQuery) => $motorQuery->where('nama_motor', 'like', '%'.$keyword.'%'));
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $queueStatuses = [
            ApplicationStatus::MenungguKonfirmasi->value,
            ApplicationStatus::VerifikasiDokumen->value,
            ApplicationStatus::Diproses->value,
            ApplicationStatus::Survey->value,
        ];

        return view('admin.applications.index', [
            'applications' => $applications,
            'statuses' => collect(ApplicationStatus::cases())->map(fn (ApplicationStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ]),
            'currentStatus' => $request->string('status')->toString(),
            'keyword' => $request->string('q')->toString(),
            'summary' => [
                'total' => PengajuanKredit::count(),
                'in_queue' => PengajuanKredit::whereIn('status_pengajuan', $queueStatuses)->count(),
                'approved' => PengajuanKredit::whereIn('status_pengajuan', [
                    ApplicationStatus::Disetujui->value,
                    ApplicationStatus::KontrakAktif->value,
                ])->count(),
                'rejected' => PengajuanKredit::whereIn('status_pengajuan', [
                    ApplicationStatus::Ditolak->value,
                    ApplicationStatus::DibatalkanAdmin->value,
                ])->count(),
            ],
        ]);
    }

    public function show(PengajuanKredit $application): View
    {
        return view('admin.applications.show', [
            'application' => $application->load([
                'pelanggan.user',
                'motor.jenisMotor',
                'jenisCicilan',
                'asuransi',
                'financialDetail',
                'documents',
                'logs.changedBy',
                'installments.payments',
                'delivery.address',
            ]),
            'statusOptions' => collect(ApplicationStatus::cases())
                ->reject(fn (ApplicationStatus $status) => $status === ApplicationStatus::Draft)
                ->values(),
            'documentStatusOptions' => DocumentVerificationStatus::cases(),
        ]);
    }

    public function updateStatus(Request $request, PengajuanKredit $application): RedirectResponse
    {
        $validated = $request->validate([
            'status_pengajuan' => ['required', 'string'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);
        $admin = auth()->user();
        abort_if(! $admin, 403);

        $this->adminWorkflowService->updateApplicationStatus(
            $application,
            $validated['status_pengajuan'],
            $validated['catatan'] ?? null,
            $admin,
        );

        return back()->with('status', 'Status pengajuan berhasil diperbarui.');
    }

    public function verifyDocument(Request $request, PengajuanKredit $application, DokumenPengajuan $document): RedirectResponse
    {
        abort_unless($document->pengajuan_id === $application->id, 404);

        $validated = $request->validate([
            'status_verifikasi' => ['required', 'string'],
            'catatan_verifikasi' => ['nullable', 'string', 'max:1000'],
        ]);
        $admin = auth()->user();
        abort_if(! $admin, 403);

        $this->adminWorkflowService->verifyDocument(
            $document,
            $validated['status_verifikasi'],
            $validated['catatan_verifikasi'] ?? null,
            $admin,
        );

        return back()->with('status', 'Status dokumen berhasil diperbarui.');
    }
}

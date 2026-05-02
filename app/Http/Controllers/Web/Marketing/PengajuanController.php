<?php

namespace App\Http\Controllers\Web\Marketing;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\ReviewPengajuanRequest;
use App\Http\Requests\Marketing\StorePengajuanRequest;
use App\Models\Asuransi;
use App\Models\JenisCicilan;
use App\Models\Motor;
use App\Models\Pelanggan;
use App\Models\PengajuanKredit;
use App\Services\AdminWorkflowService;
use App\Services\MarketingApplicationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PengajuanController extends Controller
{
    public function __construct(
        private readonly MarketingApplicationService $applicationService,
        private readonly AdminWorkflowService $workflowService,
    ) {
    }

    public function index(Request $request): View
    {
        $query = PengajuanKredit::query()
            ->with(['pelanggan.user', 'motor', 'jenisCicilan'])
            ->when($request->filled('q'), function ($builder) use ($request) {
                $search = trim($request->string('q')->toString());

                $builder->where(function ($pengajuanQuery) use ($search) {
                    $pengajuanQuery
                        ->where('kode_pengajuan', 'like', '%'.$search.'%')
                        ->orWhereHas('pelanggan', fn ($pelangganQuery) => $pelangganQuery->where('nama_lengkap', 'like', '%'.$search.'%'));
                });
            })
            ->when($request->filled('status'), function ($builder) use ($request) {
                match ($request->string('status')->toString()) {
                    'pending' => $builder->pending(),
                    'review' => $builder->review(),
                    'approved' => $builder->approved(),
                    'rejected' => $builder->rejected(),
                    default => $builder,
                };
            })
            ->latest();

        return view('marketing.pengajuan.index', [
            'pengajuan' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['q', 'status']),
            'statusOptions' => [
                'pending' => 'Pending',
                'review' => 'Review',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
            ],
        ]);
    }

    public function create(Request $request): View
    {
        return view('marketing.pengajuan.create', [
            'pelanggan' => Pelanggan::query()
                ->orderBy('nama_lengkap')
                ->get(),
            'motors' => Motor::query()->with('jenisMotor')->active()->orderBy('nama_motor')->get(),
            'plans' => JenisCicilan::orderBy('durasi_bulan')->get(),
            'insurances' => Asuransi::orderBy('nama_asuransi')->get(),
            'selectedPelangganId' => $request->integer('pelanggan_id') ?: null,
            'selectedMotorId' => $request->integer('motor_id') ?: null,
        ]);
    }

    public function store(StorePengajuanRequest $request): RedirectResponse
    {
        $marketing = $request->user();
        $pelanggan = Pelanggan::query()->findOrFail($request->integer('pelanggan_id'));

        $application = $this->applicationService->create(
            $marketing,
            $pelanggan,
            $request->validated() + ['documents' => $request->file('documents', [])],
        );

        return redirect()
            ->route('marketing.pengajuan.show', $application)
            ->with('status', 'Pengajuan kredit berhasil dibuat.');
    }

    public function show(PengajuanKredit $pengajuan): View
    {
        $pengajuan = $this->ownedApplication($pengajuan)->load([
            'pelanggan.user',
            'marketingOwner',
            'motor.jenisMotor',
            'jenisCicilan',
            'asuransi',
            'financialDetail',
            'documents',
            'logs.changedBy',
        ]);

        return view('marketing.pengajuan.show', [
            'pengajuan' => $pengajuan,
        ]);
    }

    public function review(PengajuanKredit $pengajuan): View
    {
        return view('marketing.pengajuan.review', [
            'pengajuan' => $this->ownedApplication($pengajuan)->load([
                'pelanggan.user',
                'marketingOwner',
                'motor.jenisMotor',
                'jenisCicilan',
                'documents',
                'logs.changedBy',
            ]),
            'reviewOptions' => [
                'pending' => 'Set Pending',
                'review' => 'Set Review',
                'approved' => 'Approve',
                'rejected' => 'Reject',
            ],
        ]);
    }

    public function updateStatus(ReviewPengajuanRequest $request, PengajuanKredit $pengajuan): RedirectResponse
    {
        $pengajuan = $this->ownedApplication($pengajuan);
        $statusMap = [
            'pending' => ApplicationStatus::MenungguKonfirmasi->value,
            'review' => ApplicationStatus::VerifikasiDokumen->value,
            'approved' => ApplicationStatus::Disetujui->value,
            'rejected' => ApplicationStatus::Ditolak->value,
        ];

        $validated = $request->validated();

        $this->workflowService->updateApplicationStatus(
            $pengajuan,
            $statusMap[$validated['status']],
            $validated['catatan'] ?? null,
            $request->user(),
        );

        return redirect()
            ->route('marketing.pengajuan.review', $pengajuan)
            ->with('status', 'Status pengajuan berhasil diperbarui oleh marketing.');
    }

    private function ownedApplication(PengajuanKredit $pengajuan): PengajuanKredit
    {
        return $pengajuan;
    }
}

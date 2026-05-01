<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewPengajuanRequest;
use App\Models\PengajuanKredit;
use App\Models\User;
use App\Services\AdminWorkflowService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PengajuanController extends Controller
{
    public function __construct(
        private readonly AdminWorkflowService $workflowService,
    ) {
    }

    public function index(Request $request): View|StreamedResponse
    {
        $filters = $request->only(['q', 'status', 'marketing_id']);

        $query = PengajuanKredit::query()
            ->with(['pelanggan', 'motor', 'marketingOwner', 'jenisCicilan', 'asuransi'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = trim($request->string('q')->toString());

                $query->where(function ($builder) use ($keyword) {
                    $builder
                        ->where('kode_pengajuan', 'like', '%'.$keyword.'%')
                        ->orWhereHas('pelanggan', fn ($pelangganQuery) => $pelangganQuery->where('nama_lengkap', 'like', '%'.$keyword.'%'));
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                match ($request->string('status')->toString()) {
                    'pending' => $query->pending(),
                    'review' => $query->review(),
                    'approved' => $query->approved(),
                    'rejected' => $query->rejected(),
                    default => $query,
                };
            })
            ->when($request->filled('marketing_id'), fn ($query) => $query->where('marketing_user_id', $request->integer('marketing_id')))
            ->latest();

        if ($request->string('export')->toString() === 'csv') {
            return $this->exportCsv(clone $query);
        }

        $pengajuan = $query
            ->paginate(10)
            ->withQueryString();

        return view('admin.pengajuan.index', [
            'pengajuan' => $pengajuan,
            'filters' => $filters,
            'statusOptions' => [
                'pending' => 'Pending',
                'review' => 'Review',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
            ],
            'marketingUsers' => User::query()
                ->where('role', User::ROLE_MARKETING)
                ->orderBy('name')
                ->get(),
            'summary' => [
                'total' => PengajuanKredit::count(),
                'pending' => PengajuanKredit::pending()->count(),
                'review' => PengajuanKredit::review()->count(),
                'approved' => PengajuanKredit::approved()->count(),
                'rejected' => PengajuanKredit::rejected()->count(),
            ],
        ]);
    }

    public function show(PengajuanKredit $pengajuan): View
    {
        return view('admin.pengajuan.show', [
            'pengajuan' => $this->loadPengajuan($pengajuan),
        ]);
    }

    public function review(PengajuanKredit $pengajuan): View
    {
        return view('admin.pengajuan.review', [
            'pengajuan' => $this->loadPengajuan($pengajuan),
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
            ->route('admin.pengajuan.review', $pengajuan)
            ->with('status', 'Status pengajuan berhasil diperbarui.');
    }

    private function loadPengajuan(PengajuanKredit $pengajuan): PengajuanKredit
    {
        return $pengajuan->load([
            'pelanggan.user',
            'marketingOwner',
            'motor.jenisMotor',
            'jenisCicilan',
            'asuransi',
            'financialDetail',
            'documents',
            'logs.changedBy',
        ]);
    }

    private function exportCsv($query): StreamedResponse
    {
        $rows = $query->get();

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');

            fwrite($handle, chr(239).chr(187).chr(191));
            fputcsv($handle, ['Kode', 'Tanggal', 'Pelanggan', 'Marketing', 'Motor', 'Tenor', 'DP', 'Total', 'Status']);

            foreach ($rows as $item) {
                fputcsv($handle, [
                    $item->kode_pengajuan,
                    optional($item->tgl_pengajuan)->format('Y-m-d') ?: optional($item->created_at)->format('Y-m-d'),
                    $item->pelanggan?->display_name,
                    $item->marketingOwner?->name,
                    $item->motor?->nama_motor,
                    ($item->jenisCicilan?->durasi_bulan ?? '-') . ' bulan',
                    (int) $item->dp,
                    (int) $item->total_bayar,
                    $item->status_pengajuan,
                ]);
            }

            fclose($handle);
        }, 'admin-pengajuan-'.now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

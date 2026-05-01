<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PelangganController extends Controller
{
    public function index(Request $request): View|StreamedResponse
    {
        $filters = $request->only(['q', 'marketing_id']);

        $query = Pelanggan::query()
            ->with(['marketingOwner'])
            ->withCount('pengajuanKredit')
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = trim($request->string('q')->toString());

                $query->where(function ($builder) use ($keyword) {
                    $builder
                        ->where('nama_lengkap', 'like', '%'.$keyword.'%')
                        ->orWhere('email', 'like', '%'.$keyword.'%')
                        ->orWhere('no_telp', 'like', '%'.$keyword.'%');
                });
            })
            ->when($request->filled('marketing_id'), fn ($query) => $query->where('marketing_user_id', $request->integer('marketing_id')))
            ->latest();

        if ($request->string('export')->toString() === 'csv') {
            return $this->exportCsv(clone $query);
        }

        $pelanggan = $query
            ->paginate(12)
            ->withQueryString();

        return view('admin.pelanggan.index', [
            'pelanggan' => $pelanggan,
            'filters' => $filters,
            'marketingUsers' => User::query()
                ->where('role', User::ROLE_MARKETING)
                ->orderBy('name')
                ->get(),
            'summary' => [
                'total' => Pelanggan::count(),
                'with_pengajuan' => Pelanggan::has('pengajuanKredit')->count(),
                'without_pengajuan' => Pelanggan::doesntHave('pengajuanKredit')->count(),
            ],
        ]);
    }

    public function show(Pelanggan $pelanggan): View
    {
        return view('admin.pelanggan.show', [
            'pelanggan' => $pelanggan->load([
                'user',
                'marketingOwner',
                'addresses',
                'pengajuanKredit.motor',
                'pengajuanKredit.jenisCicilan',
                'pengajuanKredit.marketingOwner',
            ]),
        ]);
    }

    private function exportCsv($query): StreamedResponse
    {
        $rows = $query->get();

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');

            fwrite($handle, chr(239).chr(187).chr(191));
            fputcsv($handle, ['Pelanggan', 'Email', 'Telepon', 'KTP', 'Marketing', 'Jumlah Pengajuan', 'Dibuat']);

            foreach ($rows as $item) {
                fputcsv($handle, [
                    $item->display_name,
                    $item->email,
                    $item->no_telp,
                    $item->no_ktp,
                    $item->marketingOwner?->name,
                    (int) $item->pengajuan_kredit_count,
                    optional($item->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, 'admin-pelanggan-'.now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

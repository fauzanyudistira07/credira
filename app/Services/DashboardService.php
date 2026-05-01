<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\DeliveryStatus;
use App\Enums\InstallmentPaymentStatus;
use App\Enums\PaymentVerificationStatus;
use App\Models\Pelanggan;

class DashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function build(Pelanggan $pelanggan): array
    {
        $activeApplicationStatuses = [
            ApplicationStatus::Draft->value,
            ApplicationStatus::MenungguKonfirmasi->value,
            ApplicationStatus::VerifikasiDokumen->value,
            ApplicationStatus::Diproses->value,
            ApplicationStatus::Survey->value,
            ApplicationStatus::Disetujui->value,
            ApplicationStatus::KontrakAktif->value,
        ];

        $applications = $pelanggan->applications()
            ->with(['motor', 'delivery', 'jenisCicilan'])
            ->latest()
            ->get();

        $installments = $pelanggan->applications()
            ->with('installments')
            ->get()
            ->flatMap->installments
            ->sortBy('tanggal_jatuh_tempo')
            ->values();

        $payments = $pelanggan->payments()
            ->with('installment.application.motor')
            ->latest()
            ->take(5)
            ->get();

        $pendingInstallments = $installments
            ->whereIn('status_pembayaran', [
                InstallmentPaymentStatus::BelumBayar->value,
                InstallmentPaymentStatus::Telat->value,
                InstallmentPaymentStatus::GagalVerifikasi->value,
            ])
            ->values();

        $nextInstallment = $pendingInstallments
            ->firstWhere('status_pembayaran', InstallmentPaymentStatus::BelumBayar->value)
            ?? $pendingInstallments->firstWhere('status_pembayaran', InstallmentPaymentStatus::Telat->value)
            ?? $pendingInstallments->first();

        $latestApplication = $applications->first();
        $activeApplication = $applications->first(
            fn ($application) => in_array($application->status_pengajuan, $activeApplicationStatuses, true)
        ) ?? $latestApplication;

        $deliveries = $applications
            ->pluck('delivery')
            ->filter()
            ->sortByDesc('updated_at')
            ->values();

        $latestDelivery = $deliveries->first();
        $activeDelivery = $deliveries->first(
            fn ($delivery) => in_array($delivery->status_kirim, [
                DeliveryStatus::MenungguPengiriman->value,
                DeliveryStatus::Disiapkan->value,
                DeliveryStatus::Dikirim->value,
            ], true)
        ) ?? $latestDelivery;

        $unpaidTotal = $pendingInstallments->sum('total_tagihan');
        $paidInstallments = $installments->where('status_pembayaran', InstallmentPaymentStatus::SudahBayar->value)->count();
        $overdueInstallments = $installments->where('status_pembayaran', InstallmentPaymentStatus::Telat->value)->count();
        $pendingPayments = $pelanggan->payments()
            ->where('status_verifikasi', PaymentVerificationStatus::Pending->value)
            ->count();
        $user = $pelanggan->user;
        $unreadNotifications = $user->notifications()->where('is_read', false)->count();

        return [
            'active_applications' => $applications->whereIn('status_pengajuan', $activeApplicationStatuses)->count(),
            'total_applications' => $applications->count(),
            'latest_application' => $latestApplication,
            'active_application' => $activeApplication,
            'unpaid_installment_total' => $unpaidTotal,
            'next_installment' => $nextInstallment,
            'pending_installments_count' => $pendingInstallments->count(),
            'paid_installments_count' => $paidInstallments,
            'overdue_installments_count' => $overdueInstallments,
            'latest_delivery' => $latestDelivery,
            'active_delivery' => $activeDelivery,
            'delivery_count' => $deliveries->count(),
            'unread_notifications' => $unreadNotifications,
            'pending_payments_count' => $pendingPayments,
            'notifications' => $user->notifications()->take(5)->get(),
            'recent_payments' => $payments,
            'applications' => $applications->take(4),
            'deliveries' => $deliveries->take(3),
        ];
    }
}

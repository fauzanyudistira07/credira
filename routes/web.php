<?php

use App\Http\Controllers\Web\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Web\Admin\DeliveryController as AdminDeliveryController;
use App\Http\Controllers\Web\Admin\MotorController as AdminMotorController;
use App\Http\Controllers\Web\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Web\Admin\PelangganController as AdminPelangganController;
use App\Http\Controllers\Web\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Web\Admin\PengajuanController as AdminPengajuanController;
use App\Http\Controllers\Web\Admin\UserController as AdminUserController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\Ceo\CustomerAnalyticsController;
use App\Http\Controllers\Web\Ceo\DashboardController as CeoDashboardController;
use App\Http\Controllers\Web\Ceo\MarketingPerformanceController;
use App\Http\Controllers\Web\Ceo\ProductAnalyticsController;
use App\Http\Controllers\Web\Ceo\ReportController;
use App\Http\Controllers\Web\DashboardController as UserDashboardController;
use App\Http\Controllers\Web\DashboardRedirectController;
use App\Http\Controllers\Web\DeliveryController;
use App\Http\Controllers\Web\InstallmentController;
use App\Http\Controllers\Web\MyCreditController;
use App\Http\Controllers\Web\Marketing\DashboardController as MarketingDashboardController;
use App\Http\Controllers\Web\Marketing\MotorController as MarketingMotorController;
use App\Http\Controllers\Web\Marketing\PelangganController as MarketingPelangganController;
use App\Http\Controllers\Web\Marketing\PengajuanController as MarketingPengajuanController;
use App\Http\Controllers\Web\Marketing\SimulationController as MarketingSimulationController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\PaymentController;
use App\Http\Controllers\Web\ProfilePageController;
use App\Http\Controllers\Web\ProfileController as UserProfileController;
use App\Http\Controllers\Web\PublicPageController;
use App\Http\Controllers\Web\ApplicationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::get('/tentang', [PublicPageController::class, 'about'])->name('about');
Route::get('/motor', [PublicPageController::class, 'motors'])->name('motors.index');
Route::get('/motor/{motor}', [PublicPageController::class, 'showMotor'])->name('motors.show');
Route::get('/simulasi-kredit', [PublicPageController::class, 'simulation'])->name('simulation');
Route::get('/cara-pengajuan', [PublicPageController::class, 'howToApply'])->name('how-to-apply');
Route::get('/faq', [PublicPageController::class, 'faq'])->name('faq');
Route::get('/kontak', [PublicPageController::class, 'contact'])->name('contact');
Route::post('/kontak', [PublicPageController::class, 'sendContact'])->name('contact.send');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardRedirectController::class)->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', ProfilePageController::class)->name('profile');

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');

        Route::get('/motors', [AdminMotorController::class, 'index'])->name('motors.index');
        Route::get('/motors/create', [AdminMotorController::class, 'create'])->name('motors.create');
        Route::post('/motors', [AdminMotorController::class, 'store'])->name('motors.store');
        Route::get('/motors/{motor}', [AdminMotorController::class, 'show'])->name('motors.show');
        Route::get('/motors/{motor}/edit', [AdminMotorController::class, 'edit'])->name('motors.edit');
        Route::put('/motors/{motor}', [AdminMotorController::class, 'update'])->name('motors.update');

        Route::get('/pengajuan', [AdminPengajuanController::class, 'index'])->name('pengajuan.index');
        Route::get('/pengajuan/{pengajuan}', [AdminPengajuanController::class, 'show'])->name('pengajuan.show');
        Route::get('/pengajuan/{pengajuan}/review', [AdminPengajuanController::class, 'review'])->name('pengajuan.review');
        Route::put('/pengajuan/{pengajuan}/update-status', [AdminPengajuanController::class, 'updateStatus'])->name('pengajuan.update-status');

        Route::get('/pelanggan', [AdminPelangganController::class, 'index'])->name('pelanggan.index');
        Route::get('/pelanggan/{pelanggan}', [AdminPelangganController::class, 'show'])->name('pelanggan.show');

        Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');
        Route::get('/applications/{application}', [AdminApplicationController::class, 'show'])->name('applications.show');
        Route::post('/applications/{application}/status', [AdminApplicationController::class, 'updateStatus'])->name('applications.status');
        Route::post('/applications/{application}/documents/{document}/verify', [AdminApplicationController::class, 'verifyDocument'])->name('applications.documents.verify');

        Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/{payment}', [AdminPaymentController::class, 'show'])->name('payments.show');
        Route::post('/payments/{payment}/status', [AdminPaymentController::class, 'updateStatus'])->name('payments.status');

        Route::get('/deliveries', [AdminDeliveryController::class, 'index'])->name('deliveries.index');
        Route::get('/deliveries/{delivery}', [AdminDeliveryController::class, 'show'])->name('deliveries.show');
        Route::put('/deliveries/{delivery}', [AdminDeliveryController::class, 'update'])->name('deliveries.update');
    });

    Route::prefix('marketing')->name('marketing.')->middleware('role:marketing')->group(function () {
        Route::get('/dashboard', [MarketingDashboardController::class, 'index'])->name('dashboard');
        Route::get('/motors', [MarketingMotorController::class, 'index'])->name('motors.index');
        Route::get('/motors/{motor}', [MarketingMotorController::class, 'show'])->name('motors.show');
        Route::get('/simulasi', [MarketingSimulationController::class, 'index'])->name('simulasi.index');
        Route::get('/pelanggan', [MarketingPelangganController::class, 'index'])->name('pelanggan.index');
        Route::get('/pelanggan/create', [MarketingPelangganController::class, 'create'])->name('pelanggan.create');
        Route::post('/pelanggan', [MarketingPelangganController::class, 'store'])->name('pelanggan.store');
        Route::get('/pelanggan/{pelanggan}', [MarketingPelangganController::class, 'show'])->name('pelanggan.show');
        Route::get('/pelanggan/{pelanggan}/edit', [MarketingPelangganController::class, 'edit'])->name('pelanggan.edit');
        Route::put('/pelanggan/{pelanggan}', [MarketingPelangganController::class, 'update'])->name('pelanggan.update');
        Route::get('/pengajuan', [MarketingPengajuanController::class, 'index'])->name('pengajuan.index');
        Route::get('/pengajuan/create', [MarketingPengajuanController::class, 'create'])->name('pengajuan.create');
        Route::post('/pengajuan', [MarketingPengajuanController::class, 'store'])->name('pengajuan.store');
        Route::get('/pengajuan/{pengajuan}', [MarketingPengajuanController::class, 'show'])->name('pengajuan.show');
        Route::get('/pengajuan/{pengajuan}/review', [MarketingPengajuanController::class, 'review'])->name('pengajuan.review');
        Route::put('/pengajuan/{pengajuan}/update-status', [MarketingPengajuanController::class, 'updateStatus'])->name('pengajuan.update-status');
    });

    Route::prefix('ceo')->name('ceo.')->middleware('role:ceo')->group(function () {
        Route::get('/dashboard', [CeoDashboardController::class, 'index'])->name('dashboard');
        Route::get('/laporan', fn () => redirect()->route('ceo.reports.index'))->name('laporan.index');
        Route::get('/laporan-pengajuan', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/performa-marketing', [MarketingPerformanceController::class, 'index'])->name('marketing.index');
        Route::get('/statistik-motor', [ProductAnalyticsController::class, 'index'])->name('products.index');
        Route::get('/monitoring-pelanggan', [CustomerAnalyticsController::class, 'index'])->name('customers.index');
    });

    Route::prefix('user')->name('user.')->middleware('role:user')->group(function () {
        Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
        Route::get('/my-kredit', [MyCreditController::class, 'index'])->name('my-credit.index');

        Route::get('/applications', [ApplicationController::class, 'index'])->name('applications.index');
        Route::get('/applications/create', [ApplicationController::class, 'create'])->name('applications.create');
        Route::post('/applications', [ApplicationController::class, 'store'])->name('applications.store');
        Route::get('/applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');
        Route::get('/applications/{application}/edit', [ApplicationController::class, 'edit'])->name('applications.edit');
        Route::put('/applications/{application}', [ApplicationController::class, 'update'])->name('applications.update');
        Route::get('/applications/{application}/documents', [ApplicationController::class, 'documents'])->name('applications.documents');
        Route::post('/applications/{application}/documents', [ApplicationController::class, 'uploadDocuments'])->name('applications.documents.store');
        Route::post('/applications/{application}/submit', [ApplicationController::class, 'submit'])->name('applications.submit');
        Route::post('/applications/{application}/cancel', [ApplicationController::class, 'cancel'])->name('applications.cancel');

        Route::get('/installments', [InstallmentController::class, 'index'])->name('installments.index');
        Route::get('/installments/{installment}', [InstallmentController::class, 'show'])->name('installments.show');

        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('/payments/midtrans/finish', [PaymentController::class, 'midtransFinish'])->name('payments.midtrans.finish');
        Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
        Route::post('/payments/{payment}/midtrans/refresh', [PaymentController::class, 'refreshMidtransStatus'])->name('payments.midtrans.refresh');
        Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');

        Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
        Route::get('/deliveries/{delivery}', [DeliveryController::class, 'show'])->name('deliveries.show');

        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');

        Route::get('/profile', [UserProfileController::class, 'index'])->name('profile.index');
        Route::put('/profile', [UserProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [UserProfileController::class, 'updatePassword'])->name('profile.password');
        Route::post('/addresses', [UserProfileController::class, 'storeAddress'])->name('addresses.store');
        Route::put('/addresses/{address}', [UserProfileController::class, 'updateAddress'])->name('addresses.update');
        Route::delete('/addresses/{address}', [UserProfileController::class, 'destroyAddress'])->name('addresses.destroy');
    });
});

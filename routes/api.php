<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\MidtransWebhookController;
use App\Http\Controllers\Api\PublicApiController;
use App\Http\Controllers\Api\UserApplicationApiController;
use App\Http\Controllers\Api\UserDashboardApiController;
use App\Http\Controllers\Api\UserDeliveryApiController;
use App\Http\Controllers\Api\UserInstallmentApiController;
use App\Http\Controllers\Api\UserNotificationApiController;
use App\Http\Controllers\Api\UserPaymentApiController;
use App\Http\Controllers\Api\UserProfileApiController;
use Illuminate\Support\Facades\Route;

Route::post('/payments/midtrans/notification', MidtransWebhookController::class)->name('api.payments.midtrans.notification');

Route::prefix('public')->name('api.public.')->group(function () {
    Route::get('/motors', [PublicApiController::class, 'motors'])->name('motors.index');
    Route::get('/motors/featured', [PublicApiController::class, 'featuredMotors'])->name('motors.featured');
    Route::get('/motors/{motor}', [PublicApiController::class, 'showMotor'])->name('motors.show');
    Route::get('/installment-options', [PublicApiController::class, 'installmentOptions'])->name('installment-options');
    Route::get('/insurance-options', [PublicApiController::class, 'insuranceOptions'])->name('insurance-options');
    Route::post('/simulation', [PublicApiController::class, 'simulation'])->name('simulation');
    Route::get('/faqs', [PublicApiController::class, 'faqs'])->name('faqs');
    Route::post('/contact', [PublicApiController::class, 'contact'])->name('contact');
});

Route::prefix('auth')->name('api.auth.')->group(function () {
    Route::post('/register', [AuthApiController::class, 'register'])->name('register');
    Route::post('/login', [AuthApiController::class, 'login'])->name('login');
    Route::post('/logout', [AuthApiController::class, 'logout'])->name('logout')->middleware('auth');
    Route::post('/forgot-password', [AuthApiController::class, 'forgotPassword'])->name('forgot-password');
    Route::post('/reset-password', [AuthApiController::class, 'resetPassword'])->name('reset-password');
});

Route::middleware('auth')->prefix('user')->name('api.user.')->group(function () {
    Route::get('/dashboard', [UserDashboardApiController::class, 'index'])->name('dashboard');

    Route::get('/profile', [UserProfileApiController::class, 'show'])->name('profile.show');
    Route::put('/profile', [UserProfileApiController::class, 'update'])->name('profile.update');
    Route::put('/password', [UserProfileApiController::class, 'updatePassword'])->name('profile.password');
    Route::get('/addresses', [UserProfileApiController::class, 'addresses'])->name('addresses.index');
    Route::post('/addresses', [UserProfileApiController::class, 'storeAddress'])->name('addresses.store');
    Route::put('/addresses/{address}', [UserProfileApiController::class, 'updateAddress'])->name('addresses.update');
    Route::delete('/addresses/{address}', [UserProfileApiController::class, 'destroyAddress'])->name('addresses.destroy');

    Route::get('/applications', [UserApplicationApiController::class, 'index'])->name('applications.index');
    Route::post('/applications', [UserApplicationApiController::class, 'store'])->name('applications.store');
    Route::get('/applications/{application}', [UserApplicationApiController::class, 'show'])->name('applications.show');
    Route::put('/applications/{application}', [UserApplicationApiController::class, 'update'])->name('applications.update');
    Route::post('/applications/{application}/submit', [UserApplicationApiController::class, 'submit'])->name('applications.submit');
    Route::post('/applications/{application}/cancel', [UserApplicationApiController::class, 'cancel'])->name('applications.cancel');
    Route::get('/applications/{application}/logs', [UserApplicationApiController::class, 'logs'])->name('applications.logs');
    Route::post('/applications/{application}/documents', [UserApplicationApiController::class, 'storeDocuments'])->name('applications.documents.store');
    Route::get('/applications/{application}/documents', [UserApplicationApiController::class, 'documents'])->name('applications.documents.index');
    Route::delete('/documents/{document}', [UserApplicationApiController::class, 'destroyDocument'])->name('documents.destroy');

    Route::get('/installments', [UserInstallmentApiController::class, 'index'])->name('installments.index');
    Route::get('/installments/{installment}', [UserInstallmentApiController::class, 'show'])->name('installments.show');

    Route::get('/payments', [UserPaymentApiController::class, 'index'])->name('payments.index');
    Route::post('/payments', [UserPaymentApiController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}', [UserPaymentApiController::class, 'show'])->name('payments.show');

    Route::get('/deliveries', [UserDeliveryApiController::class, 'index'])->name('deliveries.index');
    Route::get('/deliveries/{delivery}', [UserDeliveryApiController::class, 'show'])->name('deliveries.show');

    Route::get('/notifications', [UserNotificationApiController::class, 'index'])->name('notifications.index');
    Route::put('/notifications/{notification}/read', [UserNotificationApiController::class, 'read'])->name('notifications.read');
    Route::put('/notifications/read-all', [UserNotificationApiController::class, 'readAll'])->name('notifications.read-all');
});

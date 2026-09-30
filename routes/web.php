<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\GeneralSettingsController;
use App\Http\Controllers\Admin\PricingSettingsController;
use App\Http\Controllers\Admin\PvcOrderController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PvcCardPrintController;
use App\Http\Controllers\PhotoPrintController;
use App\Http\Controllers\UserOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('frontend.index');
})->name('home');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');

Route::middleware('guest')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.authenticate');
    Route::post('/admin/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('admin.login.authenticate');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register')->name('register.store');
});

Route::middleware(['auth', 'account.active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'user'])->middleware('role:user')->name('dashboard');
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->middleware('role:admin')->name('admin.dashboard');

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/general-settings', [GeneralSettingsController::class, 'edit'])->name('general-settings.edit');
        Route::put('/general-settings', [GeneralSettingsController::class, 'update'])->name('general-settings.update');
        Route::get('/pricing', [PricingSettingsController::class, 'edit'])->name('pricing.edit');
        Route::put('/pricing', [PricingSettingsController::class, 'update'])->name('pricing.update');
        Route::get('/pvc-orders', [PvcOrderController::class, 'index'])->name('pvc-orders.index');
        Route::get('/pvc-orders/{orderId}/files/{fileId}', [PvcOrderController::class, 'downloadFile'])->whereNumber('orderId')->whereNumber('fileId')->name('pvc-orders.files.download');
        Route::patch('/pvc-orders/{orderId}/status', [PvcOrderController::class, 'updateStatus'])->whereNumber('orderId')->name('pvc-orders.status');
        Route::get('/pvc-orders/{orderId}', [PvcOrderController::class, 'show'])->whereNumber('orderId')->name('pvc-orders.show');
        Route::get('/photo-orders', [PvcOrderController::class, 'photoIndex'])->name('photo-orders.index');
        Route::get('/photo-orders/{orderId}/files/{fileId}', [PvcOrderController::class, 'downloadPhotoFile'])->whereNumber('orderId')->whereNumber('fileId')->name('photo-orders.files.download');
        Route::patch('/photo-orders/{orderId}/status', [PvcOrderController::class, 'updatePhotoStatus'])->whereNumber('orderId')->name('photo-orders.status');
        Route::get('/photo-orders/{orderId}', [PvcOrderController::class, 'photoShow'])->whereNumber('orderId')->name('photo-orders.show');
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/{userId}', [UserManagementController::class, 'show'])->whereNumber('userId')->name('users.show');
        Route::patch('/users/{userId}/status', [UserManagementController::class, 'updateStatus'])->whereNumber('userId')->name('users.status');
        Route::get('/security', [AuthController::class, 'showAdminSecurity'])->name('security');
        Route::post('/security/password', [AuthController::class, 'updateAdminPassword'])->name('security.password');
    });

    Route::middleware('role:user')->group(function () {
        Route::get('/pvc-card-print', [PvcCardPrintController::class, 'show'])->name('user.pvc-card-print');
        Route::post('/pvc-card-print', [PvcCardPrintController::class, 'submit'])->name('user.pvc-card-print.submit');
        Route::get('/orders/{orderId}', [UserOrderController::class, 'show'])->whereNumber('orderId')->name('user.orders.show');
        Route::get('/orders/{orderId}/files/{fileId}', [UserOrderController::class, 'downloadFile'])->whereNumber('orderId')->whereNumber('fileId')->name('user.orders.files.download');
        Route::get('/photo-print', [PhotoPrintController::class, 'show'])->name('user.photo-print');
        Route::post('/photo-print', [PhotoPrintController::class, 'submit'])->name('user.photo-print.submit');
        Route::view('/recover-failed-order', 'user-panel.recover-failed-order')->name('user.recover-failed-order');
        Route::get('/order-history', [UserOrderController::class, 'index'])->name('user.order-history');
        Route::get('/track-help', [UserOrderController::class, 'trackHelp'])->name('user.track-help');
        Route::get('/security', [AuthController::class, 'showUserSecurity'])->name('user.security');
        Route::post('/security/password', [AuthController::class, 'updateUserPassword'])->name('user.security.password');
    });
});

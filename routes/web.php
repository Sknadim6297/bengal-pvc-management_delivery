<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\DashboardController;
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

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'user'])->middleware('role:user')->name('dashboard');
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->middleware('role:admin')->name('admin.dashboard');

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/{userId}', [UserManagementController::class, 'show'])->whereNumber('userId')->name('users.show');
        Route::patch('/users/{userId}/status', [UserManagementController::class, 'updateStatus'])->whereNumber('userId')->name('users.status');
    });

    Route::middleware('role:user')->group(function () {
        Route::view('/pvc-card-print', 'user-panel.pvc-card-print')->name('user.pvc-card-print');
        Route::view('/photo-print', 'user-panel.photo-print')->name('user.photo-print');
        Route::view('/recover-failed-order', 'user-panel.recover-failed-order')->name('user.recover-failed-order');
        Route::view('/order-history', 'user-panel.order-history')->name('user.order-history');
        Route::view('/track-help', 'user-panel.track-help')->name('user.track-help');
        Route::view('/security', 'user-panel.security')->name('user.security');
    });
});

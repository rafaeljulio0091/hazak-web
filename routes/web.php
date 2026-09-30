<?php

use App\Enums\UserRole;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Support\DeviceId;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Landing', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::middleware('role:'.UserRole::Admin->value)->group(function () {
        Route::inertia('/admin/dashboard', 'Dashboard')->name('admin.dashboard');
    });

    Route::middleware('role:'.UserRole::User->value)->prefix('my')->name('my.')->group(function () {
        Route::get('/dashboard', UserDashboardController::class)->name('dashboard');
        Route::get('/devices/{deviceId}', [TrackingController::class, 'show'])
            ->where('deviceId', DeviceId::PATTERN)
            ->name('devices.show');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/tracking/{deviceId}', [TrackingController::class, 'show'])
        ->middleware('role:'.UserRole::Admin->value)
        ->where('deviceId', DeviceId::PATTERN)
        ->name('tracking.show');
    Route::post('/tracking/token', [TrackingController::class, 'token'])->name('tracking.token');
});

require __DIR__.'/auth.php';

<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Employee\DashboardController;
use App\Http\Controllers\Employee\RegistrationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Employee\WorkshopRegistrationController;

// Redirect root -> login
Route::get('/', fn() => redirect()->route('login'));

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('workshops', \App\Http\Controllers\Admin\WorkshopController::class);
});

// Employee routes
Route::middleware(['auth', \App\Http\Middleware\EnsureEmployee::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
   Route::post('/workshops/{workshop}/register', [WorkshopRegistrationController::class, 'store'])->name('workshops.register');
    Route::delete('/workshops/{workshop}/register', [RegistrationController::class, 'destroy'])->name('workshops.unregister');
});

// Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
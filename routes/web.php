<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Employee leave routes
    Route::middleware('role:employee')->group(function () {
        Route::get('/leave', [LeaveController::class, 'index'])->name('leave.index');
        Route::get('/leave/create', [LeaveController::class, 'create'])->name('leave.create');
        Route::post('/leave', [LeaveController::class, 'store'])->name('leave.store');
        Route::delete('/leave/{leave}', [LeaveController::class, 'destroy'])->name('leave.destroy');
    });
});

require __DIR__.'/auth.php';

<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\Admin\LeaveController as AdminLeaveController;
use App\Http\Controllers\Api\Admin\LeaveTypeController as AdminLeaveTypeController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::post('/register',        [AuthController::class, 'register']);
Route::post('/auth/provision',  [AuthController::class, 'registerAdmin'])->middleware('throttle:3,1');
Route::post('/login',           [AuthController::class, 'login']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Employee routes
    Route::middleware('role:employee')->group(function () {
        Route::get('/leave-types',   [LeaveController::class, 'leaveTypes']);
        Route::get('/leaves',        [LeaveController::class, 'index']);
        Route::post('/leaves',       [LeaveController::class, 'store']);
        Route::get('/leaves/{leave}', [LeaveController::class, 'show']);
        Route::delete('/leaves/{leave}', [LeaveController::class, 'destroy']);
    });

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/leaves',                    [AdminLeaveController::class, 'index']);
        Route::get('/leaves/{leave}',            [AdminLeaveController::class, 'show']);
        Route::post('/leaves/{leave}/approve',   [AdminLeaveController::class, 'approve']);
        Route::post('/leaves/{leave}/reject',    [AdminLeaveController::class, 'reject']);

        Route::get('/leave-types',               [AdminLeaveTypeController::class, 'index']);
        Route::post('/leave-types',              [AdminLeaveTypeController::class, 'store']);
        Route::put('/leave-types/{leaveType}',   [AdminLeaveTypeController::class, 'update']);
        Route::delete('/leave-types/{leaveType}',[AdminLeaveTypeController::class, 'destroy']);
    });

});

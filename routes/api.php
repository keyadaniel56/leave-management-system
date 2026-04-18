<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\Admin\LeaveController as AdminLeaveController;
use App\Http\Controllers\Api\Admin\LeaveTypeController as AdminLeaveTypeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

// Public auth routes
Route::post('/register',        [AuthController::class, 'register']);
Route::post('/auth/provision',  [AuthController::class, 'registerAdmin'])->middleware('throttle:3,1');
Route::post('/login',           [AuthController::class, 'login']);

// Password reset
Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink($request->only('email'));

    return $status === Password::RESET_LINK_SENT
        ? response()->json(['status' => 'success', 'message' => 'Password reset link sent to your email.'])
        : response()->json(['status' => 'error', 'message' => 'Unable to send reset link. Check the email address.'], 422);
})->middleware('throttle:5,1')->name('password.email');

Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'token'    => 'required',
        'email'    => 'required|email',
        'password' => 'required|confirmed|min:8',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password'       => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        }
    );

    return $status === Password::PASSWORD_RESET
        ? response()->json(['status' => 'success', 'message' => 'Password reset successfully.'])
        : response()->json(['status' => 'error', 'message' => 'Invalid or expired reset token.'], 422);
})->middleware('throttle:5,1')->name('password.reset');

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

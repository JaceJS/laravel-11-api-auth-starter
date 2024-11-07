<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::middleware(['guest'])->group(function () {
        Route::post('/password/forgot', [AuthController::class, 'sendResetLinkEmail'])->name('password.forgot');
        Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('password.reset');
    });

    Route::middleware(['auth:api'])->group(function () {
        Route::post('/email/resend', [AuthController::class, 'resendEmailVerification'])
            ->middleware(['throttle:6,1'])->name('verification.send');

        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });
});

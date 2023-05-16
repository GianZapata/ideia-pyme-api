<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationEmailController;
use App\Http\Controllers\Client\ClientController;
use App\Http\Controllers\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/** api/auth */
Route::group(['prefix' => 'auth'], function () {
    /** Auth */
    Route::post('/login', [AuthController::class, 'login'])
        ->name('auth.login');

    Route::post('/signup', [AuthController::class, 'signup'])
        ->name('auth.signup');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('auth.logout')
        ->middleware('auth:sanctum');

    /** Email */
    Route::post('/verify-email', [VerificationEmailController::class, 'verify'])
        ->name('verification.verify');

    Route::post('/resend-verification-email', [VerificationEmailController::class, 'resend'])
        ->middleware('auth:sanctum')
        ->name('verification.resend');

    /** Password */
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])
        ->name('password.reset');

    Route::get('/reset-password/validate-token/{token}', [ResetPasswordController::class, 'validateToken'])
        ->name('password.validateToken');

    Route::put('/reset-password', [ResetPasswordController::class, 'reset'])
        ->name('password.update');
});

/** api/users */
Route::group(['prefix' => 'users'], function() {
    Route::post('/check-email', [UserController::class, 'checkEmail'])->name('users.checkEmail');
});

/** Bearer ${token} */
Route::group(['middleware' => 'auth:sanctum'], function () {

    /** api/check-auth */
    Route::get('/check-auth', function () {
        return response()->json(['message' => 'Autenticado'], 200);
    })->name('auth.check');

    /** api/profile */
    Route::get('/profile', function( Request $request ){
        return response()->json(['user' => $request->user() ], 200);
    })->name('profile');

    /** api/clients */
    Route::group(['prefix' => 'clients'], function () {
        Route::get('/', [ClientController::class, 'getAll'])
            ->name('clients.getAll');

        Route::get('/{id}', [ClientController::class, 'getById'])
            ->name('clients.getById');

        Route::post('/', [ClientController::class, 'store'])
            ->name('clients.store');

        Route::put('/{id}', [ClientController::class, 'update'])
            ->name('clients.update');

    });
});

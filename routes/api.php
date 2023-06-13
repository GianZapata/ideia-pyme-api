<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationEmailController;
use App\Http\Controllers\Client\ClientController;
use App\Http\Controllers\Reports\Empresa\SituacionLaboral\ComportamientoHistoricoNominaController;
use App\Http\Controllers\Reports\Empresa\SituacionLaboral\DistribucionNominaController;
use App\Http\Controllers\Reports\SituacionJuridicaController;
use App\Http\Controllers\Reports\SituacionLaboralEmpresaController;
use App\Http\Controllers\Reports\ClientsController;
use App\Http\Controllers\Reports\SuppliersController;
use App\Http\Controllers\Reports\Accionistas\SearchAccionistasController;
use App\Http\Controllers\SatReport\SatReportController;
use App\Http\Controllers\User\UserController;
use App\Models\Comprobante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;

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
Route::group(['prefix' => 'users'], function () {
    Route::post('/check-email', [UserController::class, 'checkEmail'])->name('users.checkEmail');
});

/** Bearer ${token} */
Route::group(['middleware' => 'auth:sanctum'], function () {

    /** api/check-auth */
    Route::get('/check-auth', function () {
        return response()->json(['message' => 'Autenticado'], 200);
    })->name('auth.check');

    /** api/profile */
    Route::get('/profile', function (Request $request) {
        return response()->json(['user' => $request->user()], 200);
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


Route::group(['prefix' => 'reports'], function (){

    Route::get('/situacion-laboral-empresa',[SituacionLaboralEmpresaController::class, 'getSituacionLaboralEmpresa'])
        ->name('reports.situacionLaboralEmpresa');


    Route::get('/situacion-juridica',[SituacionJuridicaController::class, 'getSituacionJuridica'])
        ->name('reports.situacionJuridica');

    Route::prefix('empresa')->group( function (){
        Route::prefix('situacion-laboral')->group( function (){
            Route::get('/comportamiento-historico-nomina', [ComportamientoHistoricoNominaController::class, 'getComportamientoHistoricoNomina'])
                ->name('reports.empresa.situacionLaboral.comportamientoHistoricoNomina');

            Route::get('/distribucion-nomina', [DistribucionNominaController::class, 'getDistribucionNomina'])
                ->name('reports.empresa.situacionLaboral.getDistribucionNomina');
        });
    });

    Route::prefix('clientes')->group( function(){

        Route::get('/top5clientes', [ClientsController::class, 'obtenerTop5Clientes'])
        ->name('reports.clientes.top5Clientes');

        Route::get('/diversificacionClientes', [ClientsController::class, 'obtenerDiversificacionClientes'])
        ->name('reports.clientes.diversificacionClientes');

    });

    Route::prefix('proveedores')->group( function() {

        Route::get('/top5proveedores', [SuppliersController::class, 'obtenerTop5Proveedores'])
        ->name('reports.proveedores.top5proveedores');

        Route::get('/diversificacionProveedores', [SuppliersController::class, 'obtenerDiversificacionProveedores'])
        ->name('reports.proveedores.diversificacionProveedores');

    });

    Route::prefix('facturacion')->group( function () {

        Route::get('/emitidos', [EmitidosController::class, 'getEmitidos'])
            ->name('reports.facturacion.emitidos');

        Route::get('/recibidos', [RecibidosController::class, 'getRecibidos'])
            ->name('reports.facturacion.recibidos');

        Route::get('/ingresos-egresos', [IngresosEgresosController::class, 'getIngresosEgresos'])
            ->name('reports.facturacion.ingresosEgresos');
    });

    Route::prefix('/accionistas')->group( function () {
        Route::post('/verify', [SearchAccionistasController::class, 'verify'])
            ->name('reports.accionistas.verify');
    });

});

Route::group(['prefix' => 'sat-reports'], function () {
    Route::get('/', [SatReportController::class, 'index'])
        ->name('sat-reports.index');
    Route::post('/new', [SatReportController::class, 'store'])
        ->name('sat-reports.store');
    Route::post('/credentials', [SatReportCredentialsController::class, 'store'])
        ->name('sat-reports.storeCredentials');
});

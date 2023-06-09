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
    });

    Route::prefix('facturacion')->group( function () {
        Route::get('/ingresos-egresos', function ( Request $request ) {
            $rfc =  $request->rfc;

            if( !$rfc ) {
                return response()->json(['message' => 'El RFC es requerido'], 400);
            }

            $comprobantes = Comprobante::query()
                ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
                ->join('emisores', 'facturas.emisor_id', '=', 'emisores.id')
                ->select(
                    DB::raw('YEAR(comprobantes.fecha) AS year'),
                    DB::raw('MONTH(comprobantes.fecha) AS month'),
                    DB::raw('SUM(comprobantes.total) AS total'),
                    DB::raw('comprobantes.tipo_comprobante AS tipo')
                )
                ->whereIn('comprobantes.tipo_comprobante', ['I', 'E'])
                ->where('emisores.rfc', $rfc)
                ->groupBy(DB::raw('YEAR(comprobantes.fecha)'), DB::raw('MONTH(comprobantes.fecha)'), 'comprobantes.tipo_comprobante')
                ->get();

            $facturas = DB::table('comprobantes')
                ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
                ->join('emisores', 'facturas.emisor_id', '=', 'emisores.id')
                ->select(
                    DB::raw('YEAR(comprobantes.fecha) AS year'),
                    DB::raw('SUM(CASE WHEN facturas.tipo = "emitidos" THEN comprobantes.total ELSE 0 END) AS totalEmitidos'),
                    DB::raw('SUM(CASE WHEN facturas.tipo = "recibidos" THEN comprobantes.total ELSE 0 END) AS totalRecibidos')
                )
                ->whereIn('facturas.tipo', ['emitidos', 'recibidos'])
                ->where('emisores.rfc', $rfc)
                ->groupBy(DB::raw('YEAR(comprobantes.fecha)'))
                ->get();


            $groupedData = [];

            foreach ($comprobantes as $comprobante) {
                $year = $comprobante->year;
                $month = Carbon::createFromDate($comprobante->year, $comprobante->month, 1)->locale('es_ES')->isoFormat('MMMM');
                $tipo = $comprobante->tipo;

                if (!isset($groupedData[$year])) {
                    $groupedData[$year] = [
                        'year' => $year,
                        'totalIngresos' => 0,
                        'totalEgresos' => 0,
                        'desgloseIngresos' => array_fill(0, 12, ['total' => 0, 'month' => '']),
                        'desgloseEgresos' => array_fill(0, 12, ['total' => 0, 'month' => ''])
                    ];

                    // Asignar los nombres de los meses
                    for ($i = 1; $i <= 12; $i++) {
                        $monthName = Carbon::createFromDate($year, $i, 1)->locale('es_ES')->isoFormat('MMM');
                        $groupedData[$year]['desgloseIngresos'][$i - 1]['month'] = $monthName;
                        $groupedData[$year]['desgloseEgresos'][$i - 1]['month'] = $monthName;
                    }
                }

                if ($tipo === 'I') {
                    $groupedData[$year]['totalIngresos'] += $comprobante->total;
                    $groupedData[$year]['desgloseIngresos'][$comprobante->month - 1]['total'] = floatval($comprobante->total);
                } else if ($tipo === 'E') {
                    $groupedData[$year]['totalEgresos'] += $comprobante->total;
                    $groupedData[$year]['desgloseEgresos'][$comprobante->month - 1]['total'] = floatval($comprobante->total);
                }
            }

            uksort($groupedData, function ($a, $b) {
                return $b - $a;
            });

            foreach ($groupedData as &$yearData) {
                $yearData['totalEmitidos'] = 0;
                $yearData['totalRecibidos'] = 0;
            }

            foreach ($facturas as $factura) {
                $year = $factura->year;

                $totalEmitidos = $factura->totalEmitidos;
                $totalRecibidos = $factura->totalRecibidos;

                $groupedData[$year]['totalEmitidos'] = floatval($totalEmitidos);
                $groupedData[$year]['totalRecibidos'] = floatval($totalRecibidos);

            }


            return response()->json([
                'periodos' => array_values($groupedData)
            ], 200);
        });
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

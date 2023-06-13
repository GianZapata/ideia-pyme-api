<?php

namespace App\Http\Controllers\Reports\Facturacion\CashFlowFiscal;

use App\Http\Controllers\Controller;
use App\Models\Comprobante;
use App\Services\EgresoService;
use App\Services\IngresoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashFlowFiscalController extends Controller
{

    protected $ingresoService;
    protected $egresoService;


    public function __construct(IngresoService $ingresoService, EgresoService $egresoService)
    {
        $this->ingresoService = $ingresoService;
        $this->egresoService = $egresoService;
    }

    public function getCashFlowFiscal( Request $request ) {
        $rfc =  $request->rfc;

        if( !$rfc ) return response()->json(['message' => 'El RFC es requerido'], 400);

        $ingresos = $this->ingresoService->queryIngresos( $rfc );
        $egresos = $this->egresoService->queryEgresos( $rfc );

        $data = [];

        // Procesar los ingresos
        foreach ($ingresos as $ingreso) {
            $year = $ingreso->year;
            $month = str_pad($ingreso->month, 2, "0", STR_PAD_LEFT);
            $fechaCarbon = Carbon::createFromDate($year, $month, 1)->locale('es_ES');
            $fecha = $fechaCarbon->format('M-y');

            if (!isset($data[$fecha])) {
                $data[$fecha] = ['fecha' => $fecha, 'ingresos' => 0, 'egresos' => 0];
            }

            $data[$fecha]['ingresos'] = floatval($ingreso->ingresoTotal);
        }

        // Procesar los egresos
        foreach ($egresos as $egreso) {
            $year = $egreso->year;
            $month = str_pad($egreso->month, 2, "0", STR_PAD_LEFT);
            $fechaCarbon = Carbon::createFromDate($year, $month, 1)->locale('es_ES');
            $fecha = $fechaCarbon->format('M-y');

            if (!isset($data[$fecha])) {
                $data[$fecha] = ['fecha' => $fecha, 'ingresos' => 0, 'egresos' => 0];
            }

            $data[$fecha]['egresos'] = floatval($egreso->egresoTotal) * -1;
        }

        return response()->json([
            'cashFlowData' => array_values($data),
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\Reports\Empresa\SituacionLaboral;

use App\Http\Controllers\Controller;
use App\Models\Comprobante;
use App\Services\DistribucionNominaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DistribucionNominaController extends Controller
{

    protected $nominaService;

    public function __construct(DistribucionNominaService $nominaService)
    {
        $this->nominaService = $nominaService;
    }

    public function getDistribucionNomina(Request $request)
    {
        $rfc =  $request->rfc;

        if( !$rfc ) return response()->json(['message' => 'El RFC es requerido'], 400);

        $nominaColaboradores = $this->nominaService->obtenerNominaAccionistasORepresentantes( $rfc );
        $totalNominaGeneral = $nominaColaboradores->sum('total_nomina');
        $nominaColaboradoresVsTotal = $this->nominaService->calcularNominaColaboradoresVsTotal( $rfc );

        return response()->json([
            'nominaColaboradoresVsTotal' => $nominaColaboradoresVsTotal,
            'totalNomina' => $totalNominaGeneral,
        ]);
    }

}

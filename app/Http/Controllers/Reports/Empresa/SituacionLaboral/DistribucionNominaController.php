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
        $rfc = "KFM131016RJ1";
        $nominaColaboradores = $this->nominaService->obtenerNominaColaboradores( $rfc );
        $totalNominaGeneral = $nominaColaboradores->sum('total_nomina');
        $nominaColaboradoresVsTotal = $this->nominaService->calcularNominaColaboradoresVsTotal( $rfc );

        return response()->json([
            'nominaColaboradoresVsTotal' => $nominaColaboradoresVsTotal,
            'totalNomina' => $totalNominaGeneral,
        ]);
    }

}

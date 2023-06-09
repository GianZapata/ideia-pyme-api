<?php

namespace App\Http\Controllers\Reports\Empresa\SituacionLaboral;

use App\Http\Controllers\Controller;
use App\Models\Comprobante;
use App\Services\ComportamientoHistoricoNominaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComportamientoHistoricoNominaController extends Controller
{
    protected $comportamientoHistoricoNominaService;

    public function __construct(ComportamientoHistoricoNominaService $comportamientoHistoricoNominaService)
    {
        $this->comportamientoHistoricoNominaService = $comportamientoHistoricoNominaService;
    }


    public function getComportamientoHistoricoNomina( Request $request ){

        $rangosSueldo = $this->comportamientoHistoricoNominaService->obtenerRangosSueldo();
        $empleadosPorMes = $this->comportamientoHistoricoNominaService->obtenerEmpleadosPorMes();
        $altasYBajas = $this->comportamientoHistoricoNominaService->calcularAltasYBajas($empleadosPorMes);
        $empleadosActivos = $this->comportamientoHistoricoNominaService->obtenerEmpleadosActivos();
        $totalEmpleadosActivos = $empleadosActivos->count();
        $nominaPromedio = $this->comportamientoHistoricoNominaService->calcularNominaPromedio();

        return response()->json([
            'rangosSueldo' => $rangosSueldo,
            'altasYBajas' => $altasYBajas,
            'totalEmpleadosActivos' => $totalEmpleadosActivos,
            'nominaPromedio' => $nominaPromedio,
        ]);

    }
}

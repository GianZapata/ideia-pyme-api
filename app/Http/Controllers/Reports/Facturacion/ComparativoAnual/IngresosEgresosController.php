<?php

namespace App\Http\Controllers\Reports\Facturacion\ComparativoAnual;

use App\Http\Controllers\Controller;
use App\Models\Comprobante;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IngresosEgresosController extends Controller
{
    public function getIngresosEgresos( Request $request ) {
        $rfc =  $request->rfc;

        if( !$rfc ) {
            return response()->json(['message' => 'El RFC es requerido'], 400);
        }

        $ingresos = Comprobante::query()
            ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
            ->leftJoin('emisores', 'facturas.emisor_id', '=', 'emisores.id')
            ->leftJoin('receptores', 'facturas.receptor_id', '=', 'receptores.id')
            ->select(
                DB::raw('YEAR(comprobantes.fecha) AS year'),
                DB::raw('MONTH(comprobantes.fecha) AS month'),
                DB::raw('SUM(comprobantes.total) AS total'),
            )
            ->where('comprobantes.tipo_comprobante', 'I')
            ->where(function ($query) use ($rfc) {
                $query->where('emisores.rfc', $rfc)
                    ->orWhere('receptores.rfc', $rfc);
            })
            ->groupBy(
                DB::raw('YEAR(comprobantes.fecha)'),
                DB::raw('MONTH(comprobantes.fecha)'),
            )
            ->orderByDesc(
                DB::raw('YEAR(comprobantes.fecha)')
            )->get();

        $egresos = Comprobante::query()
            ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
            ->leftJoin('emisores', 'facturas.emisor_id', '=', 'emisores.id')
            ->leftJoin('receptores', 'facturas.receptor_id', '=', 'receptores.id')
            ->select(
                DB::raw('YEAR(comprobantes.fecha) AS year'),
                DB::raw('MONTH(comprobantes.fecha) AS month'),
                DB::raw('SUM(comprobantes.total) AS total'),
            )
            ->where('comprobantes.tipo_comprobante', 'E')
            ->where(function ($query) use ($rfc) {
                $query->where('emisores.rfc', $rfc)
                    ->orWhere('receptores.rfc', $rfc);
            })
            ->groupBy(
                DB::raw('YEAR(comprobantes.fecha)'),
                DB::raw('MONTH(comprobantes.fecha)'),
            )
            ->orderByDesc(
                DB::raw('YEAR(comprobantes.fecha)')
            )->get();

        $emitidos = Comprobante::query()
            ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
            ->join('emisores', 'facturas.emisor_id', '=', 'emisores.id')
            ->select(
                DB::raw('YEAR(comprobantes.fecha) AS year'),
                DB::raw('MONTH(comprobantes.fecha) AS month'),
                DB::raw('SUM(comprobantes.total) AS total'),
            )
            ->where('emisores.rfc', $rfc)
            ->where('facturas.tipo', "emitidos")
            ->groupBy(
                DB::raw('YEAR(comprobantes.fecha)'),
                DB::raw('MONTH(comprobantes.fecha)'),
            )
            ->orderByDesc(
                DB::raw('YEAR(comprobantes.fecha)')
            )->get();

        $recibidos = Comprobante::query()
            ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
            ->join('receptores', 'facturas.receptor_id', '=', 'receptores.id')
            ->select(
                DB::raw('YEAR(comprobantes.fecha) AS year'),
                DB::raw('MONTH(comprobantes.fecha) AS month'),
                DB::raw('SUM(comprobantes.total) AS total'),
            )
            ->where('facturas.tipo', "recibidos")
            ->where('receptores.rfc', $rfc)
            ->groupBy(
                DB::raw('YEAR(comprobantes.fecha)'),
                DB::raw('MONTH(comprobantes.fecha)'),
            )
            ->orderByDesc(
                DB::raw('YEAR(comprobantes.fecha)')
            )->get();

        $groupedData = [];

        // Procesa ingresos
        foreach ($ingresos as $ingreso) {
            $year = $ingreso->year;
            $month = $ingreso->month - 1; // arrays empiezan en indice 0
            $total = $ingreso->total;

            if (!isset($groupedData[$year])) {
                // Inicializa estructura de datos para el año
                $groupedData[$year] = [
                    'year' => $year,
                    'totalIngresos' => 0,
                    'totalEgresos' => 0,
                    'totalEmitidos' => 0,
                    'totalRecibidos' => 0,
                    'desgloseIngresos' => array_fill(0, 12, ['total' => 0]),
                    'desgloseEgresos' => array_fill(0, 12, ['total' => 0]),
                    'desgloseEmitidos' => array_fill(0, 12, ['total' => 0]), // Agregado
                    'desgloseRecibidos' => array_fill(0, 12, ['total' => 0]), // Agregado
                ];
                // Asigna los nombres de los meses
                for ($i = 1; $i <= 12; $i++) {
                    $monthName = Carbon::createFromDate($year, $i, 1)->locale('es_ES')->isoFormat('MMM');
                    $groupedData[$year]['desgloseIngresos'][$i - 1]['month'] = $monthName;
                    $groupedData[$year]['desgloseEgresos'][$i - 1]['month'] = $monthName;
                    $groupedData[$year]['desgloseEmitidos'][$i - 1]['month'] = $monthName; // Agregado
                    $groupedData[$year]['desgloseRecibidos'][$i - 1]['month'] = $monthName; // Agregado
                }
            }

            $groupedData[$year]['totalIngresos'] += $total;
            $groupedData[$year]['desgloseIngresos'][$month]['total'] += $total;
        }

        // Procesa egresos
        foreach ($egresos as $egreso) {
            $year = $egreso->year;
            $month = $egreso->month - 1;
            $total = $egreso->total;

            if (!isset($groupedData[$year])) {
                // Inicializa estructura de datos para el año si no existe
                $groupedData[$year] = [
                    'totalIngresos' => 0,
                    'totalEgresos' => 0,
                    'totalEmitidos' => 0,
                    'totalRecibidos' => 0,
                    'desgloseIngresos' => array_fill(0, 12, ['total' => 0]),
                    'desgloseEgresos' => array_fill(0, 12, ['total' => 0]),
                    'desgloseEmitidos' => array_fill(0, 12, ['total' => 0]), // Agregado
                    'desgloseRecibidos' => array_fill(0, 12, ['total' => 0]), // Agregado
                ];

                // Asigna los nombres de los meses
                for ($i = 1; $i <= 12; $i++) {
                    $monthName = Carbon::createFromDate($year, $i, 1)->locale('es_ES')->isoFormat('MMM');
                    $groupedData[$year]['desgloseIngresos'][$i - 1]['month'] = $monthName;
                    $groupedData[$year]['desgloseEgresos'][$i - 1]['month'] = $monthName;
                    $groupedData[$year]['desgloseEmitidos'][$i - 1]['month'] = $monthName; // Agregado
                    $groupedData[$year]['desgloseRecibidos'][$i - 1]['month'] = $monthName; // Agregado
                }
            }


            $groupedData[$year]['totalEgresos'] += $total;
            $groupedData[$year]['desgloseEgresos'][$month]['total'] += $total;
        }

        // Procesa emitidos
        foreach ($emitidos as $emitido) {
            $year = $emitido->year;
            $month = $emitido->month - 1;
            $total = $emitido->total;

            if (!isset($groupedData[$year])) {
                // Inicializa estructura de datos para el año si no existe
                $groupedData[$year] = [
                    'totalIngresos' => 0,
                    'totalEgresos' => 0,
                    'totalEmitidos' => 0,
                    'totalRecibidos' => 0,
                    'desgloseIngresos' => array_fill(0, 12, ['total' => 0]),
                    'desgloseEgresos' => array_fill(0, 12, ['total' => 0]),
                    'desgloseEmitidos' => array_fill(0, 12, ['total' => 0]), // Agregado
                    'desgloseRecibidos' => array_fill(0, 12, ['total' => 0]), // Agregado
                ];

                // Asigna los nombres de los meses
                for ($i = 1; $i <= 12; $i++) {
                    $monthName = Carbon::createFromDate($year, $i, 1)->locale('es_ES')->isoFormat('MMM');
                    $groupedData[$year]['desgloseIngresos'][$i - 1]['month'] = $monthName;
                    $groupedData[$year]['desgloseEgresos'][$i - 1]['month'] = $monthName;
                    $groupedData[$year]['desgloseEmitidos'][$i - 1]['month'] = $monthName; // Agregado
                    $groupedData[$year]['desgloseRecibidos'][$i - 1]['month'] = $monthName; // Agregado
                }
            }

            $groupedData[$year]['totalEmitidos'] += $total;
            $groupedData[$year]['desgloseEmitidos'][$month]['total'] += $total; // Desglose
        }

        // Procesa recibidos
        foreach ($recibidos as $recibido) {
            $year = $recibido->year;
            $month = $recibido->month - 1;
            $total = $recibido->total;

            if (!isset($groupedData[$year])) {
                $groupedData[$year] = [
                    'totalIngresos' => 0,
                    'totalEgresos' => 0,
                    'totalEmitidos' => 0,
                    'totalRecibidos' => 0,
                    'desgloseIngresos' => array_fill(0, 12, ['total' => 0]),
                    'desgloseEgresos' => array_fill(0, 12, ['total' => 0]),
                    'desgloseEmitidos' => array_fill(0, 12, ['total' => 0]), // Agregado
                    'desgloseRecibidos' => array_fill(0, 12, ['total' => 0]), // Agregado
                ];

                // Asigna los nombres de los meses
                for ($i = 1; $i <= 12; $i++) {
                    $monthName = Carbon::createFromDate($year, $i, 1)->locale('es_ES')->isoFormat('MMM');
                    $groupedData[$year]['desgloseIngresos'][$i - 1]['month'] = $monthName;
                    $groupedData[$year]['desgloseEgresos'][$i - 1]['month'] = $monthName;
                    $groupedData[$year]['desgloseEmitidos'][$i - 1]['month'] = $monthName; // Agregado
                    $groupedData[$year]['desgloseRecibidos'][$i - 1]['month'] = $monthName; // Agregado
                }
            }

            $groupedData[$year]['totalRecibidos'] += $total;
            $groupedData[$year]['desgloseRecibidos'][$month]['total'] += $total;
        }

        // Ordena por año en forma descendente
        uksort($groupedData, function($a, $b) {
            return $b <=> $a;
        });

        return response()->json([
            'periodos' => array_values($groupedData)
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Comprobante;
use App\Models\Factura;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SituacionLaboralEmpresaController extends Controller {

    public function getSituacionLaboralEmpresa() {

        $totalNominaUltimos2Meses = Comprobante::query()
            ->where('comprobantes.tipo_comprobante', 'N')
            ->where('comprobantes.fecha', '>', now()->subMonths(2)) // últimos 2 meses
            ->sum('comprobantes.total');

        $totalEmpleadosUltimos2Meses = Comprobante::query()
            ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
            ->join('receptores', 'facturas.receptor_id', '=', 'receptores.id')
            ->where('comprobantes.tipo_comprobante', 'N')
            ->where('comprobantes.fecha', '>', now()->subMonths(2)) // últimos 2 meses
            ->distinct('receptores.id')
            ->count();

        $nominaPromedio = $totalNominaUltimos2Meses / $totalEmpleadosUltimos2Meses;


        // Pago de nómina y número de empleados por mes en los últimos 3 años:
        $pagosNominaEmpleadosPorMes = Comprobante::query()
            ->select(
                DB::raw('YEAR(comprobantes.fecha) as year'),
                DB::raw('MONTH(comprobantes.fecha) as month'),
                DB::raw('SUM(comprobantes.total) as total_nomina'),
                DB::raw('COUNT(DISTINCT receptores.id) as num_empleados'),
                DB::raw('SUM(comprobantes.total) / COUNT(DISTINCT receptores.id) as nomina_promedio') // Nómina promedio
            )
            ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
            ->join('receptores', 'facturas.receptor_id', '=', 'receptores.id')
            ->where('comprobantes.tipo_comprobante', 'N')
            ->where('comprobantes.fecha', '>', now()->subYears(3)) // Solo los ultimos 3 años
            ->groupBy('year', 'month')
            ->get();

        // Número de empleados activos y rangos de sueldos en los últimos 2 meses:
        $empleadosActivos = Comprobante::query()
            ->select('receptores.id', 'receptores.rfc')
            ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
            ->join('receptores', 'facturas.receptor_id', '=', 'receptores.id')
            ->where('comprobantes.tipo_comprobante', 'N')
            ->where('comprobantes.fecha', '>', now()->subMonths(2)) // últimos 2 meses
            ->distinct()
            ->get();

        $totalEmpleadosActivos = $empleadosActivos->count(); // Número de empleados activos

        $rangosSueldo = Comprobante::query()
        ->select(
            DB::raw('CASE
                        WHEN total <= 5000 THEN "Menos de $5000"
                        WHEN total > 5000 AND total <= 10000 THEN "Entre $50000 y $10000"
                        WHEN total > 10000 AND total <= 20000 THEN "Entre $10000 y $20000"
                        WHEN total > 20000 AND total <= 50000 THEN "Entre $20000 y $50000"
                        WHEN total > 50000 AND total <= 80000 THEN "Entre $50000 y $80000"
                        ELSE "Más de $80000"
                    END AS rango_sueldo'),
            DB::raw('COUNT(DISTINCT receptores.id) as empleados'),
        )
        ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
        ->join('receptores', 'facturas.receptor_id', '=', 'receptores.id')
        ->where('comprobantes.tipo_comprobante', 'N')
        ->where('comprobantes.fecha', '>', now()->subMonths(2)) // últimos 2 meses
        ->groupBy('rango_sueldo')
        ->get();


        $rangoKeys = [
            "Menos de $5000",
            "Entre $5000 y $10000",
            "Entre $10000 y $20000",
            "Entre $20000 y $50000",
            "Entre $50000 y $80000",
            "Más de $80000",
        ];

        $rangosSueldo = $rangosSueldo->keyBy('rango_sueldo');

        foreach ($rangoKeys as $key) {
            if (!$rangosSueldo->has($key)) {
                $rangosSueldo[$key] = [
                    'rango_sueldo' => $key,
                    'empleados' => 0
                ];
            }
        }

        $rangosSueldo = $rangosSueldo->sortBy('rango_sueldo')->values();

        // Colaboradores que han recibido nómina en los últimos 12 meses:
        // Obtener la fecha de hace 12 meses
        $twelveMonthsAgo = now()->subMonths(12);

        $nominaColaboradores = Comprobante::query()
            ->select(
                'receptores.nombre',
                DB::raw('SUM(comprobantes.total) as total_nomina'),
                DB::raw('YEAR(comprobantes.fecha) as year'),
                DB::raw('MONTH(comprobantes.fecha) as month')
            )
            ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
            ->join('receptores', 'facturas.receptor_id', '=', 'receptores.id')
            ->where('comprobantes.tipo_comprobante', 'N')
            ->whereBetween('comprobantes.fecha', [$twelveMonthsAgo, now()])
            ->groupBy('receptores.nombre', 'year', 'month')
            ->get();

        $totalNominaGeneral = $nominaColaboradores->sum('total_nomina');

        $nominaColaboradoresVsTotal = $nominaColaboradores->groupBy('nombre')->map(function ($rows, $nombre) use ($totalNominaGeneral) {
            $totalNominaColaborador = $rows->sum('total_nomina');
            $nominaPorMes = $rows->groupBy(function($item, $key){
                return $item['year'] . '-' . str_pad($item['month'], 2, '0', STR_PAD_LEFT) . '-01';
            })->map->sum('total_nomina');

            // Ordenar por año y mes
            $nominaPorMes = collect($nominaPorMes)->sortKeys()->toArray();

            // Agregar total de 12 meses a cada entrada de nomina por mes
            foreach ($nominaPorMes as $yearMonth => $total) {
                $nominaPorMes[$yearMonth] = [
                    'total' => $total,
                    'vs_total_12_meses' => ($total / $totalNominaColaborador) * 100,
                ];
            }

            return [
                'nombre' => $nombre,
                'total_nomina' => $totalNominaColaborador,
                'porcentaje_del_total' => ($totalNominaColaborador / $totalNominaGeneral) * 100,
                'nomina_por_mes' => $nominaPorMes,
            ];
        })->values();

        $empleadosPorMes = Comprobante::query()
            ->select(
                DB::raw('YEAR(comprobantes.fecha) as year'),
                DB::raw('MONTH(comprobantes.fecha) as month'),
                DB::raw('GROUP_CONCAT(DISTINCT receptores.id) as ids_empleados')
            )
            ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
            ->join('receptores', 'facturas.receptor_id', '=', 'receptores.id')
            ->where('comprobantes.tipo_comprobante', 'N')
            ->where('comprobantes.fecha', '>', now()->subYears(3)) // Solo los ultimos 3 años
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(function ($item) {
                // Usamos year y month para crear una llave única para cada periodo
                return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
            })
            ->map(function ($item) {
                // Convertimos los ids_empleados en un array
                return explode(',', $item->ids_empleados);
            });

        $altasYBajas = [];
        $empleadosPrevios = [];

        foreach ($empleadosPorMes as $mes => $empleados) {
            $altas = array_diff($empleados, $empleadosPrevios);
            $bajas = array_diff($empleadosPrevios, $empleados);

            $altasYBajas[$mes] = [
                'altas' => count($altas),
                'bajas' => count($bajas),
            ];

            $empleadosPrevios = $empleados;
        }

        return response()->json([
            'nominaPromedio' => $nominaPromedio,
            'altasYBajas'                  => $altasYBajas,
            'empleadosPrevios'             => $empleadosPrevios,
            'empleadosPorMes'              => $empleadosPorMes,
            'rangosSueldo'                 => $rangosSueldo,
            'totalEmpleadosActivos'        => $totalEmpleadosActivos,
            'empleadosActivos'             => $empleadosActivos,
            'totalNomina'                  => $totalNominaGeneral,
            'nominaColaboradores'          => $nominaColaboradores,
            'nominaColaboradoresVsTotal'   => $nominaColaboradoresVsTotal,
            'pagosNominaEmpleadosPorMes'   => $pagosNominaEmpleadosPorMes,
        ]);
    }

    public function getDistribucionNominaAccionistasRepresentanteLegal() {
          // Colaboradores que han recibido nómina en los últimos 12 meses:
        // Obtener la fecha de hace 12 meses
        $twelveMonthsAgo = now()->subMonths(12);

        $nominaColaboradores = Comprobante::query()
            ->select(
                'receptores.nombre',
                DB::raw('SUM(comprobantes.total) as total_nomina'),
                DB::raw('YEAR(comprobantes.fecha) as year'),
                DB::raw('MONTH(comprobantes.fecha) as month')
            )
            ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
            ->join('receptores', 'facturas.receptor_id', '=', 'receptores.id')
            ->where('comprobantes.tipo_comprobante', 'N')
            ->whereBetween('comprobantes.fecha', [$twelveMonthsAgo, now()])
            ->groupBy('receptores.nombre', 'year', 'month')
            ->get();

        $totalNominaGeneral = $nominaColaboradores->sum('total_nomina');

        $nominaColaboradoresVsTotal = $nominaColaboradores->groupBy('nombre')->map(function ($rows, $nombre) use ($totalNominaGeneral) {
            $totalNominaColaborador = $rows->sum('total_nomina');
            $nominaPorMes = $rows->groupBy(function($item, $key){
                return $item['year'] . '-' . str_pad($item['month'], 2, '0', STR_PAD_LEFT) . '-01';
            })->map->sum('total_nomina');

            // Ordenar por año y mes
            $nominaPorMes = collect($nominaPorMes)->sortKeys()->toArray();

            // Agregar total de 12 meses a cada entrada de nomina por mes
            foreach ($nominaPorMes as $yearMonth => $total) {
                $nominaPorMes[$yearMonth] = [
                    'total' => $total,
                    'vs_total_12_meses' => ($total / $totalNominaColaborador) * 100,
                ];
            }

            return [
                'nombre' => $nombre,
                'total_nomina' => $totalNominaColaborador,
                'porcentaje_del_total' => ($totalNominaColaborador / $totalNominaGeneral) * 100,
                'nomina_por_mes' => $nominaPorMes,
            ];
        })->values();

        return response()->json([
            'totalNomina'                  => $totalNominaGeneral,
            'nominaColaboradores'          => $nominaColaboradores,
            'nominaColaboradoresVsTotal'   => $nominaColaboradoresVsTotal,
        ]);
    }

    public function getDetalleFiscalFacturacionEmitidaRecibida () {
        $facturacionAnual = Factura::query()
            ->select(
                DB::raw('YEAR(comprobantes.fecha) as year'),
                DB::raw('SUM(CASE WHEN facturas.tipo = "emitidos" THEN comprobantes.total ELSE 0 END) as total_emitido'),
                DB::raw('SUM(CASE WHEN facturas.tipo = "recibidos" THEN comprobantes.total ELSE 0 END) as total_recibido')
            )
            ->join('comprobantes', 'facturas.id', '=', 'comprobantes.factura_id')
            ->groupBy('year')
            ->get();

        $tasaCrecimientoAnual = $facturacionAnual->mapWithKeys(function ($item, $key) use ($facturacionAnual) {
            if ($key === 0) {
                // No se puede calcular la tasa de crecimiento para el primer año
                return [$item->year => null];
            }

            $prevItem = $facturacionAnual[$key - 1];
            $growthRate = ($item->total_emitido - $prevItem->total_emitido) / $prevItem->total_emitido * 100;

            return [$item->year => $growthRate];
        });

        $facturacionMasNomina = Factura::query()
            ->select(
                DB::raw('YEAR(comprobantes.fecha) as year'),
                DB::raw('SUM(CASE WHEN facturas.tipo = "recibidos" THEN comprobantes.total ELSE 0 END) + SUM(CASE WHEN comprobantes.tipo_comprobante = "N" THEN comprobantes.total ELSE 0 END) as total_recibido_mas_nomina')
            )
            ->join('comprobantes', 'facturas.id', '=', 'comprobantes.factura_id')
            ->groupBy('year')
            ->get();


        return response()->json([
            '$tasaCrecimientoAnual' => $tasaCrecimientoAnual,
            '$facturacionAnual' => $facturacionAnual,
            '$facturacionMasNomina' => $facturacionMasNomina,
        ]);
    }
}

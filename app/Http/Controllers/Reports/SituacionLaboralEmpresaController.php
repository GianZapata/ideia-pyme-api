<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Comprobante;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SituacionLaboralEmpresaController extends Controller {

    public function getSituacionLaboralEmpresa() {

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

        $numEmpleadosActivos = $empleadosActivos->count(); // Número de empleados activos

        $rangosSueldo = Comprobante::query()
            ->select(
                DB::raw('CASE
                            WHEN total <= 5000 THEN "$0-$5000"
                            WHEN total > 5000 AND total <= 10000 THEN "$5001-$10000"
                            WHEN total > 10000 AND total <= 20000 THEN "$10001-$20000"
                            WHEN total > 20000 AND total <= 50000 THEN "$20001-$50000"
                            WHEN total > 50000 AND total <= 80000 THEN "$50001-$80000"
                            ELSE ">$80000"
                        END AS rango_sueldo'),
                DB::raw('COUNT(DISTINCT CASE WHEN comprobantes.fecha > CURDATE() - INTERVAL 2 MONTH THEN receptores.id END) as promedio_ultimos_2_meses'),
                DB::raw('COUNT(DISTINCT CASE WHEN comprobantes.fecha = CURDATE() THEN receptores.id END) as empl'),
                DB::raw('COUNT(DISTINCT CASE WHEN comprobantes.fecha = CURDATE() - INTERVAL 1 MONTH THEN receptores.id END) as ant')
            )
            ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
            ->join('receptores', 'facturas.receptor_id', '=', 'receptores.id')
            ->where('comprobantes.tipo_comprobante', 'N')
            ->where('comprobantes.fecha', '>', now()->subMonths(2)) // últimos 2 meses
            ->groupBy('rango_sueldo')
            ->get();


        // Colaboradores que han recibido nómina en los últimos 12 meses:
        $nominaColaboradores = Comprobante::query()
            ->select('receptores.nombre', DB::raw('SUM(comprobantes.total) as total_nomina'))
            ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
            ->join('receptores', 'facturas.receptor_id', '=', 'receptores.id')
            ->where('comprobantes.tipo_comprobante', 'N')
            ->where('comprobantes.fecha', '>', now()->subYear())
            ->groupBy('receptores.nombre')
            ->get();

        $totalNomina = $nominaColaboradores->sum('total_nomina');

        $nominaColaboradoresVsTotal = $nominaColaboradores->map(function ($colaborador) use ($totalNomina) {
            $colaborador = clone $colaborador; // Esto crea una copia del objeto
            $colaborador->porcentaje_del_total = $colaborador->total_nomina / $totalNomina * 100;
            return $colaborador;
        });

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
            '$altasYBajas'                  => $altasYBajas,
            '$empleadosPrevios'             => $empleadosPrevios,
            '$empleadosPorMes'              => $empleadosPorMes,
            '$rangosSueldo'                 => $rangosSueldo,
            '$numEmpleadosActivos'          => $numEmpleadosActivos,
            '$empleadosActivos'             => $empleadosActivos,
            '$totalNomina'                  => $totalNomina,
            '$nominaColaboradores'          => $nominaColaboradores,
            '$nominaColaboradoresVsTotal'   => $nominaColaboradoresVsTotal,
            '$pagosNominaEmpleadosPorMes'   => $pagosNominaEmpleadosPorMes,
        ]);
    }


    //     /** @var \Illuminate\Database\Query\Builder $query */
    //     $empleadosQuery = Comprobante::query()
    //         ->select(
    //             'receptores.id',
    //             'receptores.rfc',
    //             'receptores.nombre',
    //             DB::raw('COUNT(DISTINCT comprobantes.id) as num_comprobantes'),
    //             DB::raw('SUM(comprobantes.total) as total_sum')
    //         )
    //         ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
    //         ->join('receptores', 'facturas.receptor_id', '=', 'receptores.id')
    //         ->where('comprobantes.tipo_comprobante', 'N')
    //         ->groupBy('receptores.id', 'receptores.rfc');

    //     $empleados = $empleadosQuery->get();
    //     $totalEmpleados = $empleados->count();

    //     $tresMesesAtras = Carbon::now()->subMonths(3);

    //     $empleadosActivosQuery = Comprobante::query()
    //         ->select('receptores.id', 'receptores.rfc', DB::raw('COUNT(DISTINCT comprobantes.id) as num_comprobantes'), DB::raw('SUM(comprobantes.total) as total_sum'))
    //         ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
    //         ->join('receptores', 'facturas.receptor_id', '=', 'receptores.id')
    //         ->where('comprobantes.tipo_comprobante', 'N')
    //         ->where('comprobantes.fecha', '>', $tresMesesAtras)
    //         ->groupBy('receptores.id', 'receptores.rfc');

    //     $empleadosActivos = $empleadosActivosQuery->get();
    //     $totalEmpleadosActivos = $empleadosActivos->count();

    //     $totalNomina = Comprobante::where('tipo_comprobante', 'N')->sum('total');

    //     $antiguedadPromedio = DB::table('nomina_receptores')
    //     ->select(DB::raw('AVG(DATEDIFF(CURDATE(), fecha_inicio_rel_laboral)) as antiguedad_promedio'))
    //     ->first();

    //     $nominaPromedio = Comprobante::where('tipo_comprobante', 'N')->average('total');

    //     return response()->json([
    //         'totalNomina'           => floatval($totalNomina),
    //         'nominaPromedio'        => $nominaPromedio,
    //         'antiguedadPromedio'    => $antiguedadPromedio->antiguedad_promedio,
    //         'totalEmpleadosActivos' => $totalEmpleadosActivos,
    //         'totalEmpleados'        => $totalEmpleados,
    //         // 'empleados'             => $empleados,
    //         // 'empleadosActivos'      => $empleadosActivos,
    //     ], 200);
    // }
}

<?php
namespace App\Services;

use App\Models\Comprobante;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ComportamientoHistoricoNominaService
{
    public function obtenerRangosSueldo() {
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
        return $rangosSueldo;
    }

    public function obtenerEmpleadosPorMes()
    {
        $empleadosPorMes = Comprobante::query()
            ->select(
                DB::raw('YEAR(comprobantes.fecha) as year'),
                DB::raw('MONTH(comprobantes.fecha) as month'),
                DB::raw('GROUP_CONCAT(DISTINCT receptores.id) as ids_empleados'),
                DB::raw('SUM(comprobantes.total) as total_nomina') // Agregamos el total de la nómina para cada mes
            )
            ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
            ->join('receptores', 'facturas.receptor_id', '=', 'receptores.id')
            ->where('comprobantes.tipo_comprobante', 'N')
            ->where('comprobantes.fecha', '>', now()->subYears(3)) // Solo los ultimos 3 años
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(function ($item) {
                return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT) . '-01';
            })
            ->map(function ($item) {
                return [
                    'empleados' => explode(',', $item->ids_empleados),
                    'nomina' => floatval($item->total_nomina) // Guardamos el total de la nómina
                ];
            });
        return $empleadosPorMes;
    }

    public function calcularAltasYBajas($empleadosPorMes)
    {
        $altasYBajas = [];
        $empleadosPrevios = [];

        foreach ($empleadosPorMes as $mes => $datos) {
            $altas = array_diff($datos['empleados'], $empleadosPrevios);
            $bajas = array_diff($empleadosPrevios, $datos['empleados']);

            $altasYBajas[] = [
                'fecha' => $mes,
                'altas' => count($altas),
                'bajas' => count($bajas) * -1,
                'nomina' => $datos['nomina'], // Agregamos el total de la nómina a los resultados
            ];

            $empleadosPrevios = $datos['empleados'];
        }

        return $altasYBajas;
    }

    public function obtenerEmpleadosActivos()
    {
        $empleadosActivos = Comprobante::query()
            ->select('receptores.id', 'receptores.rfc')
            ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
            ->join('receptores', 'facturas.receptor_id', '=', 'receptores.id')
            ->where('comprobantes.tipo_comprobante', 'N')
            ->where('comprobantes.fecha', '>', now()->subMonths(2)) // últimos 2 meses
            ->distinct()
            ->get();

        return $empleadosActivos;
    }

    public function calcularNominaPromedio()
    {
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

        return $nominaPromedio;
    }
}

?>

<?php
namespace App\Services;

use App\Models\Comprobante;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ComportamientoHistoricoNominaService
{
    public function obtenerRangosSueldo( $rfc ) {
        $rangosSueldo = Comprobante::query()
            ->select(
                'emisores.rfc as rfc_emisor',
                'comprobantes.total as total'
            )
            ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
            ->join('emisores', 'facturas.emisor_id', '=', 'emisores.id')
            ->where('comprobantes.tipo_comprobante', 'N')
            ->where('emisores.rfc', $rfc)
            ->where('comprobantes.fecha', '>', now()->subMonths(2))
            ->get();

        return $rangosSueldo;
    }

    public function obtenerDistribucionCompletaDeSueldos( $rfc ){

        $rangosSueldo = $this->obtenerRangosSueldo( $rfc );

        $rangos = [
            "Menos de $5000"        => 0,
            "Entre $5000 y $10000"  => 0,
            "Entre $10000 y $20000" => 0,
            "Entre $20000 y $50000" => 0,
            "Entre $50000 y $80000" => 0,
            "Más de $80000"         => 0
        ];

        foreach ($rangosSueldo as $resultado) {
            $total = $resultado->total;

            if ($total <= 5000) {
                $rangos["Menos de $5000"]++;
            } elseif ($total > 5000 && $total <= 10000) {
                $rangos["Entre $5000 y $10000"]++;
            } elseif ($total > 10000 && $total <= 20000) {
                $rangos["Entre $10000 y $20000"]++;
            } elseif ($total > 20000 && $total <= 50000) {
                $rangos["Entre $20000 y $50000"]++;
            } elseif ($total > 50000 && $total <= 80000) {
                $rangos["Entre $50000 y $80000"]++;
            } else {
                $rangos["Más de $80000"]++;
            }
        }

        $output = [];
        foreach ($rangos as $rango => $empleados) {
            $output[] = (object)[
                'rango_sueldo' => $rango,
                'empleados' => $empleados
            ];
        }

        return $output;
    }

    public function obtenerEmpleadosPorMes( $rfc  )
    {
        $empleadosPorMes = Comprobante::query()
            ->select(
                DB::raw('YEAR(comprobantes.fecha) as year'),
                DB::raw('MONTH(comprobantes.fecha) as month'),
                DB::raw('GROUP_CONCAT(DISTINCT receptores.id) as ids_empleados'),
                DB::raw('SUM(comprobantes.total) as total_nomina')
            )
            ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
            ->join('emisores', 'facturas.emisor_id', '=', 'emisores.id')
            ->join('receptores', 'facturas.receptor_id', '=', 'receptores.id')
            ->where('comprobantes.tipo_comprobante', 'N')
            ->where('comprobantes.fecha', '>', now()->subYears(3))
            ->where('emisores.rfc', $rfc)
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(function ($item) {
                return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT) . '-01';
            })
            ->map(function ($item) {
                return [
                    'empleados' => explode(',', $item->ids_empleados),
                    'nomina' => floatval($item->total_nomina)
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

    public function obtenerEmpleadosActivos( $rfc )
    {
        $empleadosActivos = Comprobante::query()
            ->select('receptores.id', 'receptores.rfc')
            ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
            ->join('emisores', 'facturas.emisor_id', '=', 'emisores.id')
            ->join('receptores', 'facturas.receptor_id', '=', 'receptores.id')
            ->where('comprobantes.tipo_comprobante', 'N')
            ->where('comprobantes.fecha', '>', now()->subMonths(2))
            ->where('emisores.rfc', $rfc)
            ->distinct()
            ->get();
        return $empleadosActivos;
    }

    public function calcularNominaPromedio( $rfc )
    {
        $nominaQuery = Comprobante::query()
            ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
            ->join('emisores', 'facturas.emisor_id', '=', 'emisores.id')
            ->join('receptores', 'facturas.receptor_id', '=', 'receptores.id')
            ->where('comprobantes.tipo_comprobante', 'N')
            ->where('comprobantes.fecha', '>', now()->subMonths(2))
            ->where('emisores.rfc', $rfc);

        $totalNominaUltimos2Meses = $nominaQuery->sum('comprobantes.total');
        $totalEmpleadosUltimos2Meses = $nominaQuery->distinct('receptores.id')->count();

        $nominaPromedio = $totalNominaUltimos2Meses / $totalEmpleadosUltimos2Meses;

        return $nominaPromedio;
    }
}

?>

<?php
namespace App\Services;

use App\Models\Comprobante;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DistribucionNominaService
{

    public function obtenerNominaAccionistasORepresentantes( $rfc )
    {
        $twelveMonthsAgo = now()->subMonths(12);

        $query = Comprobante::query()
            ->select(
                'emisores.nombre',
                DB::raw('SUM(comprobantes.total) as total_nomina'),
                DB::raw('YEAR(comprobantes.fecha) as year'),
                DB::raw('MONTH(comprobantes.fecha) as month')
            )
            ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
            ->join('emisores', 'facturas.emisor_id', '=', 'emisores.id')
            ->where('comprobantes.tipo_comprobante', 'N')
            ->where('emisores.rfc', $rfc)
            ->whereBetween('comprobantes.fecha', [$twelveMonthsAgo, now()])
            ->groupBy('emisores.nombre', 'year', 'month');

        return $query->get();
    }

    public function calcularNominaColaboradoresVsTotal( $rfc )
    {
        $nominaColaboradores = $this->obtenerNominaAccionistasORepresentantes( $rfc );
        $totalNominaGeneral = $nominaColaboradores->sum('total_nomina');

        return $nominaColaboradores->groupBy('nombre')->map(function ($rows, $nombre) use ($totalNominaGeneral) {
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
    }
}

?>

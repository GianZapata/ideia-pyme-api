<?php

namespace App\Http\Controllers\Reports\Facturacion\ComparativoAnual;

use App\Http\Controllers\Controller;
use App\Models\Comprobante;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecibidosController extends Controller
{
    public function getRecibidos( Request $request ){

        $rfc =  $request->rfc;

        if( !$rfc ) {
            return response()->json(['message' => 'El RFC es requerido'], 400);
        }

        $recibidos = Comprobante::query()
            ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
            ->join('receptores', 'receptores.id', '=', 'facturas.receptor_id')
            ->select(
                DB::raw('YEAR(comprobantes.fecha) AS year'),
                DB::raw('MONTH(comprobantes.fecha) AS month'),
                DB::raw('SUM(comprobantes.total) AS recibidoTotal'),
                DB::raw('SUM(comprobantes.sub_total) AS recibidoSubtotal'),
                DB::raw('SUM(comprobantes.descuento) AS recibidoDescuentos'),
                DB::raw('SUM(comprobantes.sub_total - comprobantes.descuento) AS recibidoNeto'),
                DB::raw('SUM(CASE WHEN facturas.cancelado = 1 THEN comprobantes.total ELSE 0 END) AS recibidoCancelados')
            )
            ->where('facturas.tipo', 'recibidos')
            ->where('receptores.rfc', $rfc)
            ->groupBy(
                DB::raw('YEAR(comprobantes.fecha)'),
                DB::raw('MONTH(comprobantes.fecha)')
            )
            ->orderByDesc('year')
            ->get();

        $dataRecibidos = [];

        foreach ($recibidos as $comprobante) {
            $year = $comprobante->year;
            $monthIndex = $comprobante->month - 1;

            if (!isset($dataRecibidos[$year])) {
                $dataRecibidos[$year] = [
                    'year' => $year,
                    'recibidoTotal' => 0,
                    'recibidoSubtotal' => 0,
                    'recibidoDescuentos' => 0,
                    'recibidoNeto' => 0,
                    'recibidoCancelados' => 0,
                    'desglose' => array_fill(0, 12, ['total' => 0, 'month' => ''])
                ];

                for ($i = 1; $i <= 12; $i++) {
                    $monthName = Carbon::createFromDate($year, $i, 1)->locale('es_ES')->isoFormat('MMM');
                    $dataRecibidos[$year]['desglose'][$i - 1]['month'] = $monthName;
                }
            }

            $dataRecibidos[$year]['recibidoTotal'] += floatval($comprobante->recibidoTotal);
            $dataRecibidos[$year]['recibidoSubtotal'] += floatval($comprobante->recibidoSubtotal);
            $dataRecibidos[$year]['recibidoDescuentos'] += floatval($comprobante->recibidoDescuentos);
            $dataRecibidos[$year]['recibidoNeto'] += floatval($comprobante->recibidoNeto);
            $dataRecibidos[$year]['recibidoCancelados'] += floatval($comprobante->recibidoCancelados);
            $dataRecibidos[$year]['desglose'][$monthIndex]['total'] += floatval($comprobante->recibidoTotal);
        }

        uksort($dataRecibidos, function ($a, $b) {
            return $b - $a;
        });

        return response()->json([
            'recibidos' => array_values($dataRecibidos)
        ]);
    }
}

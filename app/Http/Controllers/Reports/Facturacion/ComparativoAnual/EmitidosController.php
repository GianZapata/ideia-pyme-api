<?php

namespace App\Http\Controllers\Reports\Facturacion\ComparativoAnual;

use App\Http\Controllers\Controller;
use App\Models\Comprobante;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmitidosController extends Controller
{
    public function getEmitidos( Request $request ) {
        $rfc =  $request->rfc;

        if( !$rfc ) {
            return response()->json(['message' => 'El RFC es requerido'], 400);
        }

        $emitidos = Comprobante::query()
            ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
            ->join('emisores', 'emisores.id', '=', 'facturas.emisor_id')
            ->select(
                DB::raw('YEAR(comprobantes.fecha) AS year'),
                DB::raw('MONTH(comprobantes.fecha) AS month'),
                DB::raw('SUM(comprobantes.total) AS emitidoTotal'),
                DB::raw('SUM(comprobantes.sub_total) AS emitidoSubtotal'),
                DB::raw('SUM(comprobantes.descuento) AS emitidoDescuentos'),
                DB::raw('SUM(comprobantes.sub_total - comprobantes.descuento) AS emitidoNeto'),
                DB::raw('SUM(CASE WHEN facturas.cancelado = 1 THEN comprobantes.total ELSE 0 END) AS emitidoCancelados')
            )
            ->where('facturas.tipo', 'emitidos')
            ->where('emisores.rfc', $rfc)
            ->groupBy(
                DB::raw('YEAR(comprobantes.fecha)'),
                DB::raw('MONTH(comprobantes.fecha)')
            )
            ->orderBy('year', 'desc')
            ->get();

        $dataEmitidos = [];

        foreach ($emitidos as $comprobante) {
            $year = $comprobante->year;
            $monthIndex = $comprobante->month - 1;

            if (!isset($dataEmitidos[$year])) {
                $dataEmitidos[$year] = [
                    'year' => $year,
                    'emitidoTotal' => 0,
                    'emitidoSubtotal' => 0,
                    'emitidoDescuentos' => 0,
                    'emitidoNeto' => 0,
                    'emitidoCancelados' => 0,
                    'desglose' => array_fill(0, 12, ['total' => 0, 'month' => ''])
                ];

                for ($i = 1; $i <= 12; $i++) {
                    $monthName = Carbon::createFromDate($year, $i, 1)->locale('es_ES')->isoFormat('MMM');
                    $dataEmitidos[$year]['desglose'][$i - 1]['month'] = $monthName;
                }
            }

            $dataEmitidos[$year]['emitidoTotal'] += floatval($comprobante->emitidoTotal);
            $dataEmitidos[$year]['emitidoSubtotal'] += floatval($comprobante->emitidoSubtotal);
            $dataEmitidos[$year]['emitidoDescuentos'] += floatval($comprobante->emitidoDescuentos);
            $dataEmitidos[$year]['emitidoNeto'] += floatval($comprobante->emitidoNeto);
            $dataEmitidos[$year]['emitidoCancelados'] += floatval($comprobante->emitidoCancelados);
            $dataEmitidos[$year]['desglose'][$monthIndex]['total'] = floatval($comprobante->emitidoTotal);
        }

        uksort($dataEmitidos, function ($a, $b) {
            return $b - $a;
        });

        return response()->json([
            'emitidos' => array_values($dataEmitidos)
        ]);
    }
}

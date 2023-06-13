<?php

namespace App\Services;

use App\Models\Comprobante;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmitidoService {

    public function getEmitidosData($rfc)
    {
        $emitidos = $this->queryEmitidos($rfc);
        $dataEmitidos = $this->processEmitidosData($emitidos);

        return $dataEmitidos;
    }

    private function queryEmitidos($rfc)
    {
        return Comprobante::query()
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
            ->orderByDesc('year')
            ->get();
    }

    private function processEmitidosData($emitidos)
    {
        $dataEmitidos = [];

        foreach ($emitidos as $comprobante) {
            $year = $comprobante->year;
            $monthIndex = $comprobante->month - 1;

            if (!isset($dataEmitidos[$year])) {
                $dataEmitidos[$year] = $this->initializeYearData($year);
            }

            $this->updateYearData($dataEmitidos, $year, $comprobante, $monthIndex);
        }

        uksort($dataEmitidos, function ($a, $b) {
            return $b - $a;
        });

        return array_values($dataEmitidos);
    }

    private function initializeYearData($year)
    {
        $data = [
            'year'              => $year,
            'emitidoTotal'      => 0,
            'emitidoSubtotal'   => 0,
            'emitidoDescuentos' => 0,
            'emitidoNeto'       => 0,
            'emitidoCancelados' => 0,
            'desglose'          => array_fill(0, 12, ['total' => 0, 'month' => ''])
        ];

        for ($i = 1; $i <= 12; $i++) {
            $monthName = Carbon::createFromDate($year, $i, 1)->locale('es_ES')->isoFormat('MMM');
            $data['desglose'][$i - 1]['month'] = $monthName;
        }

        return $data;
    }

    private function updateYearData(&$data, $year, $comprobante, $monthIndex)
    {
        $data[$year]['emitidoTotal'] += floatval($comprobante->emitidoTotal);
        $data[$year]['emitidoSubtotal'] += floatval($comprobante->emitidoSubtotal);
        $data[$year]['emitidoDescuentos'] += floatval($comprobante->emitidoDescuentos);
        $data[$year]['emitidoNeto'] += floatval($comprobante->emitidoNeto);
        $data[$year]['emitidoCancelados'] += floatval($comprobante->emitidoCancelados);
        $data[$year]['desglose'][$monthIndex]['total'] = floatval($comprobante->emitidoTotal);
    }
}

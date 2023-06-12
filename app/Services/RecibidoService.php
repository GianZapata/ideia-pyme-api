<?php

namespace App\Services;

use App\Models\Comprobante;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecibidoService {

    public function getRecibidosData($rfc) {
        $recibidos = $this->queryRecibidos($rfc);
        $dataRecibidos = $this->processRecibidosData($recibidos);
        return $dataRecibidos;
    }

    private function queryRecibidos($rfc) {
        return Comprobante::query()
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
    }

    private function processRecibidosData($recibidos)
    {
        $dataRecibidos = [];

        foreach ($recibidos as $comprobante) {
            $year = $comprobante->year;
            $monthIndex = $comprobante->month - 1;

            if (!isset($dataRecibidos[$year])) {
                $dataRecibidos[$year] = $this->initializeYearData($year);
            }

            $this->updateYearData($dataRecibidos, $year, $comprobante, $monthIndex);
        }

        uksort($dataRecibidos, function ($a, $b) {
            return $b - $a;
        });

        return array_values($dataRecibidos);
    }

    private function initializeYearData($year) {
        $data = [
            'year'              => $year,
            'recibidoTotal'      => 0,
            'recibidoSubtotal'   => 0,
            'recibidoDescuentos' => 0,
            'recibidoNeto'       => 0,
            'recibidoCancelados' => 0,
            'desglose'          => array_fill(0, 12, ['total' => 0, 'month' => ''])
        ];

        for ($i = 1; $i <= 12; $i++) {
            $monthName = Carbon::createFromDate($year, $i, 1)->locale('es_ES')->isoFormat('MMM');
            $data['desglose'][$i - 1]['month'] = $monthName;
        }

        return $data;
    }

    private function updateYearData(&$data, $year, $comprobante, $monthIndex) {
        $data[$year]['recibidoTotal'] += floatval($comprobante->recibidoTotal);
        $data[$year]['recibidoSubtotal'] += floatval($comprobante->recibidoSubtotal);
        $data[$year]['recibidoDescuentos'] += floatval($comprobante->recibidoDescuentos);
        $data[$year]['recibidoNeto'] += floatval($comprobante->recibidoNeto);
        $data[$year]['recibidoCancelados'] += floatval($comprobante->recibidoCancelados);
        $data[$year]['desglose'][$monthIndex]['total'] = floatval($comprobante->recibidoTotal);
    }
}

<?php

namespace App\Services;

use App\Models\Comprobante;
use Illuminate\Support\Facades\DB;

class EgresoService {
    public function queryEgresos( $rfc ) {
        return Comprobante::query()
            ->join('facturas', 'comprobantes.factura_id', '=', 'facturas.id')
            ->leftJoin('emisores', 'facturas.emisor_id', '=', 'emisores.id')
            ->leftJoin('receptores', 'facturas.receptor_id', '=', 'receptores.id')
            ->select(
                DB::raw('YEAR(comprobantes.fecha) AS year'),
                DB::raw('MONTH(comprobantes.fecha) AS month'),
                DB::raw('SUM(comprobantes.total) AS egresoTotal'),
                DB::raw('SUM(comprobantes.sub_total) AS egresoSubtotal'),
                DB::raw('SUM(comprobantes.descuento) AS egresoDescuentos'),
                DB::raw('SUM(comprobantes.sub_total - comprobantes.descuento) AS egresoNeto'),
                DB::raw('SUM(CASE WHEN facturas.cancelado = 1 THEN comprobantes.total ELSE 0 END) AS egresoCancelados')
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
    }
}

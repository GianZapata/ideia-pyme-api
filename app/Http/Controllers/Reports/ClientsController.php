<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Comprobante;
use App\Models\Factura;
use App\Models\Emisor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ClientsController extends Controller {

    public function obtenerTop5Clientes( $rfc ) {

        $top5Clientes = Emisor::select(
            'facturas.receptor_id',
            DB::raw('COUNT(*) AS total_facturas'),
            DB::raw('MAX(receptores.rfc) AS rfc'),
            'receptores.nombre',
            // DB::raw('GROUP_CONCAT(comprobantes.moneda SEPARATOR ",") AS monedas'),
            DB::raw('SUM(comprobantes.total) AS total'),
            DB::raw('MIN(comprobantes.fecha) AS fecha_minima'),
            DB::raw('MAX(comprobantes.fecha) AS fecha_maxima'),
            DB::raw('TIMESTAMPDIFF(MONTH, MIN(comprobantes.fecha), CURDATE()) / 12 AS antiguedad'),
            DB::raw('CASE WHEN TIMESTAMPDIFF(MONTH, MAX(comprobantes.fecha), CURDATE()) > 1 THEN "noactivo" ELSE "activo" END AS estado')
        )
        ->leftJoin('facturas', 'emisores.id', '=', 'facturas.emisor_id')
        ->leftJoin('comprobantes', 'facturas.id', '=', 'comprobantes.factura_id')
        ->leftJoin('receptores', 'facturas.receptor_id', '=', 'receptores.id')
        ->where('emisores.rfc', $rfc)
        ->groupBy('facturas.receptor_id', 'receptores.nombre')
        ->orderBy('total_facturas', 'DESC')
        ->limit(5)
        ->get();

        return response()->json([
            'top5clientes' => $top5Clientes,
        ]);
    }

    public function obtenerDiversificacionClientes( $rfc ) {
        $totalFacturacion = Emisor::leftJoin('facturas', 'emisores.id', '=', 'facturas.emisor_id')
            ->leftJoin('comprobantes', 'facturas.id', '=', 'comprobantes.factura_id')
            ->where('emisores.rfc', $rfc)
            ->sum(DB::raw('comprobantes.total * IF(comprobantes.tipo_cambio = "0.00" OR comprobantes.tipo_cambio = "1.00", 1, comprobantes.tipo_cambio)'));

        $query = Emisor::select(
                'facturas.receptor_id',
                DB::raw('COUNT(*) AS total_facturas'),
                DB::raw('MAX(receptores.rfc) AS rfc'),
                'receptores.nombre',
                DB::raw('SUM(comprobantes.total * IF(comprobantes.tipo_cambio = "0.00" OR comprobantes.tipo_cambio = "1.00", 1, comprobantes.tipo_cambio)) AS totalFinal'),
                DB::raw('SUM(comprobantes.total) AS total'),
                DB::raw('MIN(comprobantes.fecha) AS fecha_minima'),
                DB::raw('MAX(comprobantes.fecha) AS fecha_maxima'),
                DB::raw('TIMESTAMPDIFF(MONTH, MIN(comprobantes.fecha), CURDATE()) / 12 AS antiguedad'),
                DB::raw('CASE WHEN TIMESTAMPDIFF(MONTH, MAX(comprobantes.fecha), CURDATE()) > 1 THEN "noactivo" ELSE "activo" END AS estado')
            )
            ->leftJoin('facturas', 'emisores.id', '=', 'facturas.emisor_id')
            ->leftJoin('comprobantes', 'facturas.id', '=', 'comprobantes.factura_id')
            ->leftJoin('receptores', 'facturas.receptor_id', '=', 'receptores.id')
            ->where('emisores.rfc', $rfc)
            ->groupBy('facturas.receptor_id', 'receptores.nombre')
            ->orderBy('total_facturas', 'DESC')
            ->get();

        $numClientes = count($query);
        $diversificacionClientes = [];
        $antiguedadPromedio = 0;

        foreach ($query as $cliente) {
            $porcentajeFacturacion = ($cliente->totalFinal / $totalFacturacion) * 100;
            $diversificacionClientes[] = [
                'sumClients' => "1",
                'receptor_id' => $cliente->receptor_id,
                'total_facturas' => $cliente->total_facturas,
                'rfc' => $cliente->rfc,
                'nombre' => $cliente->nombre,
                'total' => $cliente->total,
                'totalFinal' => $cliente->totalFinal,
                'fecha_minima' => $cliente->fecha_minima,
                'fecha_maxima' => $cliente->fecha_maxima,
                'antiguedad' => $cliente->antiguedad,
                'estado' => $cliente->estado,
                'porcentaje_facturacion' => $porcentajeFacturacion,
            ];
            $antiguedadPromedio += $cliente->antiguedad;
        }

        $antiguedadPromedio /= $numClientes;

        return response()->json([
            'diversificacionClientes' => $diversificacionClientes,
            'numClientesPorcentaje' => $numClientes,
            'antiguedadPromedio' => $antiguedadPromedio,
        ]);
    }


}

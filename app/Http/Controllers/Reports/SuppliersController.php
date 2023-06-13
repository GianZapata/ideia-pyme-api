<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Comprobante;
use App\Models\Factura;
use App\Models\Emisor;
use App\Models\Receptor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SuppliersController extends Controller {

    public function obtenerTop5Proveedores() {

        $top5Proveedores = Receptor::select(
                'facturas.emisor_id',
                'receptores.rfc AS toRFC',
                DB::raw('COUNT(*) AS total_facturas'),
                DB::raw('MAX(emisores.rfc) AS rfc'),
                'emisores.nombre',
                DB::raw('SUM(comprobantes.total) AS total'),
                DB::raw('MIN(comprobantes.fecha) AS fecha_minima'),
                DB::raw('MAX(comprobantes.fecha) AS fecha_maxima'),
                DB::raw('TIMESTAMPDIFF(MONTH, MIN(comprobantes.fecha), CURDATE()) / 12 AS antiguedad'),
                DB::raw('CASE WHEN TIMESTAMPDIFF(MONTH, MAX(comprobantes.fecha), CURDATE()) > 1 THEN "noactivo" ELSE "activo" END AS estado')
            )
            ->leftJoin('facturas', 'receptores.id', '=', 'facturas.receptor_id')
            ->leftJoin('comprobantes', 'facturas.id', '=', 'comprobantes.factura_id')
            ->leftJoin('emisores', 'facturas.emisor_id', '=', 'emisores.id')
            ->where('receptores.rfc', 'ACO091214PD0')
            ->groupBy('facturas.emisor_id', 'emisores.nombre','toRFC')
            ->orderBy('total_facturas', 'DESC')
            ->limit(5)
            ->get();


        return response()->json([
            'top5proveedores' => $top5Proveedores,
        ]);
    }

    // public function obtenerDiversificacionClientes() {
    //     $totalFacturacion = Emisor::leftJoin('facturas', 'emisores.id', '=', 'facturas.emisor_id')
    //         ->leftJoin('comprobantes', 'facturas.id', '=', 'comprobantes.factura_id')
    //         ->where('emisores.rfc', 'ACO091214PD0')
    //         ->sum(DB::raw('comprobantes.total * IF(comprobantes.tipo_cambio = "0.00" OR comprobantes.tipo_cambio = "1.00", 1, comprobantes.tipo_cambio)'));

    //     $query = Emisor::select(
    //             'facturas.receptor_id',
    //             DB::raw('COUNT(*) AS total_facturas'),
    //             DB::raw('MAX(receptores.rfc) AS rfc'),
    //             'receptores.nombre',
    //             DB::raw('SUM(comprobantes.total * IF(comprobantes.tipo_cambio = "0.00" OR comprobantes.tipo_cambio = "1.00", 1, comprobantes.tipo_cambio)) AS totalFinal'),
    //             DB::raw('SUM(comprobantes.total) AS total'),
    //             DB::raw('MIN(comprobantes.fecha) AS fecha_minima'),
    //             DB::raw('MAX(comprobantes.fecha) AS fecha_maxima'),
    //             DB::raw('TIMESTAMPDIFF(MONTH, MIN(comprobantes.fecha), CURDATE()) / 12 AS antiguedad'),
    //             DB::raw('CASE WHEN TIMESTAMPDIFF(MONTH, MAX(comprobantes.fecha), CURDATE()) > 1 THEN "noactivo" ELSE "activo" END AS estado')
    //         )
    //         ->leftJoin('facturas', 'emisores.id', '=', 'facturas.emisor_id')
    //         ->leftJoin('comprobantes', 'facturas.id', '=', 'comprobantes.factura_id')
    //         ->leftJoin('receptores', 'facturas.receptor_id', '=', 'receptores.id')
    //         ->where('emisores.rfc', 'ACO091214PD0')
    //         ->groupBy('facturas.receptor_id', 'receptores.nombre')
    //         ->orderBy('total_facturas', 'DESC')
    //         ->get();

    //     $numClientes = count($query);
    //     $diversificacionClientes = [];
    //     $antiguedadPromedio = 0;

    //     foreach ($query as $cliente) {
    //         $porcentajeFacturacion = ($cliente->totalFinal / $totalFacturacion) * 100;
    //         $diversificacionClientes[] = [
    //             'sumClients' => "1",
    //             'receptor_id' => $cliente->receptor_id,
    //             'total_facturas' => $cliente->total_facturas,
    //             'rfc' => $cliente->rfc,
    //             'nombre' => $cliente->nombre,
    //             'total' => $cliente->total,
    //             'totalFinal' => $cliente->totalFinal,
    //             'fecha_minima' => $cliente->fecha_minima,
    //             'fecha_maxima' => $cliente->fecha_maxima,
    //             'antiguedad' => $cliente->antiguedad,
    //             'estado' => $cliente->estado,
    //             'porcentaje_facturacion' => $porcentajeFacturacion,
    //         ];
    //         $antiguedadPromedio += $cliente->antiguedad;
    //     }

    //     $antiguedadPromedio /= $numClientes;

    //     return response()->json([
    //         'diversificacionClientes' => $diversificacionClientes,
    //         'numClientesPorcentaje' => $numClientes,
    //         'antiguedadPromedio' => $antiguedadPromedio,
    //     ]);
    // }


}
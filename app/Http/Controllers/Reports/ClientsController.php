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

    public function obtenerTop5Clientes()
{

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
    ->where('emisores.rfc', 'ACO091214PD0')
    ->groupBy('facturas.receptor_id', 'receptores.nombre')
    ->orderBy('total_facturas', 'DESC')
    ->limit(5)
    ->get();


    // $top5Clientes = Emisor::select('facturas.receptor_id', DB::raw('COUNT(*) AS total_facturas'), DB::raw('MAX(receptores.rfc) AS rfc'), 'receptores.nombre', DB::raw('GROUP_CONCAT(comprobantes.moneda SEPARATOR ",") AS monedas'), DB::raw('SUM(comprobantes.total) AS total'))
    // ->leftJoin('facturas', 'emisores.id', '=', 'facturas.emisor_id')
    // ->leftJoin('comprobantes', 'facturas.id', '=', 'comprobantes.factura_id')
    // ->leftJoin('receptores', 'facturas.receptor_id', '=', 'receptores.id')
    // ->where('emisores.rfc', 'ACO091214PD0')
    // ->groupBy('facturas.receptor_id', 'receptores.nombre')
    // ->orderBy('total_facturas', 'DESC')
    // ->limit(5)
    // ->get();


    // $top5Clientes = Emisor::select('facturas.receptor_id', DB::raw('COUNT(*) AS total_facturas'), DB::raw('MAX(receptores.rfc) AS rfc'), 'receptores.nombre')
    //     ->leftJoin('facturas', 'emisores.id', '=', 'facturas.emisor_id')
    //     ->leftJoin('comprobantes', 'facturas.id', '=', 'comprobantes.factura_id')
    //     ->leftJoin('receptores', 'facturas.receptor_id', '=', 'receptores.id')
    //     ->where('emisores.rfc', 'ACO091214PD0')
    //     ->groupBy('facturas.receptor_id', 'receptores.nombre')
    //     ->orderBy('total_facturas', 'DESC')
    //     ->limit(5)
    //     ->get();

    // $top5Clientes = Emisor::select('facturas.receptor_id', DB::raw('COUNT(*) AS total_facturas'), DB::raw('MAX(receptores.rfc) AS rfc'), 'receptores.nombre', DB::raw('SUM(comprobantes.total) AS total'), 'comprobantes.moneda')
    //     ->leftJoin('facturas', 'emisores.id', '=', 'facturas.emisor_id')
    //     ->leftJoin('comprobantes', 'facturas.id', '=', 'comprobantes.factura_id')
    //     ->leftJoin('receptores', 'facturas.receptor_id', '=', 'receptores.id')
    //     ->where('emisores.rfc', 'ACO091214PD0')
    //     ->groupBy('facturas.receptor_id', 'receptores.nombre', 'comprobantes.moneda')
    //     ->orderBy('total_facturas', 'DESC')
    //     ->limit(5)
    //     ->get();

    return response()->json([
        'top5clientes' => $top5Clientes,
    ]);
}

}

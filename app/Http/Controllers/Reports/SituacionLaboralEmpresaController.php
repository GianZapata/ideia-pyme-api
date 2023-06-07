<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Comprobante;
use Illuminate\Http\Request;

class SituacionLaboralEmpresaController extends Controller
{
    public function getSituacionLaboralEmpresa(){
        $totalEmpleados = Comprobante::fromSub(function ($query) {
            $query->select('t3.*')
                ->from('comprobantes AS t1')
                ->join('facturas AS t2', 't1.factura_id', '=', 't2.id')
                ->join('receptores AS t3', 't2.receptor_id', '=', 't3.id')
                ->where('t1.serie', "=", 'NOMINA')
                ->groupBy('t3.id', 't3.rfc');
        }, 'employees')
        ->count();


        return response()->json(['totalEmpleados' => $totalEmpleados], 200);
    }
}

<?php

namespace App\Http\Controllers\Reports\Facturacion\ComparativoAnual;

use App\Http\Controllers\Controller;
use App\Models\Comprobante;
use App\Services\EmitidoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmitidosController extends Controller
{
    protected $emitidoService;

    public function __construct(EmitidoService $emitidoService)
    {
        $this->emitidoService = $emitidoService;
    }

    public function getEmitidos(Request $request)
    {
        $rfc =  $request->rfc;

        if (!$rfc) {
            return response()->json(['message' => 'El RFC es requerido'], 400);
        }

        $emitidos = $this->emitidoService->getEmitidosData($rfc);

        return response()->json([
            'emitidos' => $emitidos
        ]);
    }

}

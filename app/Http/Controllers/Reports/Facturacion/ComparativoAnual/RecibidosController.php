<?php

namespace App\Http\Controllers\Reports\Facturacion\ComparativoAnual;

use App\Http\Controllers\Controller;
use App\Models\Comprobante;
use App\Services\EmitidoService;
use App\Services\RecibidoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecibidosController extends Controller
{
    protected $recibidoService;

    public function __construct(RecibidoService $recibidoService)
    {
        $this->recibidoService = $recibidoService;
    }

    public function getRecibidos(Request $request)
    {
        $rfc =  $request->rfc;

        if (!$rfc) {
            return response()->json(['message' => 'El RFC es requerido'], 400);
        }

        $recibidos = $this->recibidoService->getRecibidosData($rfc);

        return response()->json([
            'recibidos' => $recibidos
        ]);
    }

}

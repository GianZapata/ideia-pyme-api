<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    public function store( StoreClientRequest $request ) {
        DB::beginTransaction();

        try {
            $clientRequest = $request->validated();
            /** @var \App\Models\User $authUser **/
            $authUser = Auth::user();
            if(!$authUser){
                $errors['auth'] = 'No se encontró el usuario autenticado.';
                return response()->json([
                    'message' => 'No se encontró el usuario autenticado.',
                    'errors' => $errors
                ], 400);
            }

            $authUserId = $authUser->id;

            $client = Client::create([
                'user_id'               => $authUserId,
                'name'                  => $clientRequest['name'],
                'score'                 => $clientRequest['score'] ?? null,
                'rfc'                   => isset($clientRequest['rfc']) ? Str::upper($clientRequest['rfc']) : null,
                'anioConstitucion'      => $clientRequest['anioConstitucion'],
                'sector_actividad'      => $clientRequest['sector_actividad'],
                'ventas'                => $clientRequest['ventas'],
                'ventasAnterior'        => $clientRequest['ventasAnterior'],
                'trabActivo'            => $clientRequest['trabActivo'],
                'otrosIng'              => $clientRequest['otrosIng'],
                'resExplotacion'        => $clientRequest['resExplotacion'],
                'resFinanciero'         => $clientRequest['resFinanciero'],
                'resAntesImp'           => $clientRequest['resAntesImp'],
                'deudoresComerciales'   => $clientRequest['deudoresComerciales'],
                'inversionesFin'        => $clientRequest['inversionesFin'],
                'efectivoLiquidez'      => $clientRequest['efectivoLiquidez'],
                'activoTotal'           => $clientRequest['activoTotal'],
                'pasivoNoCirculante'    => $clientRequest['pasivoNoCirculante'],
                'provisionesLargoPlazo' => $clientRequest['provisionesLargoPlazo'],
                'pasivoCirculante'      => $clientRequest['pasivoCirculante'],
                'capitalContable'       => $clientRequest['capitalContable'],
                'prestamosActuales'     => $clientRequest['prestamosActuales'],
            ]);

            if( !$client ){
                return response()->json([
                    'message' => 'No se pudo crear el cliente.',
                    'errors' => [
                        'client' => 'No se pudo crear el cliente.'
                    ]
                ], 400);
            }

            DB::commit();
            return response()->json([
                'client'=> $client,
            ], 200);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json([
                'message' => 'Ocurrió un error al crear el cliente.',
                'errors' => [],
                'exception' => $th->getMessage()
            ], 500);
        }
    }

    public function update( UpdateClientRequest $request, $clientId ) {

        DB::beginTransaction();
        try {
            $clientRequest = $request->validated();

            $client = Client::find($clientId);

            if( !$client ){
                return response()->json([
                    'message' => 'No se pudo crear el cliente.',
                    'errors' => [
                        'client' => 'No se encontró el cliente.'
                    ]
                ], 400);
            }

            /** @var \App\Models\User $authUser **/
            $authUser = Auth::user();
            if(!$authUser){
                return response()->json([
                    'message' => 'No se encontró el usuario autenticado.',
                    'errors' => [
                        'auth' => 'No se encontró el usuario autenticado.'
                    ]
                ], 400);
            }

            $authUserId = $authUser->id;

            if( $client->user_id != $authUserId ){
                return response()->json([
                    'message' => 'No se encontró el usuario autenticado.',
                    'errors' => [
                        'auth' => 'No se encontró el usuario autenticado.'
                    ]
                ], 400);
            }

            $client->update([
                'name'                   => $clientRequest['name'] ?? $client->name,
                'score'                  => $clientRequest['score'] ?? $client->score,
                'rfc'                    => isset($clientRequest['rfc']) ? Str::upper($clientRequest['rfc']) : $client->rfc,
                'anioConstitucion'       => $clientRequest['anioConstitucion'] ?? $client->anioConstitucion,
                'sector_actividad'       => $clientRequest['sector_actividad'] ?? $client->sector_actividad,
                'ventas'                 => $clientRequest['ventas'] ?? $client->ventas,
                'ventasAnterior'         => $clientRequest['ventasAnterior'] ?? $client->ventasAnterior,
                'trabActivo'             => $clientRequest['trabActivo'] ?? $client->trabActivo,
                'otrosIng'               => $clientRequest['otrosIng'] ?? $client->otrosIng,
                'resExplotacion'         => $clientRequest['resExplotacion'] ?? $client->resExplotacion,
                'resFinanciero'          => $clientRequest['resFinanciero'] ?? $client->resFinanciero,
                'resAntesImp'            => $clientRequest['resAntesImp'] ?? $client->resAntesImp,
                'deudoresComerciales'    => $clientRequest['deudoresComerciales'] ?? $client->deudoresComerciales,
                'inversionesFin'         => $clientRequest['inversionesFin'] ?? $client->inversionesFin,
                'efectivoLiquidez'       => $clientRequest['efectivoLiquidez'] ?? $client->efectivoLiquidez,
                'activoTotal'            => $clientRequest['activoTotal'] ?? $client->activoTotal,
                'pasivoNoCirculante'     => $clientRequest['pasivoNoCirculante'] ?? $client->pasivoNoCirculante,
                'provisionesLargoPlazo'  => $clientRequest['provisionesLargoPlazo'] ?? $client->provisionesLargoPlazo,
                'pasivoCirculante'       => $clientRequest['pasivoCirculante'] ?? $client->pasivoCirculante,
                'capitalContable'        => $clientRequest['capitalContable'] ?? $client->capitalContable,
                'prestamosActuales'      => $clientRequest['prestamosActuales'] ?? $client->prestamosActuales,
            ]);

            if( $client->idAnalisis === null ) {
                $client->update([
                    'antiguedadEmpresa'      => $clientRequest['antiguedadEmpresa'] ?? null,
                    'reconocimientoMercado'  => $clientRequest['reconocimientoMercado'] ?? null,
                    'informeComercial'       => $clientRequest['informeComercial'] ?? null,
                    'infraestructura'        => $clientRequest['infraestructura'] ?? null,
                    'problemasLegales'       => $clientRequest['problemasLegales'] ?? null,
                    'calidadCartera'         => $clientRequest['calidadCartera'] ?? null,
                    'referenciasBancarias'   => $clientRequest['referenciasBancarias'] ?? null,
                    'referenciasComerciales' => $clientRequest['referenciasComerciales'] ?? null,
                    'importanciaMop'         => $clientRequest['importanciaMop'] ?? null,
                    'perteneceHolding'       => $clientRequest['perteneceHolding'] ?? null,
                    'idAnalisis'             => $clientRequest['idAnalisis'] ?? null,
                ]);
            }

            $client->save();



            DB::commit();
            return response()->json([
                'client'=> $client,
                'quotation' => isset($newQuotation) ? $newQuotation : null
            ], 200);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json([
                'message' => 'Ocurrió un error al crear el cliente.',
                'errors' => [
                    'client' => 'Ocurrió un error al crear el cliente.'
                ],
                'exception' => $th->getMessage()
            ], 500);
        }
    }


    /**
     * Función para obtener todos los clientes asociados al usuario autenticado.
     *
     * @param Request $request La solicitud HTTP recibida.
     * @return \Illuminate\Http\JsonResponse Una respuesta JSON que contiene la lista de clientes solicitada.
     */
    public function getAll( Request $request ) {
        $limit = $request->get('limit', 25);
        $page = $request->get('page', 1);

        $searchTerm = $request->get('searchTerm', null);

        /** @var \App\Models\User $authUser **/
        $authUser = Auth::user();

        if(!$authUser){
            return response()->json([
                'message' => 'No se encontró el usuario autenticado.',
                'errors' => []
            ], 400);
        }

        $clientQuery = Client::where('user_id', $authUser->id)->with('report')
            ->with('user');

        if( empty( $searchTerm )) {
            $clients = $clientQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $clientQuery = $clientQuery->where('name', 'LIKE', '%' . $searchTerm . '%');
            $clients = $clientQuery->paginate($limit, ['*'], 'page', $page);
        }

        return response()->json([
            'clients' => $clients->items(),
            'total' => $clients->total(),
        ], 200);
    }

    /**
     * Get By Id
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function getById( Request $request, $clientId ){

        if(!is_numeric($clientId)){
            return response()->json([
                'message' => 'Invalid id'
            ], 400);
        }

        $authUser = Auth::user();
        if(!$authUser){
            return response()->json([
                'message' => 'No se encontró el usuario autenticado.',
                'errors' => []
            ], 400);
        }

        $client = Client::where('id', $clientId)
            ->where('user_id', $authUser->id)
            ->with('user','report')
            ->first();

        if(!$client){
            return response()->json([
                'message' => 'Client not found',
                'errors' => []
            ], 404);
        }

        return response()->json([ 'client' => $client ], 200);
    }

}

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
                'antiguedadEmpresa'      => $clientRequest['antiguedadEmpresa'] ?? $client->antiguedadEmpresa,
                'reconocimientoMercado'  => $clientRequest['reconocimientoMercado'] ?? $client->reconocimientoMercado,
                'informeComercial'       => $clientRequest['informeComercial'] ?? $client->informeComercial,
                'infraestructura'        => $clientRequest['infraestructura'] ?? $client->infraestructura,
                'problemasLegales'       => $clientRequest['problemasLegales'] ?? $client->problemasLegales,
                'calidadCartera'         => $clientRequest['calidadCartera'] ?? $client->calidadCartera,
                'referenciasBancarias'   => $clientRequest['referenciasBancarias'] ?? $client->referenciasBancarias,
                'referenciasComerciales' => $clientRequest['referenciasComerciales'] ?? $client->referenciasComerciales,
                'importanciaMop'         => $clientRequest['importanciaMop'] ?? $client->importanciaMop,
                'perteneceHolding'       => $clientRequest['perteneceHolding'] ?? $client->perteneceHolding,
                'idAnalisis'             => $clientRequest['idAnalisis'] ?? $client->idAnalisis,
            ]);

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
        $offset = $request->get('offset', 0);
        $searchTerm = $request->get('searchTerm', null);

        /** @var \App\Models\User $authUser **/
        $authUser = Auth::user();

        if(!$authUser){
            return response()->json([
                'message' => 'No se encontró el usuario autenticado.',
                'errors' => []
            ], 400);
        }

        $clientQuery = Client::where('user_id', $authUser->id)
            ->with('user');

        if( empty( $searchTerm )) {

            $clients = $clientQuery->skip($offset)->take($limit)->get();
            $total = $clientQuery->count();

            return response()->json([
                'clients' => $clients,
                'total' => $total,
            ], 200);
        }

        /** @var \Illuminate\Database\Eloquent\Builder $q **/
        $searchQuery = function ($q) use ($searchTerm) {
            $q->where('name', 'LIKE', '%' . $searchTerm . '%');
        };


        $clientQuery = $clientQuery->where($searchQuery);

        $clients = $clientQuery->skip($offset)->take($limit)->get();
        $total = $clientQuery->count();

        return response()->json([
            'clients' => $clients,
            'total' => $total,
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
            ->with('user')
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

<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Models\Client;
use App\Models\SaludFinanciera;
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
                'anioConstitucion'      => $clientRequest['anioConstitucion'] ?? null,
                'sector_actividad'      => $clientRequest['sector_actividad'] ?? null,
                'street'                => $clientRequest['street'] ?? null,
                'house_number'          => $clientRequest['house_number'] ?? null,
                'neighborhood'          => $clientRequest['neighborhood'] ?? null,
                'municipality'          => $clientRequest['municipality'] ?? null,
                'state'                 => $clientRequest['state'] ?? null,
                'postal_code'           => $clientRequest['postal_code'] ?? null,
                'country'               => $clientRequest['country'] ?? null,
                'city'                  => $clientRequest['city'] ?? null,
            ]);

            if( !$client ){
                return response()->json([
                    'message' => 'No se pudo crear el cliente.',
                    'errors' => [
                        'client' => 'No se pudo crear el cliente.'
                    ]
                ], 400);
            }

            SaludFinanciera::create([
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
                'client_id'             => $client->id
            ]);

            DB::commit();
            return response()->json([
                'client'            => $client,
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
                'name'              => $clientRequest['name'] ?? $client->name,
                'score'             => $clientRequest['score'] ?? $client->score,
                'rfc'               => isset($clientRequest['rfc']) ? Str::upper($clientRequest['rfc']) : $client->rfc,
                'anioConstitucion'  => $clientRequest['anioConstitucion'] ?? $client->anioConstitucion,
                'sector_actividad'  => $clientRequest['sector_actividad'] ?? $client->sector_actividad,
                'street'            => $clientRequest['street'] ?? $client->street,
                'house_number'      => $clientRequest['house_number'] ?? $client->house_number,
                'neighborhood'      => $clientRequest['neighborhood'] ?? $client->neighborhood,
                'municipality'      => $clientRequest['municipality'] ?? $client->municipality,
                'state'             => $clientRequest['state'] ?? $client->state,
                'postal_code'       => $clientRequest['postal_code'] ?? $client->postal_code,
                'country'           => $clientRequest['country'] ?? $client->country,
                'city'              => $clientRequest['city'] ?? $client->city,
            ]);

            DB::commit();
            return response()->json([
                'client'    => $client,
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

    public function riskScore( Request $request, $clientId ) {
        $requestData = $request->validate([
            'idSaludFinanciera'      => ['required', 'numeric', 'exists:salud_financieras,id'],
            'antiguedadEmpresa'      => ['required', 'numeric'],
            'reconocimientoMercado'  => ['required', 'numeric'],
            'informeComercial'       => ['required', 'numeric'],
            'infraestructura'        => ['required', 'numeric'],
            'problemasLegales'       => ['required', 'numeric'],
            'calidadCartera'         => ['required', 'numeric'],
            'referenciasBancarias'   => ['required', 'numeric'],
            'referenciasComerciales' => ['required', 'numeric'],
            'importanciaMop'         => ['required', 'numeric'],
            'perteneceHolding'       => ['required', 'numeric'],
        ]);

        $client = Client::find($clientId);
        if(!$client){
            return response()->json([
                'message' => 'No se encontró el cliente.',
                'errors' => [
                    'client' => 'No se encontró el cliente.'
                ]
            ], 400);
        }

        /** @var \App\Models\SaludFinanciera $saludFinanciera **/
        $saludFinanciera = SaludFinanciera::find($requestData['idSaludFinanciera']);

        if(!$saludFinanciera){
            return response()->json([
                'message' => 'No se encontró la salud financiera.',
                'errors' => [
                    'saludFinanciera' => 'No se encontró la salud financiera.'
                ]
            ], 400);
        }

        DB::beginTransaction();
        try {
            $saludFinanciera->update([
                'antiguedadEmpresa' => $requestData['antiguedadEmpresa'] ?? $saludFinanciera->antiguedadEmpresa,
                'reconocimientoMercado' => $requestData['reconocimientoMercado'] ?? $saludFinanciera->reconocimientoMercado,
                'informeComercial' => $requestData['informeComercial'] ?? $saludFinanciera->informeComercial,
                'infraestructura' => $requestData['infraestructura'] ?? $saludFinanciera->infraestructura,
                'problemasLegales' => $requestData['problemasLegales'] ?? $saludFinanciera->problemasLegales,
                'calidadCartera' => $requestData['calidadCartera'] ?? $saludFinanciera->calidadCartera,
                'referenciasBancarias' => $requestData['referenciasBancarias'] ?? $saludFinanciera->referenciasBancarias,
                'referenciasComerciales' => $requestData['referenciasComerciales'] ?? $saludFinanciera->referenciasComerciales,
                'importanciaMop' => $requestData['importanciaMop'] ?? $saludFinanciera->importanciaMop,
                'perteneceHolding' => $requestData['perteneceHolding'] ?? $saludFinanciera->perteneceHolding,
            ]);

            DB::commit();
            return response()->json([
                'client' => $client ,
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Ocurrió un error al actualizar el score de riesgo.',
                'errors' => [
                    'client' => 'Ocurrió un error al actualizar el score de riesgo.'
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

        $clientQuery = Client::with('report','saludFinancieras','user');
        // $clientQuery = Client::where('user_id', $authUser->id)
        //     ->with('report','saludFinancieras','user');

        if( empty( $searchTerm )) {
            $clients = $clientQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $clientQuery = $clientQuery->where('name', 'LIKE', '%' . $searchTerm . '%')
                ->orWhere('rfc', 'LIKE', '%' . $searchTerm . '%');
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
            ->with('user','report','saludFinancieras')
            ->first();
        // $client = Client::where('id', $clientId)
        //     ->where('user_id', $authUser->id)
        //     ->with('user','report','saludFinancieras')
        //     ->first();

        if(!$client){
            return response()->json([
                'message' => 'Client not found',
                'errors' => []
            ], 404);
        }

        return response()->json([ 'client' => $client ], 200);
    }

}

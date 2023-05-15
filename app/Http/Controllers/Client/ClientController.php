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
                'user_id' => $authUserId,
                'name' => $clientRequest['name'],
                'email' => $clientRequest['email'],
                'password' => Hash::make('@')
            ]);

            if( !$client ){
                $errors['client'] = 'No se pudo crear el cliente.';
                return response()->json([
                    'message' => 'No se pudo crear el cliente.',
                    'errors' => $errors
                ], 400);
            }

            $client->profile()->create([

                'last_name'              => $clientRequest['lastName'] ?? "",
                'middle_name'            => $clientRequest['middleName'] ?? "",
                'rfc'                    => isset($clientRequest['rfc']) ? Str::upper($clientRequest['rfc']) : null,
                'phone_number'           => $clientRequest['phoneNumber'] ?? null,
                'street'                 => $clientRequest['street'] ?? null,
                'house_number'           => $clientRequest['house_number'] ?? null,
                'neighborhood'           => $clientRequest['neighborhood'] ?? null,
                'municipality'           => $clientRequest['municipality'] ?? null,
                'state'                  => $clientRequest['state'] ?? null,
                'postal_code'            => $clientRequest['postal_code'] ?? null,
                'country'                => $clientRequest['country'] ?? null,
                'birth_date'             => $clientRequest['birthDate'] ?? null,
                'monthly_income'         => $clientRequest['monthlyIncome'] ?? null,
                'additional_income'      => $clientRequest['additionalIncome'] ?? null,

                // Data
                'anioConstitucion'       => $clientRequest['anioConstitucion'] ?? null,
                'sector_actividad'       => $clientRequest['sector_actividad'] ?? null,
                'ventas'                 => $clientRequest['ventas'] ?? null,
                'ventasAnterior'         => $clientRequest['ventasAnterior'] ?? null,
                'trabActivo'             => $clientRequest['trabActivo'] ?? null,
                'otrosIng'               => $clientRequest['otrosIng'] ?? null,
                'resExplotacion'         => $clientRequest['resExplotacion'] ?? null,
                'resFinanciero'          => $clientRequest['resFinanciero'] ?? null,
                'resAntesImp'            => $clientRequest['resAntesImp'] ?? null,
                'deudoresComerciales'    => $clientRequest['deudoresComerciales'] ?? null,
                'inversionesFin'         => $clientRequest['inversionesFin'] ?? null,
                'efectivoLiquidez'       => $clientRequest['efectivoLiquidez'] ?? null,
                'activoTotal'            => $clientRequest['activoTotal'] ?? null,
                'pasivoNoCirculante'     => $clientRequest['pasivoNoCirculante'] ?? null,
                'provisionesLargoPlazo'  => $clientRequest['provisionesLargoPlazo'] ?? null,
                'pasivoCirculante'       => $clientRequest['pasivoCirculante'] ?? null,
                'capitalContable'        => $clientRequest['capitalContable'] ?? null,
                'prestamosActuales'      => $clientRequest['prestamosActuales'] ?? null,

                // Cualitativo
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

            if( isset($clientStoreRequest['attachment_id']) ){
                $client->profile->profileImage()->create([
                    'attachment_id' => $clientRequest['attachment_id']
                ]);
            }

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
                'errors' => [],
                'exception' => $th->getMessage()
            ], 500);
        }
    }

    // agricultura_y_Ganaderia: "Agricultura y Ganaderia",
    // industria_extractiva: "Industria Extractiva",
    // industria_manufacturera: "Industria Manufacturera",
    // energia: "Energia",
    // aguas_y_saneamiento: "Aguas y Saneamiento",
    // construccion: "Construccion",
    // comercial: "Comercial",
    // transporte: "Transporte",
    // hoteleria: "Hoteleria",
    // comunicacion: "Comunicación",
    // finanzas y seguros: "Finanzas y Seguros",
    // actividades_inmobiliarias: "Actividades Inmobiliarias",
    // actividades_profesionales: "Actividades Profesionales",
    // otros_servicios: "Otros Servicios",
    // otras_actividades: "Otras Actividades",

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

        $clientQuery = Client::with('user');

        if( empty( $searchTerm )) {

            $clients = $clientQuery->skip($offset)->take($limit)->get();
            $total = $clientQuery->count();

            return response()->json([
                'clients' => $clients,
                'total' => $total,
            ], 200);
        }

        $searchQuery = function ($q) use ($searchTerm) {
            $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                ->orWhere('email', 'LIKE', '%' . $searchTerm . '%');
        };

        $profileQuery = function ($q) use ($searchTerm) {
            $q->where(DB::raw("CONCAT(last_name, ' ', middle_name)"), 'LIKE', '%' . $searchTerm . '%')
                ->orWhere(DB::raw("CONCAT(middle_name, ' ', last_name)"), 'LIKE', '%' . $searchTerm . '%');
        };

        $clientQuery = $clientQuery->where($searchQuery)
            ->orWhereHas('profile', $profileQuery);

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
            ->with('user')
            ->first();

        if(!$client){
            return response()->json([
                'message' => 'Client not found'
            ], 404);
        }

        return response()->json([ 'client' => $client ], 200);
    }

}

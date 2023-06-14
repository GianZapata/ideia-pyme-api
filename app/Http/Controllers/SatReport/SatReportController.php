<?php

namespace App\Http\Controllers\SatReport;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\SatReport;
use App\Models\SatReportPartners;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SatReportController extends Controller
{

    public function index()
    {
        return response()->json([
            'message' => 'SatReports fetched successfully',
            'satReports' => SatReport::all()
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $requestData = $request->validate([
            'client_id'     => ['required', 'integer', 'exists:clients,id'],
            'name'          => ['required', 'string'],
            'street'        => ['required', 'string'],
            'house_number'  => ['required', 'string'],
            'neighborhood'  => ['required', 'string'],
            'municipality'  => ['required', 'string'],
            'state'         => ['required', 'string'],
            'postal_code'   => ['required', 'string'],
            'country'       => ['required', 'string'],
            'city'          => ['required', 'string'],
            'with_partners' => ['required', 'boolean'],
            'partners_data' => ['required_if:with_partners,true', 'array'],
        ], [
            'client_id.required' => 'El cliente es requerido',
            'client_id.integer' => 'El cliente debe ser un id válido',
            'client_id.exists' => 'El cliente no existe',

            'name' => 'El campo name es requerido',
            'street' => 'El campo street es requerido',
            'house_number' => 'El campo houseNumber es requerido',
            'neighborhood' => 'El campo neighborhood es requerido',
            'municipality' => 'El campo municipality es requerido',
            'state' => 'El campo state es requerido',
            'postal_code' => 'El campo postalCode es requerido',
            'country' => 'El campo country es requerido',
            'city' => 'El campo city es requerido',

            'with_partners.required' => 'El campo with_partners es requerido',
            'with_partners.boolean' => 'El campo with_partners debe ser un booleano',
            'partners_data.required_if' => 'El campo partners_data es requerido',
            'partners_data.array' => 'El campo partners_data debe ser un arreglo',
        ]);

        DB::beginTransaction();
        try {

            $client = Client::find($requestData['client_id']);

            if(!$client) {
                return response()->json([
                    'message' => 'El cliente no existe',
                    'errors' => [
                        'error' => 'El cliente no existe',
                    ]
                ], 422);
            }

            if($client->has_reports) {
                return response()->json([
                    'message' => 'El cliente ya cuenta con un reporte',
                    'errors' => [
                        'error' => 'El cliente ya cuenta con un reporte',
                    ]
                ], 422);
            }

            $satReport = SatReport::create([
                'client_id'     => $requestData['client_id'],
                'name'          => $requestData['name'],
                'street'        => $requestData['street'],
                'house_number'  => $requestData['house_number'],
                'neighborhood'  => $requestData['neighborhood'],
                'municipality'  => $requestData['municipality'],
                'state'         => $requestData['state'],
                'postal_code'   => $requestData['postal_code'],
                'country'       => $requestData['country'],
                'city'          => $requestData['city'],
                'with_partners' => $request['with_partners'],
                'total_tasks'   => 8,
            ]);

            if ($requestData['with_partners']) {
                foreach ($request['partners_data'] as $key => $partner) {
                    SatReportPartners::create([
                        'sat_report_id'     => $satReport->id,
                        'rfc'               => $partner['rfc'] ?? "",
                        'curp'              => $partner['curp'] ?? "",
                        'name'              => $partner['name'] ?? "",
                        'last_name'         => $partner['last_name'] ?? "",
                        'second_last_name'  => $partner['second_last_name'] ?? "",
                        'percentage'        => $partner['percentage'] ?? "",
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Reporte creado correctamente',
                'satReport' => $satReport,
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return response()->json([
                'message' => 'Error al crear el reporte',
                'errors' => [
                    'error' => $th->getMessage(),
                ]
            ], 422);
        }

    }

}

<?php

namespace App\Http\Controllers\SatReport;

use App\Http\Controllers\Controller;
use App\Models\SatReport;
use App\Models\SatReportPartners;
use Illuminate\Http\Request;

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

        try {
            $request->validate([
                'client_id' => ['required', 'integer', 'exists:clients,id'],
                'with_partners' => ['required', 'boolean'],
                'partners_data' => ['required_if:with_partners,true', 'array'],
            ], [
                'client_id.required' => 'El cliente es requerido',
                'client_id.integer' => 'El cliente debe ser un id válido',
                'client_id.exists' => 'El cliente no existe',
                'with_partners.required' => 'El campo with_partners es requerido',
                'with_partners.boolean' => 'El campo with_partners debe ser un booleano',
                'partners_data.required_if' => 'El campo partners_data es requerido',
                'partners_data.array' => 'El campo partners_data debe ser un arreglo',
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => 'Error al validar los datos',
                'errors' => $exception->getMessage(),
            ], 422);
        }

        try {
            $satReport = SatReport::create([
                'client_id' => $request->client_id,
                'with_partners' => $request->with_partners,
                'total_tasks' => 8,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al crear el reporte',
                'errors' => $th->getMessage(),
            ], 422);
        }
        

        if ($request->with_partners) {
            foreach ($request->partners_data as $key => $partner) {
                try {
                    SatReportPartners::create([
                        'sat_report_id' => $satReport->id,
                        'rfc' => $partner['rfc'],
                        'name' => $partner['name'],
                        'last_name' => $partner['last_name'],
                        'second_last_name' => $partner['second_last_name'],
                        'percentage' => $partner['percentage'],
                    ]);
                } catch (\Throwable $th) {
                    SatReport::destroy($satReport->id);
                    return response()->json([
                        'message' => 'Error al agregar partners al reporte',
                        'errors' => $th->getMessage(),
                    ], 422);
                }
            }
        }

        return response()->json([
            'message' => 'Reporte creado correctamente',
            'satReport' => $satReport,
        ], 201);

    }

}

<?php

namespace App\Http\Controllers\SatReport;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSatReportCredentialsRequest;
use App\Http\Requests\UpdateSatReportCredentialsRequest;
use App\Models\SatReport;
use App\Models\SatReportCredentials;
use Illuminate\Http\Request;
use App\Http\Controllers\SatReport\VerifyCiecController;

class SatReportCredentialsController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        try {
            $request->validate([
                'sat_report_id' => ['required', 'integer', 'exists:sat_reports,id'],
                'credentials_type' => ['required', 'string', 'in:ciec,fiel'],
                'rfc' => ['required', 'string'],
                'password' => ['required', 'string'],
            ], [
                'sat_report_id.required' => 'El reporte es requerido',
                'sat_report_id.integer' => 'El reporte debe ser un id válido',
                'sat_report_id.exists' => 'El reporte no existe',
                'credentials_type.required' => 'El tipo de credenciales es requerido',
                'credentials_type.string' => 'El tipo de credenciales debe ser un string',
                'credentials_type.in' => 'El tipo de credenciales debe ser ciec o fiel',
                'rfc.required' => 'El RFC es requerido',
                'rfc.string' => 'El RFC debe ser un string',
                'password.required' => 'La contraseña es requerida',
                'password.string' => 'La contraseña debe ser un string',
            ]);

            $report = SatReport::where('id', $request->sat_report_id)->first();
            if (!$report) {
                return response()->json([
                    'message' => 'El reporte no existe',
                ], 404);
            }

            $satReportCredentials = SatReportCredentials::where('sat_report_id', $request->sat_report_id)->first();
            if ($satReportCredentials) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El reporte ya tiene credenciales registradas',
                ], 422);
            }
            if ($request->credentials_type == 'ciec') {

                $logged = new VerifyCiecController();
                $logged->verify($request->rfc, $request->password, $report->id);

                if ($logged->response == 'LOGUEADO') {
                    $satReportCredentials = SatReportCredentials::create([
                        'sat_report_id' => $request->sat_report_id,
                        'credentials_type' => $request->credentials_type,
                        'rfc' => $request->rfc,
                        'password' => $request->password,
                    ]);

                    return response()->json([
                        'message' => 'Credenciales registradas correctamente',
                        'satReportCredentials' => $logged,
                    ], 201);
                }else{
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Error al iniciar sesión con las credenciales',
                        'errors' => $logged,
                    ], 422);
                }
            }else{
                // efirma not available yet
                $satReportCredentials = SatReportCredentials::create([
                    'sat_report_id' => $request->sat_report_id,
                    'credentials_type' => $request->credentials_type,
                    'rfc' => $request->rfc,
                    'password' => $request->password,
                ]);
            }

            return response()->json([
                'message' => 'Credenciales registradas correctamente',
                'satReportCredentials' => $satReportCredentials,
            ], 201);

        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Error al crear las credenciales',
                'errors' => $th->getMessage(),
            ], 422);

        }

    }

}

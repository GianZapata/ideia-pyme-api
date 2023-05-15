<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'address'          => ['string'],
            'attachmentId'     => ['numeric', 'exists:attachments,id'],
            'birthDate'        => ['date', 'date_format:Y-m-d'],
            'city'             => ['string'],
            'country'          => ['string'],
            'email'            => ['required', 'email', 'unique:clients'],
            'lastName'         => ['required', 'string'],
            'name'             => ['required', 'string'],
            'phoneNumber'      => ['string'],
            'postalCode'       => ['string'],
            'rfc'              => ['required', 'string'],
            'score'            => ['numeric'],

            // 'nombre'                => ['string'], // : "ACEROS TLALPAN",
            'sector_actividad'      => ['string'], // : "comercial",
            'anioConstitucion'      => ['numeric'], // : "1978",
            'ventas'                => ['numeric'], // : "161987267",
            'ventasAnterior'        => ['numeric'], // : "170358652",
            'trabActivo'            => ['numeric'], // : "0",
            'otrosIng'              => ['numeric'], // : "0",
            'resExplotacion'        => ['numeric'], // : "4145397",
            'resFinanciero'         => ['numeric'], // : "1638026",
            'resAntesImp'           => ['numeric'], // : "1638026",
            'deudoresComerciales'   => ['numeric'], // : "7497895",
            'inversionesFin'        => ['numeric'], // : "0",
            'efectivoLiquidez'      => ['numeric'], // : "1408736",
            'activoTotal'           => ['numeric'], // : "39689714",
            'pasivoNoCirculante'    => ['numeric'], // : "20979979",
            'provisionesLargoPlazo' => ['numeric'], // : "0",
            'pasivoCirculante'      => ['numeric'], // : "11562149",
            'capitalContable'       => ['numeric'], // : "7147586",
            'prestamosActuales'     => ['numeric'], // : "0"

            // Cualitativo
            'antiguedadEmpresa'      => ['numeric'], // "1",
            'reconocimientoMercado'  => ['numeric'], // "1",
            'informeComercial'       => ['numeric'], // "5",
            'infraestructura'        => ['numeric'], // "1",
            'problemasLegales'       => ['numeric'], // "1",
            'calidadCartera'         => ['numeric'], // "2",
            'referenciasBancarias'   => ['numeric'], // "1",
            'referenciasComerciales' => ['numeric'], // "1",
            'importanciaMop'         => ['numeric'], // "1",
            'perteneceHolding'       => ['numeric'], // "5",
            'idAnalisis'             => ['numeric'], // "6"
        ];
    }
}

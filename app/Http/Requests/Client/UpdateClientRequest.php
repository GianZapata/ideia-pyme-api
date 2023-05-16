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
            'name'                  => ['string'],
            'sector_actividad'      => ['string'],
            'anioConstitucion'      => ['numeric'],
            'ventas'                => ['numeric'],
            'ventasAnterior'        => ['numeric'],
            'trabActivo'            => ['numeric'],
            'otrosIng'              => ['numeric'],
            'resExplotacion'        => ['numeric'],
            'resFinanciero'         => ['numeric'],
            'resAntesImp'           => ['numeric'],
            'deudoresComerciales'   => ['numeric'],
            'inversionesFin'        => ['numeric'],
            'efectivoLiquidez'      => ['numeric'],
            'activoTotal'           => ['numeric'],
            'pasivoNoCirculante'    => ['numeric'],
            'provisionesLargoPlazo' => ['numeric'],
            'pasivoCirculante'      => ['numeric'],
            'capitalContable'       => ['numeric'],
            'prestamosActuales'     => ['numeric'],


            'antiguedadEmpresa'      => ['numeric'],
            'reconocimientoMercado'  => ['numeric'],
            'informeComercial'       => ['numeric'],
            'infraestructura'        => ['numeric'],
            'problemasLegales'       => ['numeric'],
            'calidadCartera'         => ['numeric'],
            'referenciasBancarias'   => ['numeric'],
            'referenciasComerciales' => ['numeric'],
            'importanciaMop'         => ['numeric'],
            'perteneceHolding'       => ['numeric'],
            'idAnalisis'             => ['numeric'],
        ];
    }
}

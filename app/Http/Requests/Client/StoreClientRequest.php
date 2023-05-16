<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{

    protected $rules = [
        'name'                  => ['string'],
        'score'                 => ['numeric'],
        'rfc'                   => ['string'],
        'anioConstitucion'      => ['string'],
        'sector_actividad'      => ['string'],
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
    ];

    protected $messages = [];

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
        return $this->rules;
    }

    public function messages()
    {
        return $this->messages;
    }
}

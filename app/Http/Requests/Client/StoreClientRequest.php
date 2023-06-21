<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{

    protected $rules = [
        'name'                  => ['string'],
        'score'                 => ['numeric'],
        'rfc'                   => ['string'],
        'street'                => ['string'],
        'house_number'          => ['string'],
        'neighborhood'          => ['string'],
        'municipality'          => ['string'],
        'state'                 => ['string'],
        'postal_code'           => ['string'],
        'country'               => ['string'],
        'city'                  => ['string'],

        'anioConstitucion'      => ['numeric'],
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

    protected $messages = [
        'street.required'           => 'La calle es requerida',
        'house_number.required'     => 'El número de casa es requerido',
        'neighborhood.required'     => 'La colonia es requerida',
        'municipality.required'     => 'El municipio es requerido',
        'state.required'            => 'El estado es requerido',
        'postal_code.required'      => 'El código postal es requerido',
        'country.required'          => 'El país es requerido',
        'city.required'             => 'La ciudad es requerida',
        'rfc.required'              => 'El RFC es requerido',
        'name.required'             => 'El nombre es requerido',
        'sector_actividad.required' => 'El sector de actividad es requerido',
        'anioConstitucion.required' => 'El año de constitución es requerido',
    ];

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

<?php

namespace App\Http\Requests\SatReport;

use Illuminate\Foundation\Http\FormRequest;

class StoreSatReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'credentials_type' => ['string', 'in:ciec,fiel'],
            'with_partners' => ['required', 'boolean'],
            'partners_data' => ['required_if:with_partners,true', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'El cliente es requerido',
            'client_id.integer' => 'El cliente debe ser un id válido',
            'client_id.exists' => 'El cliente no existe',
            'credentials_type.string' => 'El tipo de credencial debe ser una cadena de texto',
            'credentials_type.in' => 'El tipo de credencial no es válido',
            'with_partners.required' => 'El campo with_partners es requerido',
            'with_partners.boolean' => 'El campo with_partners debe ser un booleano',
            'partners_data.required_if' => 'El campo partners_data es requerido',
            'partners_data.array' => 'El campo partners_data debe ser un arreglo',
        ];
    }
}

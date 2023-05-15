<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
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
        ];
    }

    public function messages()
    {
        return [
            'address.string'        => 'La dirección debe ser una cadena de caracteres.',
            'attachmentId.exists'   => 'El archivo adjunto con el ID proporcionado no existe.',
            'attachmentId.numeric'  => 'El ID del archivo adjunto debe ser un número.',
            'birthDate.date_format' => 'La fecha de nacimiento debe tener el formato YYYY-MM-DD.',
            'birthDate.date'        => 'La fecha de nacimiento debe ser una fecha válida.',
            'city.string'           => 'La ciudad debe ser una cadena de caracteres.',
            'country.string'        => 'El país debe ser una cadena de caracteres.',
            'email.email'           => 'El correo electrónico debe ser una dirección válida.',
            'email.required'        => 'El correo electrónico es requerido.',
            'email.unique'          => 'El correo electrónico ya está en uso.',
            'lastName.required'     => 'El apellido es requerido.',
            'lastName.string'       => 'El apellido debe ser una cadena de caracteres.',
            'name.required'         => 'El nombre es requerido.',
            'name.string'           => 'El nombre debe ser una cadena de caracteres.',
            'phoneNumber.string'    => 'El número de teléfono debe ser una cadena de caracteres.',
            'postalCode.string'     => 'El código postal debe ser una cadena de caracteres.',
            'rfc.required'          => 'El RFC es requerido.',
            'rfc.string'            => 'El RFC debe ser una cadena de caracteres.',
            'score.numeric'         => 'El puntaje debe ser un número.',
        ];
    }
}

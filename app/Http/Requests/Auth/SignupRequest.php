<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class SignupRequest extends FormRequest
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
            'name' => ['required', 'string'],
            'lastName' => ['required', 'string'],
            'phoneNumber' => ['string'],
            'email' => ['required', 'email'],
            'password' => [
                'required',
                'string',
                'min:6',
                Password::min(6)
                    ->letters()
                    ->symbols()
                    ->numbers(),
                'confirmed'
            ],
        ];
    }

    public function messages()
    {
        return [
            'name'           => 'El nombre es requerido',
            'lastName'       => 'El apellido es requerido',
            'phoneNumber'    => 'El número de teléfono no es válido',
            'email.required' => 'El email es requerido',
            'email.email'    => 'El email no es válido',
            'email.unique'   => 'El usuario ya está registrado',
            'password'       => 'La contraseña debe tener al menos 6 caracteres, una letra, un número y un símbolo',
        ];
    }
}

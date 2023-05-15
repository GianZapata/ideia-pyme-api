<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
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
    public function rules(): array {
        return [
            'token'                 => ['required','exists:password_reset_tokens'],
            'password_confirmation' => ['required_with:password', 'same:password'],
            'password'              => [
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


    public function messages() {
        return [
            'token.required'                      => 'El token de restablecimiento de contraseña es obligatorio.',
            'token.exists'                        => 'El token de restablecimiento de contraseña no es válido.',
            'email.required'                      => 'La dirección de correo electrónico es obligatoria.',
            'email.email'                         => 'La dirección de correo electrónico debe ser válida.',
            'email.exists'                        => 'No se encontró ninguna cuenta asociada a esta dirección de correo electrónico.',
            'password.required'                   => 'La contraseña es obligatoria.',
            'password.string'                     => 'La contraseña debe ser una cadena de texto.',
            'password.min'                        => 'La contraseña debe tener al menos :min caracteres.',
            'password.confirmed'                  => 'La confirmación de la contraseña no coincide.',
            'password_confirmation.required_with' => 'Por favor, confirma tu nueva contraseña.',
            'password_confirmation.same'          => 'Las contraseñas no coinciden. Por favor, asegúrate de que sean iguales.'
        ];
    }
}

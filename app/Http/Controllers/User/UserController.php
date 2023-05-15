<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Check if email exists
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkEmail(Request $request) {

        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'El correo electrónico es requerido.',
            'email.email' => 'Por favor, introduce un correo electrónico válido.',
        ]);

        $email = $request->email;

        $userExists = User::where('email', $email)->exists();
        return response()->json(['exists' => $userExists], 200);
    }
}

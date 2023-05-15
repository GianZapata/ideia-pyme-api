<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendResetLinkEmailRequest;
use App\Jobs\SendEmail;
use App\Mail\Auth\ResetPasswordEmail;
use App\Models\PasswordResetToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    protected const EXPIRE_IN_HOURS = 2; // rest link expiration in hours

      /**
     * Send reset link
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetLink(SendResetLinkEmailRequest $request) {

        DB::beginTransaction();
        try {

            $sendEmailRequest = $request->validated();

            if (!PasswordResetToken::canCreatePasswordResetToken($sendEmailRequest['email'])) {
                return response()->json([
                    'message' => 'Ha alcanzado el límite de tokens de restablecimiento de contraseña para hoy.',
                    'errors' => [
                        'email' => ['Ha alcanzado el límite de tokens de restablecimiento de contraseña para hoy.']
                    ]
                ], 429);
            }

            $user = User::where('email', $sendEmailRequest['email'])->first();

            if (!$user) {
                return response()->json([
                    'message' => 'No se encontró ningún usuario con esa dirección de correo electrónico.',
                    'errors' => [
                        'email' => ['No se encontró ningún usuario con esa dirección de correo electrónico.'],
                    ]
                ], 404);

            }

            $token = Str::random(15);
            $expireInHours = config('auth.passwords.users.expire') / 60;

            PasswordResetToken::create([
                'email'      => $sendEmailRequest['email'],
                'token'      => $token,
                'expires_at' => Carbon::now()->addHours($expireInHours),
            ]);

            $frontUrl = env('FRONTEND_URL', 'http://localhost:3000');
            $resetUrl = "{$frontUrl}/auth/reset-password/{$token}";

            $resetPasswordEmail = new ResetPasswordEmail([
                'user' => $user,
                'action_url' => $resetUrl,
                'code' => $token,
                'expireInHours' => $expireInHours,
            ]);
            SendEmail::dispatch($user->email,$resetPasswordEmail);

            DB::commit();
            return response()->json([
                'message' => 'Se ha enviado un correo electrónico con un enlace de restablecimiento de contraseña a su dirección de correo electrónico.'
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::debug($th->getMessage());
            return response()->json([
                'message' => 'No se pudo enviar el correo electrónico de restablecimiento de contraseña.',
                'errors' => [
                    'email' => ['No se pudo enviar el correo electrónico de restablecimiento de contraseña.']
                ]
            ], 500);
        }
    }
}

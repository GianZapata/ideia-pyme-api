<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Jobs\SendEmail;
use App\Mail\Auth\VerifyEmail;
use App\Models\EmailVerification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerificationEmailController extends Controller
{

    private const MAX_EMAIL_VERIFICATION_ATTEMPTS = 5;



    /**
     * Verificar el correo electrónico del usuario
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
    */

    public function verify(VerifyEmailRequest $request) {
        DB::beginTransaction();
        try {
            $verifyEmailRequestData = $request->validated();

            /** @var \App\Models\User $user **/
            $user = User::where('email', $verifyEmailRequestData['email'])->first();

            // Verificar si el usuario ya ha verificado su correo electrónico
            if ($user->hasVerifiedEmail()) {
                return response()->json(['message' => 'El usuario ya ha verificado su correo electrónico.'], 400);
            }

            // Verificar si el token de verificación proporcionado es válido
            if ($user->verification_token !== $verifyEmailRequestData['token']) {
                return response()->json(['message' => 'El token de verificación no es válido.'], 400);
            }

            // Marcar el correo electrónico del usuario como verificado
            $user->markEmailAsVerified();
            $user->verification_token = null;
            $user->emailVerificationAttempts()->delete();
            $user->save();

            DB::commit();
            return response()->json(['message' => 'El correo electrónico ha sido verificado exitosamente.']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => 'Ha ocurrido un error al verificar el correo electrónico.'], 500);
        }
    }

    //
      /**
     *  Reenviar el correo electrónico de verificación
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function resend(Request $request)
    {
        DB::beginTransaction();

        try {

             /** @var \App\Models\User $user **/
            $user = Auth::user();
            $ip = $request->ip();

            // Verificar si el usuario ya ha verificado su correo electrónico
            if ($user->hasVerifiedEmail()) {
                return response()->json(['message' => 'El usuario ya ha verificado su correo electrónico.'], 400);
            }

            // Obtener el número de reenvío de correo electrónico de hoy
            $today = Carbon::now()->toDateString();
            $emailCount = $user->emailVerificationAttempts()->where('created_at', '>=', $today)->count();

            if ($emailCount >= self::MAX_EMAIL_VERIFICATION_ATTEMPTS) {
                return response()->json(['message' => 'Se ha excedido el límite de reenvíos de correo electrónico para hoy.'], 400);
            }

            $token = rand(100000, 999999);
            $user->verification_token = $token;
            $user->save();

            $frontUrl = env('FRONTEND_URL', 'http://localhost:3000');
            $verificationUrl = "{$frontUrl}/auth/verify-email/{$token}?email={$user->email}";

            $verifyEmail = new VerifyEmail([
                'user' => $user,
                'action_url' => $verificationUrl,
                'verificationCode' => $token
            ]);

            SendEmail::dispatch($user->email,$verifyEmail);

            $expireInHours = config('auth.passwords.users.expire') / 60;

            // Registrar el intento de reenvío de correo electrónico en la base de datos
            $emailAttempt = new EmailVerification();
            $emailAttempt->user_id = $user->id;
            $emailAttempt->token = $token;
            $emailAttempt->expires_at = Carbon::now()->addHours($expireInHours);
            $emailAttempt->ip_address = $ip;
            $emailAttempt->created_at = Carbon::now();
            $emailAttempt->save();

            DB::commit();
            return response()->json(['message' => 'El correo electrónico de verificación ha sido enviado exitosamente.'], 200);
        } catch (\Throwable $th) {
            Log::error($th);
            DB::rollBack();
            return response()->json(['message' => 'Ha ocurrido un error al reenviar el correo electrónico de verificación.'], 500);
        }
    }
}

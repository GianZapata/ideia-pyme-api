<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            return response()->json([
                'message' => 'Error interno del servidor',
                'errors' => [
                    'server' => 'Error interno del servidor',
                ],
            ], 500);
        });
        $this->renderable(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, $request) {
            return response()->json([
                'message' => 'No tienes permisos para realizar esta acción',
                'errors' => [
                    'permission' => 'No tienes permisos para realizar esta acción',
                ],
            ], 403);
        });
        $this->renderable(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, $request) {
            return response()->json([
                'message' => 'Método no permitido',
                'errors' => [
                    'method' => 'Método no permitido',
                ],
            ], 405);
        });
    }
}

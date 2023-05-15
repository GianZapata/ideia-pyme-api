<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PasswordResetToken extends Model
{
    use HasFactory;

    protected $table = 'password_reset_tokens';

    protected $fillable = [
        'email',
        'token',
        'expires_at',
    ];

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $expireInHours = config('auth.passwords.users.expire') / 60;
            $model->id = Str::uuid()->toString();
            $model->expires_at = Carbon::now()->addHours($expireInHours); // Establece un valor predeterminado para expires_at
        });
    }

    /**
     * Verifica si un usuario puede crear un nuevo token de restablecimiento de contraseña.
     *
     * @param string $email El correo electrónico del usuario
     * @return bool True si el usuario puede crear un nuevo token; de lo contrario, false.
     */
    public static function canCreatePasswordResetToken(string $email): bool
    {
        // Busca todos los registros de la tabla password_reset_tokens que pertenecen a este usuario
        // y que fueron creados en las últimas 24 horas.
        $count = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        // Devuelve true si el usuario ha creado menos de tres tokens en las últimas 24 horas.
        return $count < 3;
    }


}

<?php

namespace App\Models;

use App\Models\ClientProfile;
use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Client extends Model
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'password',
        'verification_token',
        'is_active'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'verification_token',
        'deleted_at'
    ];

    protected array $guard_name = ['api', 'web'];

    protected $appends = ['profile_image_url'];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    /**
     * Get the profile image URL attribute.
     */
    public function profileImageUrl(): Attribute {

        return new Attribute(
            get: fn() =>
                        $this->profile &&
                        $this->profile->profileImage &&
                        $this->profile->profileImage->attachment
                            ? $this->profile->profileImage->attachment->url
                            : null
        );
    }

    public function isVerified(): Attribute
    {
        return new Attribute(
            get: fn() => !is_null($this->email_verified_at)
        );
    }


    public function profile()
    {
        return $this->hasOne(ClientProfile::class);
    }

    public function emailVerificationAttempts()
    {
        return $this->hasMany(EmailVerification::class);
    }

    /**
     * Obtiene el usuario asociado con el cliente.
     *
     * La función `belongsTo` establece una relación de "uno a muchos" inversa
     * entre el modelo `Client` y el modelo `User`. En este caso, cada cliente
     * pertenece a un usuario, lo que significa que hay una clave foránea
     * `user_id` en la tabla `clients` que hace referencia al campo `id` en la
     * tabla `users`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\EmailVerification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Casts\Attribute;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'verification_token',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_token',
        'roles',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'user_name' => 'string',
    ];

    protected $appends = [
        'profile_image_url',
        'full_name',
        'permission_names',
        'role_names'
    ];

    /**
        * Generate a unique username
        *
        * @param string $name
        * @param string $lastName
        * @return string
    */
    public function generateUsername($name, $lastName)
    {
        $username = Str::slug($name . ' ' . $lastName, '.');
        $count = 1;
        while (self::where('user_name', $username)->exists()) {
            $username = Str::slug($name . ' ' . $lastName, '') . $count;
            $count++;
        }
        return $username;
    }

    public function permissionNames(): Attribute {
        return new Attribute(
            get: fn () => $this->permissions()->pluck('name')->toArray() ?? [],
        );
    }


    public function roleNames(): Attribute {
        return new Attribute(
            get: fn () => $this->roles()->pluck('name')->toArray() ?? [],
        );
    }


    public function fullName(): Attribute {
        return new Attribute(
            get: fn () => $this->name . ' ' . $this->profile->last_name,
        );
    }

    public function profileImageUrl() : Attribute {
        return new Attribute(
            get: fn () => $this->profile && $this->profile->profileImage && $this->profile->profileImage->attachment
            ? $this->profile->profileImage->attachment->url
            : null,
        );
    }


    public function isVerified(): Attribute {
        return new Attribute(
            get: fn () => !is_null($this->email_verified_at),
        );
    }

    /**
     * Define la relación uno a uno entre el usuario y su perfil.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * @throws \Exception si el perfil del usuario no existe
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Define la relación uno a muchos entre el usuario y los intentos de verificación de correo electrónico.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function emailVerificationAttempts()
    {
        return $this->hasMany(EmailVerification::class);
    }

    // Relation with clients

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

}

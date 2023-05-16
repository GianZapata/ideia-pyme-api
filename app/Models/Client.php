<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Services\FinancialHealthService;

class Client extends Model
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'score',
        'rfc',

        'anioConstitucion',
        'sector_actividad',
        'ventas',
        'ventasAnterior',
        'trabActivo',
        'otrosIng',
        'resExplotacion',
        'resFinanciero',
        'resAntesImp',
        'deudoresComerciales',
        'inversionesFin',
        'efectivoLiquidez',
        'activoTotal',
        'pasivoNoCirculante',
        'provisionesLargoPlazo',
        'pasivoCirculante',
        'capitalContable',
        'prestamosActuales',

    ];

    protected array $guard_name = ['api', 'web'];

    protected $appends = [
        'salud_financiera'
    ];

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

    public function saludFinanciera(): Attribute {
        $financialHealthService = new FinancialHealthService();
        return new Attribute(
            get: fn () => $financialHealthService->calculate($this)
        );
    }
}

<?php

namespace App\Models;

use App\Models\SaludFinanciera;
use App\Models\SatReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Services\FinancialHealthService;

class Client extends Model
{

    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    private $sectorActividadTypes = [
        "AGRICULTURA_Y_GANADERIA"    => "Industrial",
        "INDUSTRIA_EXTRACTIVA"       => "Industrial",
        "INDUSTRIA_MANUFACTURERA"    => "Industrial",
        "ENERGIA"                    => "Industrial",
        "AGUAS_Y_SANEAMIENTO"        => "Industrial",
        "CONSTRUCCION"               => "Industrial",
        "COMERCIAL"                  => "Comercial",
        "TRANSPORTE"                 => "Servicio",
        "HOTELERIA"                  => "Hotelería",
        "COMUNICACIÓN"               => "Comunicación",
        "FINANZAS_Y_SEGUROS"         => "Servicio",
        "ACTIVIDADES_INMOBILIARIAS"  => "Servicio",
        "ACTIVIDADES_PROFESIONALES"  => "Servicio",
        "OTROS_SERVICIOS"            => "Servicio",
        "OTRAS_ACTIVIDADES"          => "Otras"
    ];

    protected array $guard_name = ['api', 'web'];

    protected $appends = [
        // 'salud_financiera',
        // 'score_cualitativo',
        // 'score_completo',
        'sector_actividad_name',
        'has_reports',
        'can_check_report',
        'municipality_name',
        'state_name',
        'country_name'
    ];

    protected $fillable = [
        'user_id',
        'name',
        'score',
        'rfc',
        'anioConstitucion',
        'sector_actividad',
        'street',
        'house_number',
        'neighborhood',
        'municipality',
        'state',
        'postal_code',
        'country',
        'city',
    ];

    public function getMunicipalityNameAttribute() {
        $federalEntities = config('federal_entities');

        // Encuentra la entidad federal (estado) que coincida con el estado de la instancia actual
        $federalEntity = collect($federalEntities)->firstWhere('slug', $this->state);

        // Si encontramos la entidad federal, entonces procedemos a buscar el municipio
        if ($federalEntity) {
            // Iteramos a través de los municipios de la entidad federal encontrada
            foreach ($federalEntity['municipalities'] as $municipality) {
                // Si el código del municipio coincide con el municipio de la instancia actual,
                // retornamos el nombre del municipio
                if ($municipality['code'] == $this->municipality) {
                    return Str::title($municipality['name']);
                }
            }
        }

        // Si no encontramos ni la entidad federal ni el municipio, retornamos el valor de municipio de la instancia actual
        return Str::title($this->municipality) ?? null;
    }
    public function getStateNameAttribute() {
        $federalEntities = config('federal_entities');
        $federalEntity = collect($federalEntities)->firstWhere('slug', $this->state);
        $state = $federalEntity['state'] ?? null;
        return Str::title($state) ?? null;
    }

    public function getCountryNameAttribute() {
        $countries = config('countries');
        $country = $countries[$this->country] ?? null;
        return Str::title($country) ?? null;
    }

    public function hasReports(): Attribute
    {
        return new Attribute(
            get: fn () => $this->reports()->count() > 0
        );
    }

    public function saludFinancieras()
    {
        return $this->hasMany(SaludFinanciera::class);
    }

    public function sectorActividadName(): Attribute
    {
        return new Attribute(
            get: fn () => $this->sector_actividad ? $this->sectorActividadTypes[$this->sector_actividad] : null
        );
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
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reports()
    {
        return $this->hasMany(SatReport::class, 'client_id');
    }

    public function report()
    {
        return $this->hasOne(SatReport::class, 'client_id');
    }

    public function canCheckReport(): Attribute
    {
        return new Attribute(
            get: fn () => $this->report && $this->report->total_tasks === intval($this->report->total_tasks_completed)
        );
    }

    public function attachments()
    {
        return $this->hasMany(ClientsAttachments::class);
    }
}

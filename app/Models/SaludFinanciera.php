<?php

namespace App\Models;

use App\Models\Client;
use App\Services\FinancialHealthService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaludFinanciera extends Model
{
    use HasFactory;

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

    protected $fillable = [
        'client_id',
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
        'antiguedadEmpresa',
        'reconocimientoMercado',
        'informeComercial',
        'infraestructura',
        'problemasLegales',
        'calidadCartera',
        'referenciasBancarias',
        'referenciasComerciales',
        'importanciaMop',
        'perteneceHolding',
        'idAnalisis',
    ];

    protected $appends = [
        'sector_actividad_name',
    ];

    public function client() {
        return $this->belongsTo(Client::class);
    }

    // public function saludFinanciera(): Attribute {
    //     $financialHealthService = new FinancialHealthService();
    //     return new Attribute(
    //         get: fn () => $financialHealthService->calculateFinancialHealth($this)
    //     );
    // }

    // public function scoreCualitativo(): Attribute {
    //     $financialHealthService = new FinancialHealthService();
    //     return new Attribute(
    //         get: fn () => $financialHealthService->calculateQualitativeScore($this)
    //     );
    // }
    // public function scoreCompleto(): Attribute {
    //     $saludFinanciera = $this->salud_financiera;
    //     $scoreCuantitativo = $saludFinanciera ? $saludFinanciera['scoreCuantitativo'] : 0;
    //     return new Attribute(
    //         get: fn () => $this->score_cualitativo + $scoreCuantitativo
    //     );
    // }

    public function sectorActividadName(): Attribute {
        return new Attribute(
            get: fn () => $this->sector_actividad ? $this->sectorActividadTypes[$this->sector_actividad] : null
        );
    }

}

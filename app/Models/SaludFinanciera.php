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

    protected $fillable = [
        'client_id',
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
    ];

    protected $appends = [
        'salud_financiera',
        'score_cualitativo',
        'score_completo',
        'can_request_risk_score'
    ];


    public function client() {
        return $this->belongsTo(Client::class);
    }

    public function saludFinanciera(): Attribute {
        $financialHealthService = new FinancialHealthService();
        return new Attribute(
            get: fn () => $financialHealthService->calculateFinancialHealth($this)
        );
    }

    public function scoreCualitativo(): Attribute {
        $financialHealthService = new FinancialHealthService();
        return new Attribute(
            get: fn () => $financialHealthService->calculateQualitativeScore($this)
        );
    }
    public function scoreCompleto(): Attribute {
        $saludFinanciera = $this->salud_financiera;
        $scoreCuantitativo = $saludFinanciera ? $saludFinanciera['scoreCuantitativo'] : 0;
        return new Attribute(
            get: fn () => $this->score_cualitativo + $scoreCuantitativo
        );
    }

    public function canRequestRiskScore(): Attribute {
        return new Attribute(
            get: fn () =>
                !$this->antiguedadEmpresa ||
                !$this->reconocimientoMercado ||
                !$this->informeComercial ||
                !$this->infraestructura ||
                !$this->problemasLegales ||
                !$this->calidadCartera ||
                !$this->referenciasBancarias ||
                !$this->referenciasComerciales ||
                !$this->importanciaMop ||
                !$this->perteneceHolding
        );
    }

}

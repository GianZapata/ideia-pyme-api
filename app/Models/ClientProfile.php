<?php

namespace App\Models;

use App\Models\Client;
use App\Models\ClientProfileImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'last_name',
        'middle_name',
        'phone_number', // E.164 format
        'street',
        'house_number',
        'neighborhood',
        'municipality',
        'state',
        'postal_code',
        'country',
        'score',
        'birth_date',
        'rfc',

        /** Data */
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

        /** Cualitativo */
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

    protected $hidden = [
        'profileImage',
        'client_id'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function profileImage()
    {
        return $this->hasOne(ClientProfileImage::class);
    }
}
